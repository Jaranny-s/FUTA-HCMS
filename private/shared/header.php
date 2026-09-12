<?php
    if(!isset($page_title)) { $page_title = 'Staff Section'; }
    if(!isset($staff_type)) { $staff_type = 'FUTA Health Centre Staff'; }

    $header_logo = (!empty($settings['logo']) && file_exists(PROJECT_PATH . '/public/assets/images/' . $settings['logo'])) 
        ? '/assets/images/' . $settings['logo'] 
        : '/assets/images/futa_logo.png';
?>

<!doctype html>
<html>
<head>
    <link rel="icon" href="<?php echo url_wrap($header_logo); ?>" />
<title><?php echo v_wrap($page_title); ?> | <?php echo $settings['hospital_name']; ?></title>
 <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap)" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
      <link rel="stylesheet" media="all" href="<?php echo url_wrap('/assets/css/staff.css?v=' . time()); ?>" />
    
    <?php if(isset($specificCss)) { ?>
      <link rel="stylesheet" media="all" href="<?php echo url_wrap($specificCss . '?v=' . time()); ?>" />
    <?php } ?>
    <link rel="stylesheet" media="all" href="<?php echo url_wrap('/assets/css/modal.css?v=' . time()); ?>" />
  </head>

  <body>
      <header>
          <a class="return-home" href="<?php echo url_wrap('/staff/dashboard.php'); ?>"><img src="<?php echo url_wrap($header_logo); ?>" width='80' height='80' alt="Logo" style="object-fit:contain;" /></a>
        <h1><?php echo $settings['hospital_name']; ?></h1>
      </header>
      
      

      
      
     