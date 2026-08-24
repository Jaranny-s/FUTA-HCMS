<?php
require_once('../../../private/config.php');
require_password_reset();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "Invalid encounter record.";
    redirect_to(url_wrap('/modules/encounters/index.php'));
}

$encounter = find_encounter_by_id((int)$id);
if (!$encounter) {
    $_SESSION['error'] = "Encounter could not be found.";
    redirect_to(url_wrap('/modules/encounters/index.php'));
}

$encounter_id = $encounter['id'];

// Handle POST submissions
if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    $staff_id = $_SESSION['staff_id'];

    if ($action === 'save_vitals') {
        save_vitals($encounter_id, $staff_id, $_POST['temperature'], $_POST['weight'], $_POST['height'], $_POST['bmi'], $_POST['pulse'], $_POST['respiration'], $_POST['oxygen_saturation'], $_POST['systolic_bp'], $_POST['diastolic_bp']);
        update_encounter_status($encounter_id, 'In Progress');
        $_SESSION['message'] = "Vitals saved successfully. Patient is ready for doctor consultation.";
    } elseif ($action === 'save_nursing_note') {
        save_nursing_note($encounter_id, $staff_id, $_POST['notes']);
        $_SESSION['message'] = "Nursing note added.";
    } elseif ($action === 'save_consultation') {
        save_consultation($encounter_id, $staff_id, $_POST['hpi'], $_POST['exam'], $_POST['assessment'], $_POST['plan'], $_POST['follow_up']);
        update_encounter_status($encounter_id, 'In Progress');
        $_SESSION['message'] = "Consultation saved.";
    } elseif ($action === 'save_diagnosis') {
        save_diagnosis($encounter_id, $staff_id, $_POST['diagnosis_type'], $_POST['diagnosis'], $_POST['icd_code'], $_POST['notes']);
        $_SESSION['message'] = "Diagnosis added.";
    } elseif ($action === 'save_prescription') {
        save_prescription($encounter_id, $staff_id, (int)$_POST['inventory_id'], $_POST['dosage'], $_POST['frequency'], $_POST['duration'], $_POST['instructions']);
        $_SESSION['message'] = "Prescription added.";
    } elseif ($action === 'complete_encounter') {
        update_encounter_status($encounter_id, 'Completed');
        $_SESSION['message'] = "Encounter marked as Completed.";
    }
    
    // Redirect to prevent form resubmission
    redirect_to(url_wrap("/modules/encounters/view.php?id={$encounter_id}"));
}

$vitals = get_vitals($encounter_id);
$nursing_notes = get_nursing_notes($encounter_id);
$consultation = get_consultation($encounter_id);
$diagnoses = get_diagnoses($encounter_id);
$prescriptions = get_prescriptions($encounter_id);
$inventory_items = get_all_inventory(); // Fetch inventory for prescription form

$page_title = 'Encounter Workspace - ' . $encounter['encounter_number'];
$specificCss = '/assets/css/encounters.css';
$specificJs = '/assets/js/encounters.js';

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    
    <main class="main-content">
        <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" class="btn btn-primary" id="link_layout">
            <i class="bi bi-arrow-left"></i> Back to Encounters
        </a>
        
        <div class="top">
            <p class="top-head">Clinical Workspace</p> 
            <p class="top-description">Encounter: <?php echo v_wrap($encounter['encounter_number']); ?></p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div class="patient-summary-card">
            <div class="patient-info">
                <?php if (!empty($encounter['profile_image'])) { ?>
                    <img src="<?php echo url_wrap('modules/patients/images/patient_pictures/' . v_wrap($encounter['profile_image'])); ?>" alt="Profile" class="workspace-avatar">
                <?php } else { ?>
                    <img src="<?php echo url_wrap('/assets/images/default_profile_pic.png'); ?>" alt="Default" class="workspace-avatar">
                <?php } ?>
                <div class="details">
                    <h2><?php echo v_wrap($encounter['patient_last'] . ' ' . $encounter['patient_first']); ?> <span style="font-size: 14px; color: #666;">(<?php echo v_wrap($encounter['p_id']); ?>)</span></h2>
                    <p>
                        <strong>Category:</strong> <?php echo v_wrap($encounter['patient_category']); ?> | 
                        <strong>Gender:</strong> <?php echo v_wrap($encounter['gender']); ?> |
                        <strong>DOB:</strong> <?php echo v_wrap($encounter['date_of_birth']); ?> | 
                        <strong>Blood Group:</strong> <?php echo v_wrap($encounter['blood_group']); ?>
                    </p>
                </div>
            </div>
            <div class="encounter-status">
                <span class="badge status-<?php echo str_replace(' ', '-', strtolower($encounter['status'])); ?>">
                    Status: <?php echo v_wrap($encounter['status']); ?>
                </span>
                <?php if ($encounter['status'] !== 'Completed' && hasPermission('edit_encounter')) { ?>
                <form action="<?php echo url_wrap("/modules/encounters/view.php?id={$encounter_id}"); ?>" method="post" style="display:inline-block; margin-top: 10px;">
                    <input type="hidden" name="action" value="complete_encounter">
                    <button type="submit" class="btn btn-success" style="background:#1bc03d; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">
                        <i class="bi bi-check-circle"></i> Complete Encounter
                    </button>
                </form>
                <?php } ?>
            </div>
        </div>

        <div class="tabs" role="tablist">
            <button role="tab" class="tab-btn active" data-tab="vitals">Vitals & Nursing</button>
            <?php if (hasPermission('view_consultation')) { ?>
            <button role="tab" class="tab-btn" data-tab="consultation">Consultation</button>
            <button role="tab" class="tab-btn" data-tab="diagnosis">Diagnosis</button>
            <button role="tab" class="tab-btn" data-tab="prescription">Prescription</button>
            <?php } ?>
        </div>

        <!-- VITALS TAB -->
        <div id="vitals" class="tab-content active">
            <div class="clinical-grid">
                <!-- Left: Vitals Form/View -->
                <div class="card">
                    <h3>Patient Vitals</h3>
                    <?php if ($vitals) { ?>
                        <div class="vitals-display">
                            <p><strong>Temp:</strong> <?php echo v_wrap($vitals['temperature']); ?> °C</p>
                            <p><strong>Weight:</strong> <?php echo v_wrap($vitals['weight']); ?> kg</p>
                            <p><strong>BP:</strong> <?php echo v_wrap($vitals['systolic_bp'] . '/' . $vitals['diastolic_bp']); ?> mmHg</p>
                            <p><strong>Pulse:</strong> <?php echo v_wrap($vitals['pulse']); ?> bpm</p>
                            <p><strong>SpO2:</strong> <?php echo v_wrap($vitals['oxygen_saturation']); ?> %</p>
                        </div>
                    <?php } else { ?>
                        <?php if (hasPermission('add_vitals')) { ?>
                            <button data-modal-target="addVitalsModal" class="btn btn-primary" style="margin-bottom:15px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px;">+ Record New Vitals</button>
                            
                            <!-- Add Vitals Modal -->
                            <div id="addVitalsModal" class="modal-overlay">
                                <div class="modal-content">
                                    <button class="modal-close" data-modal-close>&times;</button>
                                    <h3 class="modal-title"><i class="bi bi-heart-pulse"></i> Record Patient Vitals</h3>
                                    <form action="" method="post">
                                        <input type="hidden" name="action" value="save_vitals">
                                        <div class="form-grid">
                                            <div class="form-group"><label>Temp (°C)</label><input type="number" step="0.1" name="temperature"></div>
                                            <div class="form-group"><label>Weight (kg)</label><input type="number" step="0.1" name="weight"></div>
                                            <div class="form-group"><label>Height (cm)</label><input type="number" step="0.1" name="height"></div>
                                            <div class="form-group"><label>Systolic BP</label><input type="number" name="systolic_bp"></div>
                                            <div class="form-group"><label>Diastolic BP</label><input type="number" name="diastolic_bp"></div>
                                            <div class="form-group"><label>Pulse (bpm)</label><input type="number" name="pulse"></div>
                                            <div class="form-group"><label>SpO2 (%)</label><input type="number" name="oxygen_saturation"></div>
                                            <div class="form-group"><label>Respiration</label><input type="number" name="respiration"></div>
                                        </div>
                                        <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width:100%;">Save Vitals</button>
                                    </form>
                                </div>
                            </div>
                        <?php } else { echo "<p>No vitals recorded yet.</p>"; } ?>
                    <?php } ?>
                </div>

                <!-- Right: Nursing Notes -->
                <div class="card">
                    <h3>Nursing Notes</h3>
                    <div class="notes-list" style="max-height: 200px; overflow-y:auto; margin-bottom:15px;">
                        <?php while($n = $nursing_notes->fetch_assoc()) { ?>
                            <div class="note-item" style="padding:10px; border-bottom:1px solid #eee;">
                                <p style="margin:0;"><?php echo nl2br(v_wrap($n['notes'])); ?></p>
                                <small style="color:#888;">Added at <?php echo $n['created_at']; ?></small>
                            </div>
                        <?php } ?>
                    </div>
                    <?php if (hasPermission('add_nursing_notes')) { ?>
                        <button data-modal-target="addNursingNoteModal" class="btn btn-primary" style="margin-top:10px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px;">+ Add Note</button>
                        
                        <!-- Add Nursing Note Modal -->
                        <div id="addNursingNoteModal" class="modal-overlay">
                            <div class="modal-content">
                                <button class="modal-close" data-modal-close>&times;</button>
                                <h3 class="modal-title"><i class="bi bi-journal-text"></i> Add Nursing Note</h3>
                                <form action="" method="post">
                                    <input type="hidden" name="action" value="save_nursing_note">
                                    <textarea name="notes" rows="4" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;" placeholder="Add new nursing note..."></textarea>
                                    <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width:100%;">Save Note</button>
                                </form>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>

        <!-- CONSULTATION TAB -->
        <?php if (hasPermission('view_consultation')) { ?>
        <div id="consultation" class="tab-content" hidden>
            <div class="card">
                <h3>Doctor's Consultation</h3>
                <form action="" method="post">
                    <input type="hidden" name="action" value="save_consultation">
                    
                    <div class="form-group">
                        <label>History of Present Illness</label>
                        <textarea name="hpi" rows="4" style="width:100%; border-radius:5px; padding:10px;"><?php echo v_wrap($consultation['history_of_present_illness'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Physical Examination</label>
                        <textarea name="exam" rows="3" style="width:100%; border-radius:5px; padding:10px;"><?php echo v_wrap($consultation['physical_examination'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Assessment</label>
                        <textarea name="assessment" rows="2" style="width:100%; border-radius:5px; padding:10px;"><?php echo v_wrap($consultation['assessment'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Management Plan</label>
                        <textarea name="plan" rows="3" style="width:100%; border-radius:5px; padding:10px;"><?php echo v_wrap($consultation['management_plan'] ?? ''); ?></textarea>
                    </div>

                    <?php if (hasPermission('edit_consultation')) { ?>
                        <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px;">Save Consultation</button>
                    <?php } ?>
                </form>
            </div>
        </div>
        
        <!-- DIAGNOSIS TAB -->
        <div id="diagnosis" class="tab-content" hidden>
            <div class="card">
                <h3>Diagnoses</h3>
                <table class="staff-list" style="margin-bottom: 20px;">
                    <tr><th>Type</th><th>Diagnosis</th><th>ICD Code</th><th>Notes</th><th>Date</th></tr>
                    <?php while($diag = $diagnoses->fetch_assoc()) { ?>
                    <tr>
                        <td><span class="badge status-<?php echo strtolower($diag['diagnosis_type']); ?>"><?php echo v_wrap($diag['diagnosis_type']); ?></span></td>
                        <td><?php echo v_wrap($diag['diagnosis']); ?></td>
                        <td><?php echo v_wrap($diag['icd_code']); ?></td>
                        <td><?php echo v_wrap($diag['notes']); ?></td>
                        <td><?php echo date('d M Y', strtotime($diag['created_at'])); ?></td>
                    </tr>
                    <?php } ?>
                </table>

                <?php if (hasPermission('add_diagnosis')) { ?>
                <button data-modal-target="addDiagnosisModal" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px;">+ Add Diagnosis</button>
                
                <!-- Add Diagnosis Modal -->
                <div id="addDiagnosisModal" class="modal-overlay">
                    <div class="modal-content">
                        <button class="modal-close" data-modal-close>&times;</button>
                        <h3 class="modal-title"><i class="bi bi-clipboard2-pulse"></i> Add New Diagnosis</h3>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="save_diagnosis">
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Type</label>
                                <select name="diagnosis_type" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                    <option value="Provisional">Provisional</option>
                                    <option value="Confirmed">Confirmed</option>
                                    <option value="Final">Final</option>
                                </select>
                            </div>
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Diagnosis Description *</label>
                                <input type="text" name="diagnosis" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>ICD-10 Code</label>
                                <input type="text" name="icd_code" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Notes</label>
                                <textarea name="notes" rows="2" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:10px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width:100%;">Save Diagnosis</button>
                        </form>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>

        <!-- PRESCRIPTION TAB -->
        <div id="prescription" class="tab-content" hidden>
            <div class="card">
                <h3>Prescriptions</h3>
                <table class="staff-list" style="margin-bottom: 20px;">
                    <tr><th>Medication</th><th>Dosage & Frequency</th><th>Duration</th><th>Instructions</th><th>Status</th></tr>
                    <?php while($rx = $prescriptions->fetch_assoc()) { ?>
                    <tr>
                        <td><strong><?php echo v_wrap($rx['medication_name']); ?></strong></td>
                        <td><?php echo v_wrap($rx['dosage'] . ' - ' . $rx['frequency']); ?></td>
                        <td><?php echo v_wrap($rx['duration']); ?></td>
                        <td><?php echo v_wrap($rx['instructions']); ?></td>
                        <td><span class="badge status-<?php echo strtolower($rx['status']); ?>"><?php echo v_wrap($rx['status']); ?></span></td>
                    </tr>
                    <?php } ?>
                </table>

                <?php if (hasPermission('add_prescription')) { ?>
                <button data-modal-target="addPrescriptionModal" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px;">+ Write Prescription</button>
                
                <!-- Add Prescription Modal -->
                <div id="addPrescriptionModal" class="modal-overlay">
                    <div class="modal-content">
                        <button class="modal-close" data-modal-close>&times;</button>
                        <h3 class="modal-title"><i class="bi bi-prescription2"></i> Write New Prescription</h3>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="save_prescription">
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Medication *</label>
                                <select name="inventory_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                    <option value="">-- Select from Inventory --</option>
                                    <?php while($inv = $inventory_items->fetch_assoc()) { ?>
                                        <option value="<?php echo $inv['id']; ?>"><?php echo v_wrap($inv['drug_name'] . ' (' . $inv['stock_quantity'] . ' in stock)'); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:15px;">
                                <div class="form-group">
                                    <label>Dosage</label>
                                    <input type="text" name="dosage" placeholder="e.g. 500mg" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                </div>
                                <div class="form-group">
                                    <label>Frequency</label>
                                    <input type="text" name="frequency" placeholder="e.g. BD" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                </div>
                            </div>
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Duration</label>
                                <input type="text" name="duration" placeholder="e.g. 5 days" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Special Instructions</label>
                                <input type="text" name="instructions" placeholder="e.g. Take after food" style="width:100%; padding:10px; border-radius:5px; border:1px solid #ddd;">
                            </div>
                            <button type="submit" class="btn btn-primary" style="margin-top:10px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width:100%;">Add Prescription</button>
                        </form>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>

    </main>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
