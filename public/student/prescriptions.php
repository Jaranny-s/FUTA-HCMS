<?php
require_once('../../private/config.php');
require_student_login();
$student = find_student_by_matric($_SESSION['student_matric']);

$page_title = 'My Prescriptions';
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
                    <h3><i class="bi bi-capsule"></i> Medication History</h3>
                    <p style="color:#666; text-align: center; margin-top: 30px;">
                        <i class="bi bi-prescription2" style="font-size: 3rem; color: #ccc;"></i><br>
                        You have no recorded prescriptions.
                    </p>
                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>
    </div>
</body>
</html>
