<?php
require_once('../../private/config.php');
require_student_login();
$student = find_student_by_matric($_SESSION['student_matric']);

if (is_post_request()) {
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    
    if ($password !== '' && $password !== $confirm) {
        $_SESSION['error'] = "Passwords do not match.";
    } else {
        $update_pwd = ($password !== '') ? $password : null;
        update_student_profile($student['id'], $phone, $email, $update_pwd);
        $_SESSION['message'] = "Profile updated successfully.";
        // refresh student details
        $student = find_student_by_matric($_SESSION['student_matric']);
    }
    redirect_to(url_wrap('/student/profile.php'));
}

$page_title = 'Update Profile';
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
                    <h3><i class="bi bi-person-lines-fill"></i> Update Profile</h3>
                    <div><?php echo display_session_message(); ?></div>
                    
                    <form action="profile.php" method="post" style="margin-top: 20px;">
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="text" name="phone" value="<?php echo v_wrap($student['phone'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?php echo v_wrap($student['email'] ?? ''); ?>">
                        </div>
                        
                        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">
                        
                        <h4>Change Password</h4>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password">
                        </div>
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password">
                        </div>

                        <button type="submit" class="action-btn primary" style="font-size:1rem;">Save Changes</button>
                    </form>
                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>
    </div>
    <script src="../assets/js/modal.js?v=<?php echo time(); ?>"></script>
</body>
</html>
