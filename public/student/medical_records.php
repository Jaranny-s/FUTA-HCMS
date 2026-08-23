<?php
require_once('../../private/config.php');
require_student_login();
$student = find_student_by_matric($_SESSION['student_matric']);

// In a full implementation, this would fetch from encounters/consultations table
// $medical_records = get_student_medical_records($student['id']); 

$page_title = 'Medical Records';
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
                    <h3><i class="bi bi-journal-medical"></i> Medical Records</h3>
                    <div style="background: #e8f4fd; color: #0c5460; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <i class="bi bi-info-circle"></i> For privacy reasons, only basic visit histories and vitals are displayed. Detailed consultation notes are stored securely offline.
                    </div>
                    
                    <p style="color:#666; text-align: center; margin-top: 30px;">
                        <i class="bi bi-folder2-open" style="font-size: 3rem; color: #ccc;"></i><br>
                        No recent medical encounters found.
                    </p>
                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>
    </div>
</body>
</html>
