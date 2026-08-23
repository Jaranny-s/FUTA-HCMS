<?php 
require_once('../../private/config.php'); 

require_password_reset();

?>
<?php 

$id = $_GET['id'] ?? 'This id is unavailable';

$staff = find_staff_by_id($id);

$page_title = v_wrap($staff['full_name']);



include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
    
  
  <main class="main-content">
      
    <a href="javascript:history.back()" class="btn btn-primary" id="link_layout">
  <i class="bi bi-arrow-left"></i> Back
</a>
      
  <div class="top">
        <p class="top-head">Account Review </p> 
    <p class="top-description">view of account details for existing health centre staff</p>
  </div>
<div><?php echo display_session_message(); ?></div>
<div><?php echo display_temp_password(); ?></div>
    
 <div class="role-head"><?php echo v_wrap($staff['full_name']); echo"'s Details"; ?></div>
      
<div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="staff-view" data-tab="staff-view">
    Staff Account Details
      </button>
 </div>
  
    <div id="staff-view" class="tab-content active" role="tabpanel" aria-labelledby="staff-view-tab">
      <div class = "staff-name-and-picture">
    <dl>
    <dt>Full Name:</dt>
    <dd><?php echo v_wrap($staff['full_name']); ?></dd>
    </dl>
    
    <dl>
    <dt>Staff ID:</dt>
    <dd><?php echo v_wrap($staff['system_staff_id']); ?></dd>
    </dl>
    
    <dl>
    <dt>Email:</dt>
    <dd><?php echo v_wrap($staff['email']); ?></dd>
    </dl>
    <dl>
    <dt>Role:</dt>
    <dd><?php echo v_wrap($staff['role']); ?></dd>
    </dl>
    
    <dl>
    <dt>Department:</dt>
    <dd><?php echo v_wrap($staff['department']); ?></dd>
    </dl>
    
    <dl>
    <dt>Professional Headshot:</dt>
    <dd><?php echo v_wrap($staff['profile_image']); ?></dd>
    </dl>
    </div>  
        <?php if (!empty($staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="Staff profile photo" class="staff-profile-header">
        <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="No Staff photo uploaded" class="staff-profile-header">
        <?php } ?>
        </div>
 </main>
  
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>