<?php
require_once('../../private/config.php');
require_student_login();
$student = find_student_by_matric($_SESSION['student_matric']);

$prescriptions = get_student_prescriptions($student['id']);

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
    <link rel="stylesheet" href="../assets/css/student_portal.css?v=<?php echo time(); ?>">
</head>
<body>
    <header class="portal-header">
        <div class="brand">
            <img src="../assets/images/futa_logo.png" width="40" height="40" alt="FUTA Logo">
            FUTA HCMS Student Portal
        </div>
        <div class="user-menu">
            <a href="dashboard.php" style="margin-right:15px;">Dashboard</a>
            <span>
                <?php if (!empty($student['profile_image'])) { ?>
                    <img src="<?php echo url_wrap('/modules/patients/images/patient_pictures/' . v_wrap($student['profile_image'])); ?>" alt="Profile" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; vertical-align: middle; margin-right: 5px;">
                <?php } else { ?>
                    <i class="bi bi-person-circle" style="vertical-align: middle; margin-right: 5px;"></i>
                <?php } ?>
                <?php echo v_wrap($student['first_name']); ?>
            </span>
        </div>
    </header>

    <div class="portal-container">
        <div class="dashboard-grid">
            <div class="main-column">
                <div class="card">
                    <h3><i class="bi bi-capsule"></i> Medication History</h3>
                    
                    <?php if($prescriptions && $prescriptions->num_rows > 0) { ?>
                        <div class="appointment-list">
                        <?php while($rx = $prescriptions->fetch_assoc()) { ?>
                            <div class="appointment-card" style="border-left: 4px solid #0F4E74;">
                                <div style="display: flex; justify-content: space-between;">
                                    <div>
                                        <h4 style="margin: 0 0 5px 0; color: #333;"><?php echo v_wrap($rx['drug_name'] ?? $rx['medication_name']); ?></h4>
                                        <p style="margin: 0; color: #666; font-size: 0.9rem;">
                                            <strong>Dosage:</strong> <?php echo v_wrap($rx['dosage'] . ' - ' . $rx['frequency']); ?><br>
                                            <strong>Instructions:</strong> <?php echo v_wrap($rx['instructions']); ?>
                                        </p>
                                    </div>
                                    <div style="text-align: right;">
                                        <span class="status-badge" style="background:#e8f4fd; color:#0F4E74;"><?php echo v_wrap($rx['status']); ?></span>
                                        <p style="margin-top: 5px; font-size: 0.8rem; color: #999;"><?php echo date('d M Y', strtotime($rx['created_at'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                        </div>
                    <?php } else { ?>
                        <p style="color:#666; text-align: center; margin-top: 30px;">
                            <i class="bi bi-prescription2" style="font-size: 3rem; color: #ccc;"></i><br>
                            You have no recorded prescriptions.
                        </p>
                    <?php } ?>
                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>
    </div>
    <script src="../assets/js/modal.js?v=<?php echo time(); ?>"></script>
</body>
</html>
