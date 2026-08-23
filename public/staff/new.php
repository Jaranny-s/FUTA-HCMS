<?php 
require_once('../../private/config.php'); 

require_password_reset();

if (!hasPermission('create_staff')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

?>

<?php 
    // TO UPLOAD IMAGES
    $image_name = null;

if (!empty($_FILES['profile_image']['name'])) {

    $original_name = $_FILES['profile_image']['name'];
    $tmp_path = $_FILES['profile_image']['tmp_name'];

    // Get file extension
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // Allow only images
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed)) {

        // Create unique filename
        $image_name = 'staff_' . time() . '.' . $ext;

        $destination = __DIR__ . '/images/staff_pictures/' . $image_name;

        move_uploaded_file($tmp_path, $destination);
    }
}
 ?>
<?php 
if(is_post_request()) {
    
    $staff = [];
    $staff['full_name'] = $_POST['full_name'] ?? '';
    $staff['email'] = $_POST['email'] ?? '';
    $staff['hashed_password'] = $_POST['password'] ?? '';
    $staff['confirm_password'] = $_POST['confirm_password'] ?? '';
    $staff['role'] = $_POST['role'] ?? '';
    $staff['department'] = $_POST['department'] ?? '';
    $staff['profile_image'] = $image_name ?? '';
    $staff['password_reset_required'] = 1 ?? '';
    
    
    
    $result = insert_staff($staff);
    if (is_array($result) && isset($result['success']) && $result['success']) {

    $new_id = $result['id'];

    $_SESSION['message'] = "Staff created successfully!";
    $_SESSION['temp_password'] = $result['temp_password'];

    redirect_to(url_wrap('/staff/view.php?id=' . $new_id));

    } else {
        
    // INSERT failed
        $errors = $result;
    }
    
    } else {
    // display the blank form
}

$staff = [];
?>

<?php 
$page_title = 'Add Staff';

$specificCss = '/assets/css/add_staff.css'; 


include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
    
  
  <main class="main-content">
      
    <a href="javascript:history.back()" class="btn btn-primary" id="link_layout">
  <i class="bi bi-arrow-left"></i> Back
</a>
      
  <div class="top">
        <p class="top-head">Account Creation </p> 
    <p class="top-description">creation of account details for new health centre staff</p>
  </div>
      
<?php echo display_errors($errors); ?>
      
 <div class="role-head">Add Staff</div>
  
  <div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="staff-registration" data-tab="staff-registration">
    Staff Registration Form
      </button>
 </div>
  
    <div id="staff-registration" class="tab-content active" role="tabpanel" aria-labelledby="staff-registration-tab">
     
<form action="<?php echo url_wrap('/staff/new.php'); ?>" method="post" enctype="multipart/form-data">
    <dl>
    <dt>Full Name:</dt>
    <dd><input type="text" name="full_name" value="" placeholder="Staff last name, first name" required/></dd>
    </dl>
    
    <dl>
    <dt>Staff ID:</dt>
    <dd><span class="note-for-staff-id"><?php echo "NOTE: This will be automatically created if you fill the rest of the form and click ' REGISTER STAFF '. " ?></span></dd>
    </dl>
    
    <dl>
    <dt>Email:</dt>
    <dd><input type="email" name="email" value="" placeholder="Staff email" required/></dd>
    </dl>
    
    <dl>
    <dt>Password:</dt>
    <dd><span class="note-for-staff-id"><?php echo "NOTE: This will also be automatically created if you fill the rest of the form and click ' REGISTER STAFF '. " ?></span></dd>
    </dl>

    <dl>
    <dt>Role:</dt>
    <dd><select name="role" id="role" required>
    <option value="" disabled selected>Select staff role</option>
    <option value="admin">Admin</option>
    <option value="doctor">Doctor</option>
    <option value="nurse">Nurse</option>
    <option value="pharmacist">Pharmacist</option>
    <option value="receptionist">Receptionist</option>
</select></dd>
    </dl>
    
    <dl>
    <dt>Department:</dt>
    <dd><input type="text" name="department" value="" placeholder="Staff department in Health Centre" required/></dd>
    </dl>
    
    <dl>
    <dt>Professional Headshot:</dt>
    <dd><input type="file" name="profile_image" accept="image/*" required/></dd>
    </dl>
    
        
    
    <div id="submit-response"><input type="submit" name="submit" value="Register Staff" /></div>
    
</form>
  </div>
 </main>
  
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>