<?php
require_once('../../../private/config.php');
require_password_reset();

if (!isset($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['receptionist', 'nurse', 'doctor', 'admin', 'super_admin'])) {
    $_SESSION['error'] = "Access Denied: You do not have permission to check in patients.";
    redirect_to(url_wrap('/staff/dashboard.php'));
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
        redirect_to($_SERVER['HTTP_REFERER'] ?? url_wrap('/modules/encounters/index.php'));
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
            <p class="top-head">Reception Check-in</p>
            <p class="top-description">Queue walk-in patients and assign them to doctors.</p>
        </div>

        <div><?php echo display_session_message(); ?></div>
        <?php if (isset($_SESSION['error'])) { echo "<div style='color:#d9534f; background:#f9eded; padding:15px; border-radius:8px; margin-bottom:20px; font-weight:500;'><i class='bi bi-exclamation-triangle-fill'></i> {$_SESSION['error']}</div>"; unset($_SESSION['error']); } ?>

        <div style="background: white; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; max-width: 800px; margin: 0 auto; border: 1px solid #eee;">
            <div style="background: linear-gradient(135deg, #0F4E74 0%, #1a7bb5 100%); padding: 30px; color: white;">
                <h3 style="margin: 0; font-size: 1.5rem; font-weight: 400;"><i class="bi bi-person-bounding-box" style="margin-right: 10px; opacity: 0.8;"></i> Create New Encounter</h3>
                <p style="margin: 10px 0 0 0; opacity: 0.8; font-size: 0.9rem;">Fill in the details below to assign a patient to the clinic queue.</p>
            </div>
            
            <form action="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" method="post" style="padding: 30px;">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Select Patient</label>
                        <select name="patient_id" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem; background-color: #fcfcfc;">
                            <option value="">-- Choose Patient --</option>
                            <?php while($p = $patients->fetch_assoc()) { ?>
                                <option value="<?php echo $p['id']; ?>">
                                    <?php echo v_wrap($p['patient_id'] . ' - ' . $p['surname'] . ' ' . $p['first_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div class="form-group" style="margin: 0;">
                        <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Assign Doctor</label>
                        <select name="doctor_id" required style="width:100%; padding:12px; border:1px solid #ddd; border-radius:8px; font-size:1rem; background-color: #fcfcfc;">
                            <option value="">-- Choose Doctor --</option>
                            <?php while($d = $doctors->fetch_assoc()) { ?>
                                <option value="<?php echo $d['id']; ?>">
                                    <?php echo v_wrap($d['full_name']); ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 30px;">
                    <label style="display: block; margin-bottom: 8px; color: #555; font-weight: 500;">Triage Priority</label>
                    <div style="display: flex; gap: 15px;">
                        <label style="flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; text-align: center; transition: 0.2s;" onmouseover="this.style.borderColor='#0F4E74'; this.style.backgroundColor='#f0f8fc'" onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='#ddd'; this.style.backgroundColor='transparent'}">
                            <input type="radio" name="priority" value="Routine" style="margin-bottom: 10px;" onchange="updateRadioStyles(this)">
                            <div style="font-weight: 600; color: #28a745;">Routine</div>
                        </label>
                        <label style="flex: 1; border: 1px solid #0F4E74; background-color: #f0f8fc; border-radius: 8px; padding: 15px; cursor: pointer; text-align: center; transition: 0.2s;">
                            <input type="radio" name="priority" value="Normal" checked style="margin-bottom: 10px;" onchange="updateRadioStyles(this)">
                            <div style="font-weight: 600; color: #0F4E74;">Normal</div>
                        </label>
                        <label style="flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; text-align: center; transition: 0.2s;" onmouseover="this.style.borderColor='#0F4E74'; this.style.backgroundColor='#f0f8fc'" onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='#ddd'; this.style.backgroundColor='transparent'}">
                            <input type="radio" name="priority" value="Urgent" style="margin-bottom: 10px;" onchange="updateRadioStyles(this)">
                            <div style="font-weight: 600; color: #fd7e14;">Urgent</div>
                        </label>
                        <label style="flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 15px; cursor: pointer; text-align: center; transition: 0.2s;" onmouseover="this.style.borderColor='#0F4E74'; this.style.backgroundColor='#f0f8fc'" onmouseout="if(!this.querySelector('input').checked){this.style.borderColor='#ddd'; this.style.backgroundColor='transparent'}">
                            <input type="radio" name="priority" value="Emergency" style="margin-bottom: 10px;" onchange="updateRadioStyles(this)">
                            <div style="font-weight: 600; color: #dc3545;">Emergency</div>
                        </label>
                    </div>
                </div>

                <div style="text-align: right; border-top: 1px solid #eee; padding-top: 25px;">
                    <button type="submit" class="btn btn-primary" style="background:#0F4E74; color:white; padding:12px 30px; border:none; border-radius:8px; cursor:pointer; font-size: 1.1rem; font-weight: 500; display: inline-flex; align-items: center; gap: 8px; transition: 0.3s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        Create Encounter <i class="bi bi-arrow-right-circle"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <script>
        function updateRadioStyles(selectedRadio) {
            // Reset all labels
            const labels = selectedRadio.closest('div').querySelectorAll('label');
            labels.forEach(label => {
                label.style.borderColor = '#ddd';
                label.style.backgroundColor = 'transparent';
            });
            // Highlight selected
            const selectedLabel = selectedRadio.closest('label');
            selectedLabel.style.borderColor = '#0F4E74';
            selectedLabel.style.backgroundColor = '#f0f8fc';
        }
        </script>

    </main>
</div>
<?php include(SHARED_PATH . '/footer.php'); ?>
