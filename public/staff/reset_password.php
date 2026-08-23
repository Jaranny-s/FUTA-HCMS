<?php
require_once "../../private/config.php";

// 1. Must be logged in
if (!is_logged_in()) {
    redirect_to(url_wrap("/staff/login.php"));
}

$staff_id = $_SESSION["staff_id"];

// 2. Check if reset is required
$sql = "SELECT password_reset_required FROM staff WHERE id = ?";
$query = $db_1->prepare($sql);
$query->bind_param("i", $staff_id);
$query->execute();
$query->bind_result($reset_required);
$query->fetch();
$query->close();

// If NOT required → go to dashboard
if ($reset_required == 0) {
    redirect_to(url_wrap("/staff/dashboard.php"));
}
$page_title = 'FUTA HCMS Staff Password Reset Page';
$errors = [];
$password_required = $options["password_required"] ?? true;

// 3. Handle form submission
if (is_post_request()) {
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    // Validation
    if ($password_required) {
        // password
        if (is_blank($password)) {
            $errors[] = "Password cannot be blank.";
        } elseif (!has_length($password, ["min" => 12, "max" => 255])) {
            $errors[] = "Password must be between 12 and 255 characters.";
        }
        if (!has_valid_password_format($password)) {
            $errors[] = "Password must include 1 uppercase letter, 1 lowercase letter, 1 number and 1 symbol.";
        }

        // confirm_password
        if (is_blank($confirm)) {
            $errors[] = "Confirm Password field cannot be blank.";
        } elseif ($confirm !== $password) {
            $errors[] = "Password and Confirm Password fields do not match.";
        }
    }

    // 4. Save new password
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE staff SET password = ?, password_reset_required = 0 WHERE id = ?";
        $query = $db_1->prepare($sql);
        $query->bind_param("si", $hashed_password, $staff_id);
        $query->execute();
        $query->close();

        redirect_to(url_wrap("/staff/dashboard.php"));
    }
}
?>


<!doctype html>
<html>
<head>
    <link rel="icon" href="<?php echo url_wrap("/assets/images/futa_logo.png"); ?>" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<title><?php echo v_wrap($page_title); ?> | FUTA Health Centre Management System</title>
 <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap)" rel="stylesheet" />
      <link rel="stylesheet" media="all" href="<?php echo url_wrap("/assets/css/verification.css"); ?>" />
  </head>

  <body>
    <div id="deepBlueBg">
    
      
      <div class="header-img">
        <img src="<?php echo url_wrap("/assets/images/futa_logo.png"); ?>" width='120' height='120' />
      </div>

        <div class="password-form">
        <p>HC Staff Password Reset Page</p>
        <br />
        <p>Create/reset your own secure password, which only you know, after an admin has initially given you a temporary password.</p>
      <form action="reset_password.php" method="post">
          <div><span><i class="bi bi-lock"></i>
           <input type="password" name="password" value="" placeholder="Password" required/></span></div>
          <div><span><i class="bi bi-lock"></i>
           <input type="password" name="confirm_password" value="" placeholder="Confirm Password" required/></span></div>
        
      <div class="errors">
        <?php echo display_errors($errors); ?></div>
        
        <div id="submit-response">
        <input type="submit" name="submit" value="Create New Password" />
        </div>
        </form>
      </div>
        
        <div class="footnote">
        <p >&copy; <?php echo date('Y'); ?> FUTA Health Centre Management System (FUTA HCMS)</p>
      </div>
        
      </div>
    </body>
</html>
