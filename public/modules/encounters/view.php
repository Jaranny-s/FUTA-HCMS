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

// --- Doctor Authorization ---
// Doctors can VIEW any encounter (for referral/handover context)
// but can only WRITE (consultation, diagnosis, prescription) to their OWN encounters.
$current_role = $_SESSION['staff_role'] ?? '';
$current_staff_id = $_SESSION['staff_id'] ?? null;
$is_assigned_doctor = ($encounter['doctor_id'] == $current_staff_id);
$is_doctor = ($current_role === 'doctor');
$is_privileged = in_array($current_role, ['admin', 'super_admin']);

// A doctor or unauthorized staff trying to write to someone else's encounter is blocked at form level.
// We capture this flag and pass it to the UI so clinical write buttons are hidden/disabled.
$is_encounter_active = ($encounter['status'] !== 'Completed' && $encounter['status'] !== 'Cancelled');
$can_write_clinical = $is_encounter_active && (($is_doctor && $is_assigned_doctor) || $is_privileged);
$can_record_vitals = $is_encounter_active && (hasPermission('record_vitals') || hasPermission('edit_vitals') || in_array($current_role, ['nurse', 'doctor', 'admin', 'super_admin']));

// Handle POST submissions
if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    $staff_id = $_SESSION['staff_id'];

    // Enforce clinical permissions: only assigned doctor or admin/super_admin can write consultation, diagnosis, prescription, complete, or reassign
    $clinical_write_actions = ['save_consultation', 'save_diagnosis', 'save_prescription', 'complete_encounter', 'reassign_encounter'];
    if (!$can_write_clinical && in_array($action, $clinical_write_actions)) {
        $_SESSION['error'] = "Access Denied: Only the assigned doctor or clinic administrators can perform this action.";
        redirect_to(url_wrap("/modules/encounters/view.php?id={$encounter_id}"));
    }

    if ($action === 'save_vitals') {
        $temp = !empty($_POST['temperature']) ? (float)$_POST['temperature'] : null;
        $weight = !empty($_POST['weight']) ? (float)$_POST['weight'] : null;
        $height = !empty($_POST['height']) ? (float)$_POST['height'] : null;
        $pulse = !empty($_POST['pulse']) ? (int)$_POST['pulse'] : null;
        $respiration = !empty($_POST['respiration']) ? (int)$_POST['respiration'] : null;
        $oxygen = !empty($_POST['oxygen_saturation']) ? (int)$_POST['oxygen_saturation'] : null;
        $sys_bp = !empty($_POST['systolic_bp']) ? (int)$_POST['systolic_bp'] : null;
        $dia_bp = !empty($_POST['diastolic_bp']) ? (int)$_POST['diastolic_bp'] : null;

        $bmi = null;
        if ($weight && $height && $height > 0) {
            $height_m = $height / 100.0;
            $bmi = round($weight / ($height_m * $height_m), 1);
        }

        save_vitals($encounter_id, $staff_id, $temp, $weight, $height, $bmi, $pulse, $respiration, $oxygen, $sys_bp, $dia_bp);
        update_encounter_status($encounter_id, 'In Progress');
        $_SESSION['message'] = "Vitals recorded successfully. Patient is now in progress for doctor consultation.";
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
        $inv_val = $_POST['inventory_id'] ?? '';
        $inv_id = ($inv_val !== 'custom' && !empty($inv_val)) ? (int)$inv_val : null;
        $custom_name = $_POST['custom_medication_name'] ?? '';
        save_prescription($encounter_id, $staff_id, $inv_id, $_POST['dosage'], $_POST['frequency'], $_POST['duration'], $_POST['instructions'], $custom_name);
        $_SESSION['message'] = "Prescription added successfully.";
    } elseif ($action === 'complete_encounter') {
        // Enforce clinical prerequisites before marking completed
        $check_vitals = get_vitals($encounter_id);
        $check_nursing = get_nursing_notes($encounter_id);
        $check_consult = get_consultation($encounter_id);
        $check_diag = get_diagnoses($encounter_id);

        $ok_vn = !empty($check_vitals) || ($check_nursing && $check_nursing->num_rows > 0);
        $ok_c = !empty($check_consult) && (
            !empty(trim($check_consult['history_of_present_illness'] ?? '')) || 
            !empty(trim($check_consult['physical_examination'] ?? '')) || 
            !empty(trim($check_consult['assessment'] ?? '')) || 
            !empty(trim($check_consult['management_plan'] ?? ''))
        );
        $ok_d = ($check_diag && $check_diag->num_rows > 0);

        if (!$ok_vn || !$ok_c || !$ok_d) {
            $_SESSION['error'] = "Cannot complete encounter yet: Vitals/Nursing notes, Consultation, and Diagnosis must all be recorded first.";
        } else {
            update_encounter_status($encounter_id, 'Completed');
            if ($is_doctor && $current_staff_id) {
                $check_other = $db_1->query("SELECT COUNT(*) as c FROM encounters WHERE doctor_id = {$current_staff_id} AND status = 'In Progress' AND id != {$encounter_id}");
                if ($check_other && (int)$check_other->fetch_assoc()['c'] === 0) {
                    $db_1->query("UPDATE staff SET duty_status = 'Available' WHERE id = " . (int)$current_staff_id);
                }
            }
            $_SESSION['message'] = "Encounter marked as Completed successfully.";
        }
    } elseif ($action === 'reassign_encounter') {
        if ($is_privileged) {
            $pin = trim($_POST['supervisor_pin'] ?? '');
            if (empty($pin) || !verify_staff_reverification_pin($staff_id, $pin)) {
                $_SESSION['error'] = "Authorization failed: Invalid supervisor PIN or password.";
                redirect_to(url_wrap("/modules/encounters/view.php?id={$encounter_id}"));
            }
        }
        $target_doctor_id = (int)($_POST['target_doctor_id'] ?? 0);
        $reason_cat = trim($_POST['reassign_reason'] ?? '');
        $notes = trim($_POST['reassign_notes'] ?? '');
        $full_reason = $reason_cat . (!empty($notes) ? " - " . $notes : "");

        if ($target_doctor_id <= 0) {
            $_SESSION['error'] = "Please select a target doctor for reassignment.";
        } elseif (empty($full_reason)) {
            $_SESSION['error'] = "Please select or provide a transfer reason.";
        } else {
            $ok = reassign_encounter($encounter_id, $target_doctor_id, $full_reason, $staff_id);
            if ($ok) {
                if ($is_doctor && $current_staff_id) {
                    $check_other = $db_1->query("SELECT COUNT(*) as c FROM encounters WHERE doctor_id = {$current_staff_id} AND status = 'In Progress' AND id != {$encounter_id}");
                    if ($check_other && (int)$check_other->fetch_assoc()['c'] === 0) {
                        $db_1->query("UPDATE staff SET duty_status = 'Available' WHERE id = " . (int)$current_staff_id);
                    }
                }
                $targetDoc = find_staff_by_id($target_doctor_id);
                $docName = $targetDoc['full_name'] ?? 'Doctor';
                $_SESSION['message'] = "Encounter successfully reassigned to Dr. {$docName}.";
                redirect_to(url_wrap('/modules/encounters/index.php'));
            } else {
                $_SESSION['error'] = "Failed to reassign encounter.";
            }
        }
    } elseif ($action === 'cancel_encounter') {
        if ($is_privileged) {
            $pin = trim($_POST['supervisor_pin'] ?? '');
            if (empty($pin) || !verify_staff_reverification_pin($staff_id, $pin)) {
                $_SESSION['error'] = "Authorization failed: Invalid supervisor PIN or password.";
                redirect_to(url_wrap("/modules/encounters/view.php?id={$encounter_id}"));
            }
        }
        $category = trim($_POST['cancel_category'] ?? '');
        $notes = trim($_POST['cancel_notes'] ?? '');
        $full_reason = $category . (!empty($notes) ? " - " . $notes : "");
        
        if (empty($category) && empty($notes)) {
            $_SESSION['error'] = "A reason must be selected or provided to void this encounter.";
        } elseif (!in_array($current_role, ['doctor', 'receptionist', 'admin', 'super_admin'])) {
            $_SESSION['error'] = "Access Denied: You do not have permission to void encounters.";
        } else {
            cancel_encounter($encounter_id, $full_reason, $staff_id);
            $_SESSION['message'] = "Encounter #{$encounter['encounter_number']} has been voided/cancelled.";
        }
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

// Encounter completion prerequisites check
$has_vitals = !empty($vitals);
$has_nursing = ($nursing_notes && $nursing_notes->num_rows > 0);
$has_vitals_nursing = $has_vitals || $has_nursing;

$has_consultation = !empty($consultation) && (
    !empty(trim($consultation['history_of_present_illness'] ?? '')) || 
    !empty(trim($consultation['physical_examination'] ?? '')) || 
    !empty(trim($consultation['assessment'] ?? '')) || 
    !empty(trim($consultation['management_plan'] ?? ''))
);

$has_diagnosis = ($diagnoses && $diagnoses->num_rows > 0);

// Prescriptions are optional; required: vitals & nursing, consultation, diagnosis
$can_complete_encounter = $has_vitals_nursing && $has_consultation && $has_diagnosis;
$can_prescribe = (hasPermission('prescribe_medication') || in_array($current_role, ['doctor', 'super_admin'])) && $can_write_clinical;

if ($is_doctor && $is_assigned_doctor && $encounter['status'] === 'In Progress') {
    $db_1->query("UPDATE staff SET duty_status = 'In Consultation' WHERE id = " . (int)$current_staff_id);
}

$doctor_workloads = ($is_encounter_active) ? get_doctors_workload_summary() : [];

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

        <?php if (!empty($encounter['transfer_reason'])): 
            $fromDocName = 'Previous Doctor';
            if (!empty($encounter['transferred_from'])) {
                $fDoc = find_staff_by_id((int)$encounter['transferred_from']);
                if ($fDoc) $fromDocName = 'Dr. ' . $fDoc['full_name'];
            }
        ?>
        <div style="background:#f0f9ff; border:1px solid #bae6fd; border-left:4px solid #0284c7; border-radius:8px; padding:12px 18px; margin-bottom:20px; display:flex; align-items:center; gap:12px;">
            <i class="bi bi-arrow-left-right" style="font-size:1.4rem; color:#0284c7;"></i>
            <div>
                <strong style="color:#0369a1;">Reassigned Clinical Encounter:</strong>
                <span style="color:#334155; font-size:0.9rem;"> Transferred from <strong><?php echo htmlspecialchars($fromDocName); ?></strong>. Reason: <em><?php echo htmlspecialchars($encounter['transfer_reason']); ?></em></span>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($encounter['status'] === 'Cancelled'): ?>
        <div style="background:#fff2f2; border:1px solid #f5c2c7; border-left:5px solid #dc3545; border-radius:8px; padding:16px 20px; margin-bottom:20px; display:flex; align-items:flex-start; gap:14px;">
            <i class="bi bi-x-octagon-fill" style="font-size:1.6rem; color:#dc3545; line-height:1; margin-top:2px;"></i>
            <div>
                <strong style="color:#b02a37; font-size:1.05rem; display:block; margin-bottom:4px;">Encounter Voided / Cancelled</strong>
                <span style="color:#555; font-size:0.92rem;">
                    <strong>Cancelled on:</strong> <?php echo !empty($encounter['cancelled_at']) ? date('d M Y, h:i A', strtotime($encounter['cancelled_at'])) : date('d M Y, h:i A', strtotime($encounter['created_at'])); ?> &bull; 
                    <strong>Reason:</strong> <?php echo v_wrap($encounter['cancel_reason'] ?? 'No reason recorded'); ?>
                </span>
                <div style="margin-top:6px; font-size:0.85rem; color:#777;">This encounter is closed and all clinical documentation is retained in read-only mode for medical audit purposes.</div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($is_doctor && !$is_assigned_doctor): ?>
        <div style="background:#fff3cd; border:1px solid #ffc107; border-left:4px solid #e6a000; border-radius:8px; padding:12px 18px; margin-bottom:20px; display:flex; align-items:center; gap:12px;">
            <i class="bi bi-eye" style="font-size:1.3rem; color:#856404;"></i>
            <div>
                <strong style="color:#856404;">View-only mode.</strong>
                <span style="color:#666; font-size:0.9rem;"> This encounter is assigned to <strong><?php echo v_wrap($encounter['doctor_name']); ?></strong>. You can read all records but cannot add or modify clinical entries.</span>
            </div>
        </div>
        <?php endif; ?>

        <div class="patient-summary-card">
            <div class="patient-info">
                <?php 
                $patient_pic = url_wrap('/assets/images/default_profile_pic.png');
                if (!empty($encounter['profile_image']) && $encounter['profile_image'] !== 'default_profile_pic.png') {
                    $pic_file = dirname(__DIR__) . '/patients/images/patient_pictures/' . $encounter['profile_image'];
                    if (file_exists($pic_file)) {
                        $patient_pic = url_wrap('/modules/patients/images/patient_pictures/' . v_wrap($encounter['profile_image']));
                    }
                }
                ?>
                <img src="<?php echo $patient_pic; ?>" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/default_profile_pic.png'); ?>';" alt="" class="workspace-avatar">
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
            
            <div class="encounter-status" style="display: flex; flex-direction: column; align-items: flex-end;">
                <div>
                    <span class="badge status-<?php echo str_replace(' ', '-', strtolower($encounter['status'])); ?>">
                        Status: <?php echo v_wrap($encounter['status']); ?>
                    </span>
                </div>
                
                <div style="margin-top: 10px; display: flex; gap: 8px; align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                    <?php if ($is_encounter_active && ($is_assigned_doctor || $is_privileged)) { ?>
                    <button type="button" data-modal-target="reassignEncounterModal" onclick="openReassignModal()" class="btn" style="background: #fffbeb; color: #92400e; border: 1px solid #fde68a; padding: 8px 14px; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="bi bi-arrow-left-right"></i> Transfer / Reassign
                    </button>
                    <?php } ?>

                    <?php if ($encounter['status'] !== 'Completed' && $can_write_clinical) { ?>
                        <?php if ($can_complete_encounter) { ?>
                        <form action="<?php echo url_wrap("/modules/encounters/view.php?id={$encounter_id}"); ?>" method="post" style="display:inline-block; margin: 0;">
                            <input type="hidden" name="action" value="complete_encounter">
                            <button type="submit" class="btn btn-success" style="background:#1bc03d; color:white; border:none; padding:8px 18px; border-radius:6px; cursor:pointer; font-weight:600; font-size:0.92rem; box-shadow:0 3px 6px rgba(27,192,61,0.25);">
                                <i class="bi bi-check-circle-fill"></i> Complete Encounter
                            </button>
                        </form>
                        <?php } else { ?>
                        <button type="button" class="btn" disabled style="background:#f1f3f5; color:#868e96; border:1px solid #ced4da; padding:8px 16px; border-radius:6px; cursor:not-allowed; font-size:0.9rem; font-weight:500;" title="Complete all clinical prerequisites to enable">
                            <i class="bi bi-lock-fill"></i> Complete Encounter
                        </button>
                        <?php } ?>
                    <?php } ?>
                </div>

                <?php if ($encounter['status'] !== 'Completed' && $can_write_clinical && !$can_complete_encounter) { ?>
                <div style="font-size: 0.78rem; margin-top: 6px; color: #555; text-align: right;">
                    <span style="font-weight:600; color:#444;">Required to complete:</span><br>
                    <span style="color: <?php echo $has_vitals_nursing ? '#198754' : '#dc3545'; ?>; font-weight: 500;">
                        <i class="bi <?php echo $has_vitals_nursing ? 'bi-check-circle-fill' : 'bi-x-circle'; ?>"></i> Vitals & Nursing
                    </span> &bull; 
                    <span style="color: <?php echo $has_consultation ? '#198754' : '#dc3545'; ?>; font-weight: 500;">
                        <i class="bi <?php echo $has_consultation ? 'bi-check-circle-fill' : 'bi-x-circle'; ?>"></i> Consultation
                    </span> &bull; 
                    <span style="color: <?php echo $has_diagnosis ? '#198754' : '#dc3545'; ?>; font-weight: 500;">
                        <i class="bi <?php echo $has_diagnosis ? 'bi-check-circle-fill' : 'bi-x-circle'; ?>"></i> Diagnosis
                    </span>
                    <div style="font-size:0.72rem; color:#888; margin-top:2px;">(Prescription is optional)</div>
                </div>
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
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h3 style="margin: 0;">Patient Vitals</h3>
                        <?php if ($vitals && $can_record_vitals) { ?>
                            <button data-modal-target="addVitalsModal" class="btn" style="background:#0F4E74; color:white; border:none; padding:5px 12px; border-radius:4px; font-size:0.85rem; cursor:pointer;">
                                <i class="bi bi-pencil-square"></i> Update Vitals
                            </button>
                        <?php } ?>
                    </div>
                    <?php if ($vitals) { ?>
                        <div class="vitals-display" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 10px; margin-top: 5px;">
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">Blood Pressure</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['systolic_bp'] . '/' . $vitals['diastolic_bp']); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">mmHg</span></strong>
                            </div>
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">Temperature</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['temperature']); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">°C</span></strong>
                            </div>
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">Pulse Rate</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['pulse']); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">bpm</span></strong>
                            </div>
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">SpO2</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['oxygen_saturation']); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">%</span></strong>
                            </div>
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">Weight</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['weight']); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">kg</span></strong>
                            </div>
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">Height</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['height']); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">cm</span></strong>
                            </div>
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">BMI</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['bmi'] ?? 'N/A'); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">kg/m²</span></strong>
                            </div>
                            <div style="background:#f8f9fa; padding:10px; border-radius:6px; border:1px solid #e9ecef;">
                                <small style="color:#666; display:block;">Respiration</small>
                                <strong style="font-size:1.05rem; color:#0F4E74;"><?php echo v_wrap($vitals['respiration'] ?? 'N/A'); ?> <span style="font-size:0.75rem; font-weight:normal; color:#666;">cpm</span></strong>
                            </div>
                        </div>
                    <?php } else { ?>
                        <?php if ($can_record_vitals) { ?>
                            <button data-modal-target="addVitalsModal" class="btn btn-primary" style="margin-bottom:15px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">+ Record New Vitals</button>
                        <?php } else { echo "<p style='color:#666;'>No vitals recorded yet.</p>"; } ?>
                    <?php } ?>

                    <!-- Add/Update Vitals Modal -->
                    <?php if ($can_record_vitals) { ?>
                    <div id="addVitalsModal" class="modal-overlay">
                        <div class="modal-content" style="max-width:550px;">
                            <button class="modal-close" data-modal-close>&times;</button>
                            <h3 class="modal-title"><i class="bi bi-heart-pulse"></i> <?php echo $vitals ? 'Update' : 'Record'; ?> Patient Vitals</h3>
                            <form action="" method="post">
                                <input type="hidden" name="action" value="save_vitals">
                                <div class="form-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                    <div class="form-group"><label>Temperature (°C)</label><input type="number" step="0.1" name="temperature" value="<?php echo htmlspecialchars($vitals['temperature'] ?? ''); ?>" placeholder="e.g. 36.8" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                    <div class="form-group"><label>Weight (kg)</label><input type="number" step="0.1" name="weight" value="<?php echo htmlspecialchars($vitals['weight'] ?? ''); ?>" placeholder="e.g. 68.5" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                    <div class="form-group"><label>Height (cm)</label><input type="number" step="0.1" name="height" value="<?php echo htmlspecialchars($vitals['height'] ?? ''); ?>" placeholder="e.g. 175" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                    <div class="form-group"><label>Pulse (bpm)</label><input type="number" name="pulse" value="<?php echo htmlspecialchars($vitals['pulse'] ?? ''); ?>" placeholder="e.g. 72" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                    <div class="form-group"><label>Systolic BP (mmHg)</label><input type="number" name="systolic_bp" value="<?php echo htmlspecialchars($vitals['systolic_bp'] ?? ''); ?>" placeholder="e.g. 120" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                    <div class="form-group"><label>Diastolic BP (mmHg)</label><input type="number" name="diastolic_bp" value="<?php echo htmlspecialchars($vitals['diastolic_bp'] ?? ''); ?>" placeholder="e.g. 80" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                    <div class="form-group"><label>SpO2 (% Oxygen)</label><input type="number" name="oxygen_saturation" value="<?php echo htmlspecialchars($vitals['oxygen_saturation'] ?? ''); ?>" placeholder="e.g. 98" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                    <div class="form-group"><label>Respiration (breaths/min)</label><input type="number" name="respiration" value="<?php echo htmlspecialchars($vitals['respiration'] ?? ''); ?>" placeholder="e.g. 16" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;"></div>
                                </div>
                                <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width:100%; cursor:pointer; font-weight:600;">Save Vitals</button>
                            </form>
                        </div>
                    </div>
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
                        <textarea name="hpi" rows="4" style="width:100%; border-radius:5px; padding:10px;" <?php if(!$can_write_clinical) echo 'readonly'; ?>><?php echo v_wrap($consultation['history_of_present_illness'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Physical Examination</label>
                        <textarea name="exam" rows="3" style="width:100%; border-radius:5px; padding:10px;" <?php if(!$can_write_clinical) echo 'readonly'; ?>><?php echo v_wrap($consultation['physical_examination'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Assessment</label>
                        <textarea name="assessment" rows="2" style="width:100%; border-radius:5px; padding:10px;" <?php if(!$can_write_clinical) echo 'readonly'; ?>><?php echo v_wrap($consultation['assessment'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Management Plan</label>
                        <textarea name="plan" rows="3" style="width:100%; border-radius:5px; padding:10px;" placeholder="e.g. Advise rest, dietary adjustments, prescribed medication..." <?php if(!$can_write_clinical) echo 'readonly'; ?>><?php echo v_wrap($consultation['management_plan'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-group" style="margin-top: 12px;">
                        <label>Follow-up Instructions / Patient Advice</label>
                        <textarea name="follow_up" rows="2" style="width:100%; border-radius:5px; padding:10px;" placeholder="e.g. Return for routine BP review in 2 weeks, or sooner if feeling unwell." <?php if(!$can_write_clinical) echo 'readonly'; ?>><?php echo v_wrap($consultation['follow_up_instructions'] ?? ''); ?></textarea>
                    </div>

                    <?php if (hasPermission('edit_consultation') && $can_write_clinical) { ?>
                        <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:600;">Save Consultation</button>
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

                <?php if (hasPermission('add_diagnosis') && $can_write_clinical) { ?>
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

                <?php if ($can_prescribe) { ?>
                <button data-modal-target="addPrescriptionModal" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px; cursor:pointer;">+ Write Prescription</button>
                
                <!-- Add Prescription Modal -->
                <div id="addPrescriptionModal" class="modal-overlay">
                    <div class="modal-content">
                        <button class="modal-close" data-modal-close>&times;</button>
                        <h3 class="modal-title"><i class="bi bi-prescription2"></i> Write New Prescription</h3>
                        <form action="" method="post">
                            <input type="hidden" name="action" value="save_prescription">
                            <div class="form-group" style="margin-bottom:15px;">
                                <label>Medication *</label>
                                <select name="inventory_id" id="med_inventory_select" onchange="toggleMedCustom(this.value)" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                    <option value="">-- Select from Inventory --</option>
                                    <?php 
                                    if ($inventory_items && $inventory_items->num_rows > 0) {
                                        $inventory_items->data_seek(0);
                                        while($inv = $inventory_items->fetch_assoc()) { ?>
                                        <option value="<?php echo $inv['id']; ?>"><?php echo v_wrap($inv['drug_name'] . ' (' . $inv['stock_quantity'] . ' in stock)'); ?></option>
                                    <?php } } ?>
                                    <option value="custom">+ Other / Custom Medication (Not in stock)</option>
                                </select>
                            </div>
                            <div id="custom_med_field" class="form-group" style="margin-bottom:15px; display:none;">
                                <label>Custom Medication Name *</label>
                                <input type="text" name="custom_medication_name" placeholder="e.g. Augmentin 625mg or Cough syrup" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            </div>
                            <script>
                            function toggleMedCustom(val) {
                                var customDiv = document.getElementById('custom_med_field');
                                var customInput = customDiv.querySelector('input');
                                if (val === 'custom') {
                                    customDiv.style.display = 'block';
                                    customInput.required = true;
                                } else {
                                    customDiv.style.display = 'none';
                                    customInput.required = false;
                                    customInput.value = '';
                                }
                            }
                            </script>
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

        <?php if ($is_encounter_active && in_array($current_role, ['doctor', 'receptionist', 'admin', 'super_admin'])) { ?>
        <!-- Danger Zone: Void Encounter -->
        <div style="margin-top: 40px; border: 1px solid #f5c2c7; background: #fffdfd; border-radius: 8px; padding: 18px 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <div>
                <h4 style="margin: 0 0 4px 0; color: #842029; font-size: 1rem;"><i class="bi bi-shield-exclamation"></i> Administrative Option: Void Encounter</h4>
                <p style="margin: 0; color: #666; font-size: 0.85rem;">Permanently cancel this encounter if patient left without being seen (LWBS), accidental check-in, or referred/transferred out.</p>
            </div>
            <div>
                <button type="button" data-modal-target="voidEncounterModal" class="btn" style="background: transparent; color: #dc3545; border: 1px solid #dc3545; padding: 7px 16px; border-radius: 5px; font-weight: 600; font-size: 0.88rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dc3545'; this.style.color='white';" onmouseout="this.style.background='transparent'; this.style.color='#dc3545';">
                    <i class="bi bi-x-circle"></i> Void Encounter
                </button>
            </div>
        </div>

        <!-- Void Encounter Confirmation Modal -->
        <div id="voidEncounterModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 520px; text-align: left;">
                <button class="modal-close" data-modal-close>&times;</button>
                <h3 class="modal-title" style="color: #dc3545; margin-bottom: 12px;"><i class="bi bi-exclamation-triangle-fill"></i> Void Clinical Encounter</h3>
                
                <div style="background: #f8d7da; border: 1px solid #f5c2c7; color: #721c24; padding: 12px 15px; border-radius: 6px; font-size: 0.88rem; margin-bottom: 18px;">
                    <strong>Caution:</strong> Voiding will permanently cancel this encounter, remove it from the active clinic queue, and cancel any linked appointment. This action cannot be reversed.
                </div>

                <form action="" method="post">
                    <input type="hidden" name="action" value="cancel_encounter">
                    
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display:block; margin-bottom:6px; font-weight:500;">Reason for Cancellation *</label>
                        <select name="cancel_category" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                            <option value="">-- Select Reason --</option>
                            <option value="Patient Left Without Being Seen (LWBS)">Patient Left Without Being Seen (LWBS)</option>
                            <option value="Duplicate / Accidental Check-in">Duplicate / Accidental Check-in</option>
                            <option value="Patient Left Against Medical Advice (LAMA)">Patient Left Against Medical Advice (LAMA)</option>
                            <option value="Emergency Transfer to External Facility">Emergency Transfer to External Facility</option>
                            <option value="Patient Refused Treatment">Patient Refused Treatment</option>
                            <option value="Other Administrative Reason">Other Administrative Reason</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 18px;">
                        <label style="display:block; margin-bottom:6px; font-weight:500;">Detailed Explanation / Clinical Notes</label>
                        <textarea name="cancel_notes" rows="3" placeholder="e.g. Student had to leave for lecture before consultation; vitals were normal." style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></textarea>
                    </div>

                    <?php if ($is_privileged): ?>
                    <div style="margin-bottom: 18px; padding: 12px; background: #fff5f5; border: 1px solid #fed7d7; border-radius: 6px;">
                        <label style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; color: #742a2a;">
                            <i class="bi bi-shield-lock-fill" style="color: #c53030;"></i> Supervisor Authorization PIN *
                        </label>
                        <p style="font-size: 0.78rem; color: #9b2c2c; margin-top: 0; margin-bottom: 8px;">Enter your 4-digit supervisor PIN (or account password) to authorize voiding this clinical encounter.</p>
                        <input type="password" name="supervisor_pin" maxlength="30" required placeholder="Enter 4-digit PIN" style="width: 100%; padding: 8px 12px; border: 1px solid #feb2b2; border-radius: 6px; font-size: 0.9rem; letter-spacing: 2px;">
                    </div>
                    <?php endif; ?>

                    <div style="margin-bottom: 20px; display: flex; align-items: flex-start; gap: 8px;">
                        <input type="checkbox" id="confirm_void_chk" required onchange="document.getElementById('void_submit_btn').disabled = !this.checked;" style="margin-top: 3px;">
                        <label for="confirm_void_chk" style="font-size: 0.85rem; color: #444; cursor: pointer;">
                            I confirm that this encounter should be permanently voided and closed.
                        </label>
                    </div>

                    <button type="submit" id="void_submit_btn" disabled class="btn" style="background: #dc3545; color: white; border: none; padding: 12px; border-radius: 6px; width: 100%; font-weight: 600; cursor: pointer;">
                        <i class="bi bi-x-circle-fill"></i> Confirm & Void Encounter
                    </button>
                </form>
            </div>
        </div>
        <?php } ?>
        
        <?php if ($is_encounter_active && ($is_assigned_doctor || $is_privileged)) { ?>
        <!-- Transfer / Reassign Encounter Modal -->
        <div id="reassignEncounterModal" class="modal-overlay">
            <div class="modal-content" style="max-width: 540px; text-align: left;">
                <button type="button" class="modal-close" data-modal-close onclick="closeReassignModal()">&times;</button>
                <h3 class="modal-title" style="color: #0d6efd; margin-top: 0; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                    <i class="bi bi-arrow-left-right"></i> Transfer / Handover Patient
                </h3>
                
                <p style="font-size: 0.88rem; color: #555; margin-bottom: 16px; line-height: 1.4;">
                    Transfer <strong><?php echo htmlspecialchars($encounter['patient_first'] . ' ' . $encounter['patient_last']); ?></strong> to another physician. All recorded vitals, nursing notes, and preliminary entries are preserved intact.
                </p>

                <form action="" method="post">
                    <input type="hidden" name="action" value="reassign_encounter">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #333;">Select Doctor to Receive Patient *</label>
                        <select name="target_doctor_id" required style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.9rem;">
                            <option value="">-- Choose Doctor --</option>
                            <?php 
                            foreach ($doctor_workloads as $doc): 
                                if ((int)$doc['id'] === (int)$encounter['doctor_id']) continue; // skip current doctor
                                $statusBadge = ($doc['duty_status'] === 'Available') ? '🟢 Available' : (($doc['duty_status'] === 'In Consultation') ? '🔵 In Consultation' : (($doc['duty_status'] === 'On Break') ? '🟡 On Break' : '⚪ Off Duty'));
                            ?>
                            <option value="<?php echo $doc['id']; ?>">
                                Dr. <?php echo htmlspecialchars($doc['full_name']); ?> [<?php echo $statusBadge; ?> | Active Queue: <?php echo $doc['total_active_queue']; ?>]
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #333;">Reason for Transfer *</label>
                        <select name="reassign_reason" required style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.9rem;">
                            <option value="">-- Select Transfer Reason --</option>
                            <option value="Emergency / Duty Handover">Emergency / Doctor Shift Handover</option>
                            <option value="Specialist Clinical Second Opinion">Specialist / Clinical Second Opinion</option>
                            <option value="Queue Balancing">Queue Balancing / Clinic Workload</option>
                            <option value="Doctor Called to Urgent Case">Doctor Called to Ward / Emergency Room</option>
                            <option value="Patient Preference / Family Request">Patient Preference / Request</option>
                            <option value="Other Clinical Reason">Other Clinical Reason</option>
                        </select>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #333;">Handover Notes for Receiving Doctor (Optional)</label>
                        <textarea name="reassign_notes" rows="3" placeholder="e.g. Patient presents with acute abdominal pain; vitals stable. Handing over as shift ends." style="width: 100%; padding: 10px; border: 1px solid #ced4da; border-radius: 6px; font-size: 0.88rem; box-sizing: border-box;"></textarea>
                    </div>

                    <?php if ($is_privileged): ?>
                    <div style="margin-bottom: 18px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                        <label style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 4px; color: #1e293b;">
                            <i class="bi bi-shield-lock-fill" style="color: #0F4E74;"></i> Supervisor Authorization PIN *
                        </label>
                        <p style="font-size: 0.78rem; color: #64748b; margin-top: 0; margin-bottom: 8px;">Enter your 4-digit supervisor PIN (or account password) to authorize this override.</p>
                        <input type="password" name="supervisor_pin" maxlength="30" required placeholder="Enter 4-digit PIN" style="width: 100%; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 0.9rem; letter-spacing: 2px;">
                    </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: flex-end; gap: 10px;">
                        <button type="button" class="btn" data-modal-close onclick="closeReassignModal()" style="padding: 10px 18px; border: 1px solid #ccc; background: #fff; border-radius: 6px; cursor: pointer; font-weight: 500;">Cancel</button>
                        <button type="submit" style="padding: 10px 20px; border: none; background: #0d6efd; color: #fff; border-radius: 6px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                            <i class="bi bi-arrow-left-right"></i> Confirm Transfer
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <script>
        function openReassignModal() {
            var m = document.getElementById('reassignEncounterModal');
            if (m) m.classList.add('active');
        }
        function closeReassignModal() {
            var m = document.getElementById('reassignEncounterModal');
            if (m) m.classList.remove('active');
        }
        </script>
        <?php } ?>

        <script>
        // Keep-alive heartbeat: pings server every 4 minutes (240s) to keep consultation session alive while doctor types notes
        setInterval(function() {
            fetch('<?php echo url_wrap("/modules/ajax/keep_alive.php"); ?>', { credentials: 'same-origin' })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    // Heartbeat refreshed session
                })
                .catch(function(err) {
                    console.warn('Keep-alive ping failed:', err);
                });
        }, 240000);
        </script>

    </main>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
