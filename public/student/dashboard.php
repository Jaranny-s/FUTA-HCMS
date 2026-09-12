<?php
require_once('../../private/config.php');
require_student_login();

if (isset($_GET['skip_profile_prompt'])) {
    $_SESSION['skip_profile_prompt'] = true;
    redirect_to(url_wrap('/student/dashboard.php'));
}

$student = find_student_by_matric($_SESSION['student_matric']);
$upcoming_appointment = get_upcoming_student_appointment($student['id']);
$all_appointments = get_student_appointments($student['id']);

$is_profile_incomplete = empty($student['gender']) 
    || empty($student['date_of_birth']) 
    || empty($student['next_of_kin_name']) 
    || empty($student['department'])
    || $student['date_of_birth'] === '2000-01-01';

$page_title = 'Student Dashboard';
$defaultImage = 'default_profile_pic.png';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo v_wrap($page_title); ?> - FUTA HCMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_portal.css?v=<?php echo time(); ?>">
</head>
<body>

    <header class="portal-header">
        <div class="brand">
            <img src="../assets/images/futa_logo.png" width="40" height="40" alt="FUTA Logo">
            FUTA HCMS Student Portal
        </div>
        <div class="user-menu">
            <span>
                <?php if (!empty($student['profile_image'])) { ?>
                    <img src="<?php echo url_wrap('/modules/patients/images/patient_pictures/' . v_wrap($student['profile_image'])); ?>" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultImage)); ?>';" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 5px;">
                <?php } else { ?>
                    <i class="bi bi-person-circle" style="vertical-align: middle; margin-right: 5px;"></i>
                <?php } ?>
                <?php echo v_wrap($student['first_name'] . ' ' . $student['surname']); ?>
            </span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="portal-container">
        <div><?php echo display_session_message(); ?></div>

        <?php if ($is_profile_incomplete) { ?>
            <div style="background: #fff3cd; color: #856404; border: 1px solid #ffeeba; border-radius: 8px; padding: 15px 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                <div>
                    <i class="bi bi-exclamation-triangle-fill" style="margin-right: 8px; font-size: 1.1rem;"></i>
                    <strong>Profile Incomplete:</strong> Your medical profile is missing key details (Date of Birth, Next of Kin, Department). Please complete your profile to finalize clinic clearance.
                </div>
                <a href="profile.php" style="background: #0F4E74; color: white; padding: 8px 16px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; white-space: nowrap; margin-left: 15px;">Complete Profile &rarr;</a>
            </div>
        <?php } ?>

        <div class="welcome-banner">
            <h1>Welcome back, <?php echo v_wrap($student['first_name']); ?>!</h1>
            <p>Matric No: <?php echo v_wrap($student['matric_number']); ?> | Patient ID: <?php echo v_wrap($student['patient_id']); ?></p>
        </div>

        <div class="dashboard-grid">
            
            <div class="main-column">
                <div class="card">
                    <h3>Your Next Appointment</h3>
                    <?php if ($upcoming_appointment) { ?>
                        <div class="appointment-item" style="border-left: 4px solid #0F4E74;">
                            <div>
                                <div class="date"><?php echo date('l, d F Y', strtotime($upcoming_appointment['appointment_date'])); ?></div>
                                <div class="details">
                                    Time: <?php echo date('h:i A', strtotime($upcoming_appointment['appointment_date'])); ?> <br>
                                    Type: <?php echo v_wrap($upcoming_appointment['appointment_type']); ?> <br>
                                    Reason: <?php echo v_wrap($upcoming_appointment['reason']); ?>
                                </div>
                            </div>
                            <div>
                                <span class="badge status-<?php echo strtolower($upcoming_appointment['status']); ?>"><?php echo v_wrap($upcoming_appointment['status']); ?></span>
                            </div>
                        </div>
                    <?php } else { ?>
                        <p style="color:#666;">You have no upcoming appointments.</p>
                        <button data-modal-target="bookAppointmentModal" class="action-btn primary" style="display:inline-block; width:auto; padding:10px 20px; cursor:pointer; border:none; font-family:inherit;">Book an Appointment</button>
                    <?php } ?>
                </div>

                <div class="card" style="margin-top: 25px;">
                    <h3>Recent Visit History</h3>
                    <?php if ($all_appointments->num_rows > 0) { ?>
                        <?php 
                        $count = 0;
                        while($app = $all_appointments->fetch_assoc()) { 
                            if ($count >= 5) break; // show only 5 recent
                            $count++;
                        ?>
                            <div class="appointment-item">
                                <div>
                                    <div class="date" style="color:#444; font-size:1rem;"><?php echo date('d M Y', strtotime($app['appointment_date'])); ?></div>
                                    <div class="details"><?php echo v_wrap($app['appointment_type']); ?></div>
                                </div>
                                <div>
                                    <span class="badge status-<?php echo str_replace(' ', '-', strtolower($app['status'])); ?>"><?php echo v_wrap($app['status']); ?></span>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p style="color:#666;">No history found.</p>
                    <?php } ?>
                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>
    </div>

    <?php if ($is_profile_incomplete && empty($_SESSION['skip_profile_prompt'])) { ?>
    <!-- Complete Registration Prompt Modal -->
    <div id="completeProfilePromptModal" class="modal-overlay active">
        <div class="modal-content" style="max-width: 520px; text-align: center;">
            <button class="modal-close" data-modal-close>&times;</button>
            <div style="font-size: 3.2rem; color: #0F4E74; margin-bottom: 10px;">
                <i class="bi bi-person-exclamation"></i>
            </div>
            <h3 class="modal-title" style="border: none; margin-bottom: 12px; font-size: 1.35rem; color: #0F4E74;">Complete Your Medical Registration</h3>
            <p style="color: #666; font-size: 0.95rem; line-height: 1.5; margin-bottom: 25px;">
                Welcome to FUTA Health Centre! Your medical profile is currently incomplete. Completing your profile details (such as Date of Birth, Department, Medical history, and Next of Kin) ensures healthcare providers have the vital information needed to serve you quickly.
            </p>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <a href="dashboard.php?skip_profile_prompt=1" class="action-btn" style="width: auto; padding: 10px 22px; margin-bottom: 0; text-align: center; justify-content: center; background: #e9ecef; color: #495057; border: 1px solid #ced4da;">Skip for now</a>
                <a href="profile.php" class="action-btn primary" style="width: auto; padding: 10px 22px; margin-bottom: 0; text-align: center; justify-content: center;">Complete Profile <i class="bi bi-arrow-right" style="margin-left: 5px;"></i></a>
            </div>
        </div>
    </div>
    <?php } ?>

    <script src="../assets/js/modal.js?v=<?php echo time(); ?>"></script>
</body>
</html>
