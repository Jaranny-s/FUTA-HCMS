<?php
require_once('../../private/config.php');
require_student_login();
$student = find_student_by_matric($_SESSION['student_matric']);
$all_appointments = get_student_appointments($student['id']);
$page_title = 'My Appointments';
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
            <a href="dashboard.php" style="color:#555; text-decoration:none; margin-right:15px;">Dashboard</a>
            <span><i class="bi bi-person-circle"></i> <?php echo v_wrap($student['first_name']); ?></span>
        </div>
    </header>

    <div class="portal-container">
        <div class="dashboard-grid">
            <div class="main-column">
                <div class="card">
                    <h3><i class="bi bi-calendar2-check"></i> All Appointments</h3>
                    
                    <?php if ($all_appointments->num_rows > 0) { ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 15px;">
                            <tr style="border-bottom: 2px solid #eee; text-align: left;">
                                <th style="padding: 10px;">Date & Time</th>
                                <th style="padding: 10px;">Type</th>
                                <th style="padding: 10px;">Reason</th>
                                <th style="padding: 10px;">Status</th>
                            </tr>
                            <?php while($app = $all_appointments->fetch_assoc()) { ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px 10px;"><?php echo date('d M Y, h:i A', strtotime($app['appointment_date'])); ?></td>
                                <td style="padding: 15px 10px;"><?php echo v_wrap($app['appointment_type']); ?></td>
                                <td style="padding: 15px 10px;"><?php echo v_wrap($app['reason']); ?></td>
                                <td style="padding: 15px 10px;"><span class="badge status-<?php echo str_replace(' ', '-', strtolower($app['status'])); ?>"><?php echo v_wrap($app['status']); ?></span></td>
                            </tr>
                            <?php } ?>
                        </table>
                    <?php } else { ?>
                        <p style="color:#666; text-align: center; margin-top: 30px;">You have not booked any appointments yet.</p>
                        <div style="text-align: center;"><a href="book.php" class="action-btn primary" style="display:inline-block; width:auto; padding:10px 20px;">Book Now</a></div>
                    <?php } ?>

                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>
    </div>
</body>
</html>
