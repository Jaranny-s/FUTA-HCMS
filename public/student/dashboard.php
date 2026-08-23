<?php
require_once('../../private/config.php');
require_student_login();

$student = find_student_by_matric($_SESSION['student_matric']);
$upcoming_appointment = get_upcoming_student_appointment($student['id']);
$all_appointments = get_student_appointments($student['id']);

$page_title = 'Student Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo v_wrap($page_title); ?> - FUTA HCMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_portal.css">
</head>
<body>

    <header class="portal-header">
        <div class="brand">
            <img src="../assets/images/futa_logo.png" width="40" height="40" alt="FUTA Logo">
            FUTA HCMS Student Portal
        </div>
        <div class="user-menu">
            <span><i class="bi bi-person-circle"></i> <?php echo v_wrap($student['first_name'] . ' ' . $student['surname']); ?></span>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="portal-container">
        <div><?php echo display_session_message(); ?></div>

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
                        <a href="book.php" class="action-btn primary" style="display:inline-block; width:auto; padding:10px 20px;">Book an Appointment</a>
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

</body>
</html>
