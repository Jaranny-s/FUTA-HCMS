<?php 
require_once('../../private/config.php'); ?>

<?php 
$page_title = 'FUTA HCMS Staff Login';
$errors = [];
$email = '';
$password = '';

if(is_post_request()) {

  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';

    // Validations
  if(is_blank($email)) {
    $errors[] = "Email cannot be blank.";
  }
  if(is_blank($password)) {
    $errors[] = "Password cannot be blank.";
  }
  
  // if errors are empty, try to log in. 
  if(empty($errors)) {
    
    $login_failure_alert = "Log in was unsuccessful.";
    $staff = find_staff_by_email($email);
    if($staff) {
      // using ome variable ensres that msg is the same
      if(password_verify($password, $staff['password'])) {
        // password matches
  log_in_staff($staff);
  if ($staff['password_reset_required'] == 1) {
         redirect_to(url_wrap('/staff/reset_password.php'));
    } else {
        redirect_to(url_wrap('/staff/dashboard.php'));
  }
        
      } else {
        // username found but password does not match
        $errors[] = $login_failure_alert; 
      }
      
    } else {
      // no username found
        $errors[] = $login_failure_alert;
    }
  }
  
}
?>

<!doctype html>
<html>
<head>
    <link rel="icon" href="<?php echo url_wrap('/assets/images/futa_logo.png'); ?>" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<title><?php echo v_wrap($page_title); ?> | FUTA Health Centre Management System</title>
 <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap)" rel="stylesheet" />
      <link rel="stylesheet" media="all" href="<?php echo url_wrap('/assets/css/login.css?v=' . time()); ?>" />
  </head>

  <body>
    <div id="login-bg">
    <div class="login-overlay"></div>
      
      <div class="header-img">
        <img src="<?php echo url_wrap('/assets/images/futa_logo.png'); ?>" width='120' height='120' />
      </div>
      
      <div class="login-form">
        <p>Health Centre Staff Login</p>
        <br />
        <p>Enter your Email and Password below.</p>
      <form action="login.php" method="post">
        <div><span><i class="bi bi-person"></i>
          <input type="email" name="email" value="<?php echo v_wrap($email); ?>" placeholder="Email"/></span></div>
           
         <div><span><i class="bi bi-lock"></i>
           <input type="password" name="password" value="" placeholder="Password"/></span></div>
        
      <div style="display:flex; justify-content:space-between; align-items:center; font-size:0.85rem; margin:15px auto 0 auto; width:65%; color:#555;">
          <label style="display:flex; align-items:center; gap:5px;">
              <input type="checkbox" name="remember"> Remember me
          </label>
          <a href="#" style="color:#0F4E74; text-decoration:none;" onclick="alert('Password reset link would be sent to your email.')">Forgotten Password?</a>
      </div>
        
      <div class="errors">
        <?php echo display_errors($errors); ?></div>
        
        <div id="submit-response">
        <input type="submit" name="submit" value="Log In" />
        </div>
        </form>
      </div>
      
      <div class="footnote">
        <p >&copy; <?php echo date('Y'); ?> FUTA Health Centre Management System (FUTA HCMS)</p>
      </div>
      
    </div>
    
    <script>const WWW_ROOT = "<?php echo WWW_ROOT; ?>";</script>
    <script src="<?php echo url_wrap('/assets/js/login.js?v=' . time()); ?>"></script>
  </body>
</html>
