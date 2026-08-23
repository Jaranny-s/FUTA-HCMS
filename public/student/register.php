<?php
require_once('../../private/config.php');

$errors = [];
$first_name = '';
$surname = '';
$email = '';
$phone = '';
$matric_number = '';

if (is_post_request()) {
    $first_name = $_POST['first_name'] ?? '';
    $surname = $_POST['surname'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $matric_number = $_POST['matric_number'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (is_blank($first_name) || is_blank($surname) || is_blank($matric_number) || is_blank($password)) {
        $errors[] = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    } else {
        // Check if matric exists
        if (find_student_by_matric($matric_number)) {
            $errors[] = "A student with this Matriculation Number is already registered.";
        } else {
            $new_id = register_student($first_name, $surname, $email, $phone, $matric_number, $password);
            if ($new_id) {
                // Auto log in
                $student = find_student_by_matric($matric_number);
                log_in_student($student);
                $_SESSION['message'] = "Registration successful! Welcome to the FUTA Health Centre Portal.";
                redirect_to(url_wrap('/student/dashboard.php'));
            } else {
                $errors[] = "Registration failed. Please try again.";
            }
        }
    }
}

$page_title = 'Student Registration';
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
        .login-form input[type="text"], .login-form input[type="password"], .login-form input[type="email"] {
            margin: 5px 0;
            width: 80%; /* Slightly wider for form inputs */
        }
        .login-form span {
            width: 80%; /* Match width */
            display: inline-block;
            text-align: left;
            padding: 5px 2px;
        }
        .register-link {
            font-size: 0.9rem;
            color: #666;
            margin-top: 15px;
            padding-bottom: 20px;
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
      
      <div class="login-form" style="height: auto; width: 450px; padding-bottom: 20px;">
        <p style="margin-top:20px;">Student Registration</p>
        <br />
        <p>Create your Self-Service Portal account below.</p>
        
        <form action="register.php" method="post" style="margin-top:15px;">
            
            <div><span><i class="bi bi-person"></i>
              <input type="text" name="first_name" value="<?php echo v_wrap($first_name); ?>" placeholder="First Name" required/></span></div>
               
            <div><span><i class="bi bi-person"></i>
              <input type="text" name="surname" value="<?php echo v_wrap($surname); ?>" placeholder="Surname" required/></span></div>
            
            <div><span><i class="bi bi-card-text"></i>
              <input type="text" name="matric_number" value="<?php echo v_wrap($matric_number); ?>" placeholder="Matriculation Number (e.g. CSC/18/1234)" required/></span></div>

            <div><span><i class="bi bi-envelope"></i>
              <input type="email" name="email" value="<?php echo v_wrap($email); ?>" placeholder="Email Address"/></span></div>

            <div><span><i class="bi bi-telephone"></i>
              <input type="text" name="phone" value="<?php echo v_wrap($phone); ?>" placeholder="Phone Number"/></span></div>
            
             <div><span><i class="bi bi-lock"></i>
               <input type="password" name="password" value="" placeholder="Password" required/></span></div>

             <div><span><i class="bi bi-lock-fill"></i>
               <input type="password" name="confirm_password" value="" placeholder="Confirm Password" required/></span></div>
            
            <div class="errors">
                <?php echo display_errors($errors); ?>
            </div>
            
            <div id="submit-response">
                <input type="submit" name="submit" value="Register Account" style="width: 85%;" />
            </div>
        </form>

        <div class="register-link">
            Already have an account? <a href="../index.php">Log In Here</a>
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
