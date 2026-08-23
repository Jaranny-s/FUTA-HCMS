<?php
require_once('../../private/config.php'); ?>

<?php
require_password_reset();

$page_title = 'Logout Page';



include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    
    <a href="javascript:history.back()" class="btn btn-primary" id="link_layout">
  <i class="bi bi-arrow-left"></i> Back
</a>
    
  <div class="top">
    <p class="top-head">Logout </p> 
    <p class="top-description">turn off login status and exit account</p>
  </div>
    
 

    
  <div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="logout" data-tab="logout">
    Logout
  </button>

</div>

<div id="logout" class="tab-content active" role="tabpanel" aria-labelledby="logout-tab">
    
    
        
     
        
    <div action="<?php echo url_wrap('/staff/logout.php'); ?>" method="post" id="logout-confirmation">
        <?php echo "Are you sure you want to log out of your account?"; ?>
        
        <div id="submit-response">
            <br />
            <span><a href="logout_confirm.php" class="btn btn-primary" id="link_layout">Yes, Log Out</a></span>
            <span><a href="javascript:history.back()" class="btn btn-primary" id="link_layout">"No, Cancel"</a></span>
        </div>
    </div>


      </div>
    
    </main>

</div>

<?php include(SHARED_PATH . '/footer.php'); ?>