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
        // Handle image upload if provided
        $image_name = null;
        if (!empty($_FILES['profile_image']['name'])) {
            $original_name = $_FILES['profile_image']['name'];
            $tmp_path = $_FILES['profile_image']['tmp_name'];
            $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $allowed)) {
                $image_name = 'student_' . time() . '.' . $ext;
                $destination = __DIR__ . '/../modules/patients/images/patient_pictures/' . $image_name;
                move_uploaded_file($tmp_path, $destination);
            }
        }

        // Check if matric exists
        if (find_student_by_matric($matric_number)) {
            $errors[] = "A student with this Matriculation Number is already registered.";
        } else {
            $new_id = register_student($first_name, $surname, $email, $phone, $matric_number, $password, $image_name);
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
            width: 90%; 
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .login-form span {
            width: 90%; 
            display: flex;
            align-items: center;
            margin: 0 auto 10px auto;
            text-align: left;
            background: white;
            border: 1px solid #ddd;
            border-radius: 5px;
            overflow: hidden;
        }
        .login-form span i {
            padding: 10px 15px;
            background: #f4f7f6;
            color: #555;
            border-right: 1px solid #ddd;
        }
        .login-form span input {
            border: none;
            margin: 0;
            width: 100%;
            border-radius: 0;
        }
        .login-form span input:focus {
            outline: none;
            box-shadow: none;
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
      
      <div class="login-form" style="height: auto; width: 450px; padding-bottom: 20px; border-top: 5px solid #0F4E74;">
        <p style="margin-top:20px; font-weight: 600; color: #0F4E74; font-size: 1.2rem;">Student Registration</p>
        <br />
        <p style="color: #666; margin-bottom: 20px;">Create your Self-Service Portal account below.</p>
        
        <form action="register.php" method="post" enctype="multipart/form-data" style="margin-top:15px;">
            
            <div><span><i class="bi bi-person"></i>
              <input type="text" name="first_name" value="<?php echo v_wrap($first_name); ?>" placeholder="First Name" required/></span></div>
               
            <div><span><i class="bi bi-person"></i>
              <input type="text" name="surname" value="<?php echo v_wrap($surname); ?>" placeholder="Surname" required/></span></div>
            
            <div><span><i class="bi bi-card-text"></i>
              <input type="text" name="matric_number" value="<?php echo v_wrap($matric_number); ?>" placeholder="Matric No (e.g. CSC/18/1234)" required/></span></div>

            <div><span><i class="bi bi-envelope"></i>
              <input type="email" name="email" value="<?php echo v_wrap($email); ?>" placeholder="Email Address"/></span></div>

            <div><span><i class="bi bi-telephone"></i>
              <input type="text" name="phone" value="<?php echo v_wrap($phone); ?>" placeholder="Phone Number"/></span></div>
            
             <div><span><i class="bi bi-lock"></i>
               <input type="password" name="password" value="" placeholder="Password" required/></span></div>

             <div><span><i class="bi bi-lock-fill"></i>
               <input type="password" name="confirm_password" value="" placeholder="Confirm Password" required/></span></div>
            
            <div><span style="background: white;"><i class="bi bi-camera"></i>
              <input type="file" name="profile_image" accept="image/*" style="padding: 8px;" /></span>
              <small style="color: #666; display: block; margin: -5px auto 10px auto; width: 90%; text-align: left;">Upload a profile picture (optional)</small>
            </div>

            <div class="errors">
                <?php echo display_errors($errors); ?>
            </div>
            
            <div id="submit-response" style="margin-top: 20px;">
                <input type="submit" name="submit" value="Register Account" style="width: 90%; background: #0F4E74; border-radius: 5px;" />
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
