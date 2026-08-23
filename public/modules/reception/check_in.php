<?php
require_once('../../../private/config.php');
require_password_reset();

if (!hasPermission('create_encounter')) {
    // We assume receptionists have this or something similar.
    // For now, let's just restrict by role if permission doesn't exist.
    // redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Check-in Patient";
$specificCss = "/assets/css/encounters.css"; // We'll create this soon

// Handle form submission
if (is_post_request()) {
    $patient_id = $_POST['patient_id'] ?? '';
    $doctor_id = $_POST['doctor_id'] ?? '';
    $priority = $_POST['priority'] ?? 'Normal';
    
    if (is_blank($patient_id) || is_blank($doctor_id)) {
        $_SESSION['error'] = "Patient and Doctor must be selected.";
    } else {
        $created_by = $_SESSION['staff_id'];
        $encounter_id = create_encounter($patient_id, $doctor_id, null, $priority, $created_by);
        $_SESSION['message'] = "Patient successfully checked in! Encounter Created.";
        redirect_to(url_wrap('/modules/encounters/index.php'));
    }
}

// Fetch lists for the form
$patients = find_all_patients();
$doctors = find_staff_by_role('doctor');

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    <main class="main-content">
        
        <div class="top">
            <p class="top-head">Check-in Queue</p>
            <p class="top-description">Check in walk-in patients and create encounters.</p>
        </div>

        <div><?php echo display_session_message(); ?></div>
        <?php if (isset($_SESSION['error'])) { echo "<div style='color:red;'>{$_SESSION['error']}</div>"; unset($_SESSION['error']); } ?>

        <div class="encounter-card">
            <form action="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" method="post">
                
                <div class="form-group">
                    <label>Select Patient</label>
                    <select name="patient_id" required>
                        <option value="">-- Choose Patient --</option>
                        <?php while($p = $patients->fetch_assoc()) { ?>
                            <option value="<?php echo $p['id']; ?>">
                                <?php echo v_wrap($p['patient_id'] . ' - ' . $p['surname'] . ' ' . $p['first_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Assign Doctor</label>
                    <select name="doctor_id" required>
                        <option value="">-- Choose Doctor --</option>
                        <?php while($d = $doctors->fetch_assoc()) { ?>
                            <option value="<?php echo $d['id']; ?>">
                                <?php echo v_wrap($d['full_name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Priority</label>
                    <select name="priority" required>
                        <option value="Normal">Normal</option>
                        <option value="Routine">Routine</option>
                        <option value="Urgent">Urgent</option>
                        <option value="Emergency">Emergency</option>
                    </select>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary" style="background:#0F4E74; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer;">
                        Check In & Start Encounter
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<?php include(SHARED_PATH . '/footer.php'); ?>
