<?php
require_once "../../private/config.php";

require_password_reset();

$id = $_GET["id"] ?? "This id is currently unavailable.";
$roles = ["Admin", "Doctor", "Nurse", "Pharmacist", "Receptionist"]; // for the "roles" field
$departments = ["Administrative/Management", "Medicine", "Nursing", "Pharmacy", "Reception"]; // for the "department" field

if (!isset($_GET["id"])) {
  redirect_to(url_wrap("/staff/index.php"));
}

if (!hasPermission('edit_staff')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

// for image editing
$old_image = $_POST["old_image"] ?? null;
$new_image_name = $old_image; // default: keep old image

if (!empty($_FILES["profile_image"]["name"])) {
  $tmp = $_FILES["profile_image"]["tmp_name"];
  $ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
  $allowed = ["jpg", "jpeg", "png", "webp"];

  if (in_array($ext, $allowed)) {
    // Generate new filename
    $new_image_name = "staff_" . time() . "." . $ext;
    $destination = __DIR__ . "/images/staff_pictures/" . $new_image_name;

    // Delete old image
    if ($old_image && file_exists(__DIR__ . "/../public/uploads/staff/" . $old_image)) {
      unlink(__DIR__ . "/../public/uploads/staff/" . $old_image);
    }
  }
}

if (is_post_request()) {
  // Handle form values sent by edit.php

  $staff = [];
  $staff["id"] = $id;
  $staff["full_name"] = $_POST["full_name"] ?? "";
  $staff["email"] = $_POST["email"] ?? "";
  $staff["hashed_password"] = $_POST["password"] ?? "";
  $staff["confirm_password"] = $_POST["confirm_password"] ?? "";
  $staff["role"] = $_POST["role"] ?? "";
  $staff["department"] = $_POST["department"] ?? "";
  $staff["profile_image"] = $new_image_name ?? "";
  //$staff['status']= $_POST['status'] ?? '';

  $result = update_staff($staff);
  if ($result === true) {
    $_SESSION["message"] = "Staff edited succesfully!";

    redirect_to(url_wrap("/staff/view.php?id=" . $id));
  } else {
    $errors = $result;
  }
} else {
  $staff = find_staff_by_id($id);

  if (!$staff) {
    redirect_to(url_wrap("/staff/index.php?msg=notfound"));
    exit();
  }
}
?>

<?php
$page_title = v_wrap($staff["full_name"]);

$specificCss = "/assets/css/add_staff.css";

include SHARED_PATH . "/header.php";
?>

<div id="content">
  
  <?php include SHARED_PATH . "/navigation.php"; ?>
    
  
  <main class="main-content">
      
    <a href="javascript:history.back()" class="btn btn-primary" id="link_layout">
  <i class="bi bi-arrow-left"></i> Back
</a>
      
  <div class="top">
        <p class="top-head">Account Editing </p> 
    <p class="top-description">correction of account details for existing health centre staff</p>
  </div>
    <div id="ajax-message" class="ajax-message" hidden></div>
    <?php echo display_errors($errors); ?>
    
 <div class="role-head"><?php
 echo v_wrap($staff["full_name"]);
 echo " - Edit";
 ?></div>
  
  <div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="staff-edit" data-tab="staff-edit">
   Edit Staff
      </button>
   
   <?php if ($_SESSION['role_id'] === 1) { ?>
    <button role="tab" class="tab-btn" aria-selected="true" aria-controls="admin-password-reset" data-tab="admin-password-reset">
   Reset Password by Admin
      </button> 
    <?php } ?>
    
    <button id="delete-tab-btn" role="tab" class="tab-btn" aria-selected="false" aria-controls="pic-delete" data-tab="pic-delete">
   Delete Picture
      </button>
    
 </div>
  
    <div id="staff-edit" class="tab-content active" role="tabpanel" aria-labelledby="staff-edit-tab">
     <form action="<?php echo url_wrap("/staff/edit.php?id=" . v_wrap(u_wrap($id))); ?>" method="post" enctype="multipart/form-data">
    
       <input type="hidden" name="old_image" value="<?php echo $staff["profile_image"]; ?>">
       
    <dl>
    <dt>Full Name:</dt>
    <dd><input type="text" name="full_name" value="<?php echo v_wrap($staff["full_name"]); ?>" /></dd>
    </dl>
    
    <dl>
    <dt>Staff ID:</dt>
    <dd><?php echo v_wrap($staff["system_staff_id"]); ?></dd>
    </dl>
    
    <dl>
    <dt>Email:</dt>
    <dd><input type="email" name="email" value="<?php echo v_wrap($staff["email"]); ?>" /></dd>
    </dl>
    
    <dl>
    <dt>Password:</dt>
    <dd><input type="password" name="password" value="" placeholder="Type new password here(optional)" /></dd>
    </dl>
       
    <dl>
    <dt>Confirm Password:</dt>
    <dd><input type="password" name="confirm_password" value="" placeholder="Confirm password if typed above." /></dd>
    </dl>
     
         <p>Passwords should be at least 12 characters and include at least one UPPERCASE letter, one lowercase letter, 1 number and one $ymbol. </p>
        
    <dl>
        <dt>Role</dt>
        <dd>
          <select name="role">
  <?php foreach ($roles as $role) { ?>
    <option value="<?php echo $role; ?>"
      <?php if ($staff["role"] === $role) {
        echo "selected";
      } ?>>
      <?php echo v_wrap($role); ?>
    </option>
  <?php } ?>
</select>
    </dd>
    </dl>
       
    <dl>
        <dt>Department</dt>
        <dd>
          <select name="department">
  <?php foreach ($departments as $department) { ?>
    <option value="<?php echo $department; ?>"
      <?php if ($staff["department"] === $department) {
        echo "selected";
      } ?>>
      <?php echo v_wrap($department); ?>
    </option>
  <?php } ?>
</select>
    </dd>
    </dl>
       
    <dl>
    <dt>Professional Headshot: </dt>
    <dd>
    <input type="file" name="profile_image" accept="image/*">
    <?php if (!empty($staff["profile_image"])) {
      echo v_wrap($staff["profile_image"]); ?>    
    
      <span id="open-delete-tab" data-id="<?php echo $staff["id"]; ?>" data-image="<?php echo $staff[
  "profile_image"
]; ?>" style="cursor:pointer;">
      <i class="bi bi-trash text-danger"></i>
      </span>
    <?php
    } ?>
      
    </dd>
    </dl>
       
       
    
    <div id="submit-response"><input type="submit" name="submit" value="Update Staff" /></div>
     </form>
    </div>
    
    
    <?php // for accessing admin password reset button 
    if ($_SESSION['role_id'] === 1) { ?>
    <div id="admin-password-reset" class="tab-content" hidden role="tabpanel" aria-labelledby="admin-password-reset-tab">
    <form  action="<?php echo url_wrap("/staff/reset_password_admin.php"); ?>" method="post">
      
      <p>Please do this with <?php
      echo v_wrap($staff["full_name"]);
      echo "'s";
      ?> approval ONLY.</p>
      
    <input type="hidden" name="staff_id" value="<?php echo $staff["id"]; ?>">

      <button type="submit">Reset Password</button>
    </form>
    </div>
    <?php } ?>
    
    <?php
// for deleting professional headshot
?>
    <div id="pic-delete" class="tab-content" hidden role="tabpanel" aria-labelledby="pic-delete-tab">
      
      <p>Are you sure you want to delete <?php
      echo v_wrap($staff["full_name"]);
      echo "'s";
      ?> profile image?</p>
      
      <form action="<?php echo url_wrap("/staff/edit.php"); ?>" method="post">
        
        <input type="hidden" name="delete_image" value="<?php echo $staff["profile_image"]; ?>" />
        
        <div id="submit-response">
          <button type="button" id="confirm-delete" value="Yes, Delete Image" class="btn btn-danger">Yes, Delete Image</button>
          <button type="button" class="btn btn-secondary" id="cancel-delete">Cancel</button>
        </div>
        
      </form>
    </div>
    
      </main>
  
</div>

<?php include SHARED_PATH . "/footer.php"; ?>


