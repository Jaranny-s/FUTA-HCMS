<?php 
require_once('../../private/config.php'); 

require_password_reset();

?>


<?php 
$page_title = 'Dashboard';

  
include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    
  <i class="bi bi-arrow-left"></i> Back
</a>
    
    <div class="top">
    <p class="top-head">My Dashboard </p> 
    <p class="top-description">profile info, staff management and more</p>
  </div>

  <div class="role-head"><?php echo $staff_type; ?>'s Portal</div>
  
  <div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="profile" data-tab="overview">
    Overview Profile
  </button>
    <button role="tab" class="tab-btn" aria-selected="false" aria-controls="edit" data-tab="edit">
    Edit Profile
  </button>
  </div>
  
  
  <div id="overview" class="tab-content active" role="tabpanel" aria-labelledby="overview-tab">
    <dl>
      <dt></dt>
      <dd><img src="" alt="image showing profile picture"/></dd>
    </dl>
    <dl>
      <dt>Name:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Job in Health Centre:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Staff Type:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Session:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Semester:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
  </div>
  
  <div id="edit" class="tab-content" role="tabpanel" aria-labelledby="edit-tab" hidden>
    <dl>
      <dt></dt>
      <dd><img src="" alt="edit image"/></dd>
    </dl>
    <dl>
      <dt>Name:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Job in Health Centre:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Staff Type:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Session:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    <dl>
      <dt>Semester:</dt>
      <dd><?php echo v_wrap(); ?></dd>
    </dl>
    
  </div>
    
  </main>
  
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
