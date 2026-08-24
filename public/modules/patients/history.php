<?php
require_once('../../../private/config.php');
require_password_reset();

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "Invalid patient record.";
    redirect_to(url_wrap('/modules/patients/index.php'));
}

$patient = find_patient_by_id((int)$id);
if (!$patient) {
    $_SESSION['error'] = "Patient not found.";
    redirect_to(url_wrap('/modules/patients/index.php'));
}

$history = get_patient_medical_history($patient['id']);

$page_title = "Patient History - " . v_wrap($patient['surname'] . ' ' . $patient['first_name']);
$specificCss = "/assets/css/encounters.css"; // Reuse the encounter styling

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    <main class="main-content">
        <a href="<?php echo url_wrap('/modules/patients/view.php?id=' . u_wrap($patient['id'])); ?>" class="btn btn-primary" id="link_layout"><i class="bi bi-arrow-left"></i> Back to Patient Record</a>
        
        <div class="top">
            <p class="top-head">Medical History</p>
            <p class="top-description"><?php echo v_wrap($patient['patient_id'] . ' - ' . $patient['surname'] . ' ' . $patient['first_name']); ?></p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div class="clinical-grid" style="grid-template-columns: 1fr;">
            <?php if ($history && $history->num_rows > 0) { 
                while($enc = $history->fetch_assoc()) {
                    $vitals = get_vitals($enc['id']);
                    $diagnoses = get_diagnoses($enc['id']);
                    $prescriptions = get_prescriptions($enc['id']);
            ?>
            <div class="card" style="margin-bottom: 20px; border-left: 5px solid #0F4E74;">
                <h3 style="margin-top: 0; color: #0F4E74; border-bottom: 1px solid #eee; padding-bottom: 10px;">
                    Visit Date: <?php echo date('d M Y, h:i A', strtotime($enc['created_at'])); ?> 
                    <span style="float: right; font-size: 0.9rem; color: #666;">Encounter: <?php echo v_wrap($enc['encounter_number']); ?></span>
                </h3>
                <p><strong>Attending Doctor:</strong> Dr. <?php echo v_wrap($enc['doctor_name']); ?></p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                    <div>
                        <h4 style="color: #444; margin-bottom: 10px;">Diagnoses</h4>
                        <?php if ($diagnoses && $diagnoses->num_rows > 0) { ?>
                            <ul style="margin: 0; padding-left: 20px; color: #555;">
                                <?php while($d = $diagnoses->fetch_assoc()) { ?>
                                    <li><strong><?php echo v_wrap($d['diagnosis_type']); ?>:</strong> <?php echo v_wrap($d['diagnosis']); ?> <?php echo $d['icd_code'] ? '('.v_wrap($d['icd_code']).')' : ''; ?></li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <p style="color: #999; margin: 0;">No diagnoses recorded.</p>
                        <?php } ?>
                    </div>
                    <div>
                        <h4 style="color: #444; margin-bottom: 10px;">Prescriptions</h4>
                        <?php if ($prescriptions && $prescriptions->num_rows > 0) { ?>
                            <ul style="margin: 0; padding-left: 20px; color: #555;">
                                <?php while($p = $prescriptions->fetch_assoc()) { ?>
                                    <li><?php echo v_wrap($p['medication_name']); ?> (<?php echo v_wrap($p['dosage'] . ' - ' . $p['frequency']); ?>)</li>
                                <?php } ?>
                            </ul>
                        <?php } else { ?>
                            <p style="color: #999; margin: 0;">No prescriptions recorded.</p>
                        <?php } ?>
                    </div>
                </div>

                <?php if ($vitals) { ?>
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #eee;">
                    <span style="font-size: 0.9rem; color: #666;">
                        <strong>Vitals:</strong> 
                        BP: <?php echo v_wrap($vitals['systolic_bp'] . '/' . $vitals['diastolic_bp']); ?> mmHg | 
                        Temp: <?php echo v_wrap($vitals['temperature']); ?> °C | 
                        Weight: <?php echo v_wrap($vitals['weight']); ?> kg
                    </span>
                </div>
                <?php } ?>
            </div>
            <?php } 
            } else { ?>
                <div class="card" style="text-align: center; padding: 40px;">
                    <i class="bi bi-folder2-open" style="font-size: 3rem; color: #ccc;"></i>
                    <p style="color: #666; margin-top: 15px;">This patient has no completed medical encounters.</p>
                </div>
            <?php } ?>
        </div>

    </main>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
