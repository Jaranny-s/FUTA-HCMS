<?php
require_once('../private/config.php');

$errors = [];
$matric_number = '';

if (is_post_request()) {
    $matric_number = $_POST['matric_number'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $remember = isset($_POST['remember']);

    if (is_blank($matric_number) || is_blank($password)) {
        $errors[] = "Matric Number and Password cannot be blank.";
    } else {
        $student = find_student_by_matric($matric_number);
        if ($student && password_verify($password, $student['hashed_password'])) {
            log_in_student($student);
            redirect_to(url_wrap('/student/dashboard.php'));
        } else {
            $errors[] = "Log in was unsuccessful. Please check your matric number and password.";
        }
    }
}

$page_title = 'Student Portal Login';
?>
<!doctype html>
<html>
<head>
    <link rel="icon" href="<?php echo url_wrap('/assets/images/futa_logo.png'); ?>" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <title><?php echo v_wrap($page_title); ?> | FUTA Health Centre Management System</title>
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" media="all" href="<?php echo url_wrap('/assets/css/login.css?v=' . time()); ?>" />
    <style>
        .staff-portal-btn {
            display: inline-block;
            margin-top: 15px;
            color: #0F4E74;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .staff-portal-btn:hover {
            text-decoration: underline;
        }
        .register-link {
            font-size: 0.9rem;
            color: #666;
            margin-top: 15px;
        }
        .register-link a {
            color: #0F4E74;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div id="login-bg">
    <div class="login-overlay"></div>
      
      <div class="header-img">
        <img src="<?php echo url_wrap('/assets/images/futa_logo.png'); ?>" width='120' height='120' />
      </div>
      
      <div class="login-form" style="height: 480px;">
        <p>Student Self-Service Portal</p>
        <br />
        <p>Enter your Matriculation Number and Password below.</p>
        
        <form action="index.php" method="post">
            <div><span><i class="bi bi-person"></i>
              <input type="text" name="matric_number" value="<?php echo v_wrap($matric_number); ?>" placeholder="e.g. CSC/18/1234"/></span></div>
               
             <div><span><i class="bi bi-lock"></i>
               <input type="password" name="password" value="" placeholder="Password"/></span></div>
            
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; margin:15px auto 0 auto; width:65%; color:#555;">
                <label style="display:flex; align-items:center; gap:5px;">
                    <input type="checkbox" name="remember"> Remember me
                </label>
                <a href="#" style="color:#0F4E74; text-decoration:none;" onclick="alert('Password reset link would be sent to your email.')">Forgotten Password?</a>
            </div>
            
            <div class="errors">
                <?php echo display_errors($errors); ?>
            </div>
            
            <div id="submit-response">
                <input type="submit" name="submit" value="Log In" />
            </div>
        </form>

        <div class="register-link">
            Don't have an account? <a href="student/register.php">Register Here</a>
        </div>
        
        <div>
            <a href="staff/login.php" class="staff-portal-btn">
                Switch to Staff Portal &rarr;
            </a>
        </div>

      </div>
      
      <div class="footnote">
        <p>&copy; <?php echo date('Y'); ?> FUTA Health Centre Management System (FUTA HCMS)</p>
      </div>
      
    </div>
    
    <script>const WWW_ROOT = "<?php echo WWW_ROOT; ?>";</script>
    <script src="<?php echo url_wrap('/assets/js/login.js?v=' . time()); ?>"></script>
</body>
</html>
