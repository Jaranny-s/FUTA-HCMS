<?php
    if(!isset($page_title)) { $page_title = 'Staff Section'; }
    if(!isset($staff_type)) { $staff_type = 'FUTA Health Centre Staff'; }
?>

<!doctype html>
<html>
<head>
    <link rel="icon" href="<?php echo url_wrap('/assets/images/futa_logo.png'); ?>" />
<title><?php echo v_wrap($page_title); ?> | <?php echo $settings['hospital_name']; ?></title>
 <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap)" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
      <link rel="stylesheet" media="all" href="<?php echo url_wrap('/assets/css/staff.css'); ?>" />
    
    <?php if(isset($specificCss)) { ?>
      <link rel="stylesheet" media="all" href="<?php echo url_wrap($specificCss); ?>" />
    <?php } ?>
  </head>

  <body>
      <header>
          <a class="return-home" href="<?php echo url_wrap('/staff/dashboard.php'); ?>"><img src="<?php echo url_wrap('/assets/images/futa_logo.png'); ?>" width='80' height='80' /></a>
        <h1>FUTA Health Centre Management System</h1>
      </header>
      
      

      
      
     