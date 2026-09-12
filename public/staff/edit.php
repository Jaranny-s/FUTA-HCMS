<?php
require_once "../../private/config.php";

require_password_reset();

$id = $_GET["id"] ?? null;
if (!$id) {
  redirect_to(url_wrap("/staff/index.php"));
  exit();
}

if (!hasPermission('edit_staff')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
    exit();
}

$existingStaff = find_staff_by_id($id);
if (!$existingStaff) {
  $_SESSION['error'] = "Staff account not found.";
  redirect_to(url_wrap("/staff/index.php"));
  exit();
}

// Protect super_admin account: only super_admin can edit
if (($existingStaff['role'] === 'super_admin' || $existingStaff['role_id'] == 6) && ($_SESSION['staff_role'] ?? '') !== 'super_admin') {
  $_SESSION['error'] = "Access Denied: Only Super Admin can edit Super Admin accounts.";
  redirect_to(url_wrap("/staff/index.php"));
  exit();
}

$roles = ["Admin", "Doctor", "Nurse", "Pharmacist", "Receptionist"];
$departments = ["Administrative/Management", "Medicine", "Nursing", "Pharmacy", "Reception"];
$errors = [];

$new_image_name = $existingStaff['profile_image']; // default: keep existing image

if (!empty($_FILES["profile_image"]["name"]) && $_FILES["profile_image"]["error"] !== UPLOAD_ERR_NO_FILE) {
  $tmp = $_FILES["profile_image"]["tmp_name"];
  $ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
  $allowed = ["jpg", "jpeg", "png", "webp"];

  if (in_array($ext, $allowed)) {
    $new_image_name = "staff_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
    $uploadDir = __DIR__ . "/images/staff_pictures/";
    if (!file_exists($uploadDir)) {
      mkdir($uploadDir, 0777, true);
    }
    $destination = $uploadDir . $new_image_name;

    if (move_uploaded_file($tmp, $destination)) {
      // Delete old image if not default
      if (!empty($existingStaff['profile_image']) && $existingStaff['profile_image'] !== 'default_profile_pic.png' && file_exists($uploadDir . $existingStaff['profile_image'])) {
        unlink($uploadDir . $existingStaff['profile_image']);
      }
    } else {
      $errors[] = "Failed to upload new profile image.";
    }
  } else {
    $errors[] = "Invalid image format. Allowed formats: JPG, JPEG, PNG, WEBP.";
  }
}

if (is_post_request()) {
  $staff = [];
  $staff["id"] = $id;
  $staff["full_name"] = $_POST["full_name"] ?? "";
  $staff["email"] = $_POST["email"] ?? "";
  $staff["hashed_password"] = $_POST["password"] ?? "";
  $staff["confirm_password"] = $_POST["confirm_password"] ?? "";
  $staff["role"] = $_POST["role"] ?? "";
  $staff["department"] = $_POST["department"] ?? "";
  $staff["profile_image"] = $new_image_name ?? $existingStaff['profile_image'];
  $staff["status"] = $_POST["status"] ?? $existingStaff['status'] ?? 'active';

  if (empty($errors)) {
    $result = update_staff($staff);
    if ($result === true) {
      $_SESSION["message"] = "Staff edited successfully!";
      redirect_to(url_wrap("/staff/view.php?id=" . $id));
    } else {
      $errors = is_array($result) ? $result : [$result];
    }
  }
  // Keep form data on validation error
  $staff["system_staff_id"] = $existingStaff["system_staff_id"];
} else {
  $staff = $existingStaff;
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
          <select name="role" id="edit_staff_role">
  <?php 
  $roleMap = [
      'admin' => 'Admin',
      'doctor' => 'Doctor',
      'nurse' => 'Nurse',
      'pharmacist' => 'Pharmacist',
      'receptionist' => 'Receptionist'
  ];
  foreach ($roleMap as $rKey => $rLabel) { ?>
    <option value="<?php echo $rKey; ?>"
      <?php if (strtolower($staff["role"]) === $rKey) {
        echo "selected";
      } ?>>
      <?php echo v_wrap($rLabel); ?>
    </option>
  <?php } ?>
</select>
    </dd>
    </dl>
       
    <dl>
        <dt>Department</dt>
        <dd>
          <select name="department" id="edit_staff_dept">
    <option value="<?php echo v_wrap($staff["department"]); ?>" selected>
      <?php echo v_wrap($staff["department"]); ?>
    </option>
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

    <dl>
    <dt>Account Status:</dt>
    <dd>
      <select name="status" id="edit_staff_status">
        <option value="active" <?php if (strtolower($staff["status"] ?? 'active') === 'active') echo "selected"; ?>>Active</option>
        <option value="inactive" <?php if (strtolower($staff["status"] ?? '') === 'inactive') echo "selected"; ?>>Inactive</option>
      </select>
    </dd>
    </dl>
       
       
    
    <div id="submit-response"><input type="submit" name="submit" value="Update Staff" /></div>
     </form>
    </div>
    
    
    <?php // for accessing admin password reset button 
    $canAdminReset = (isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1) || ($_SESSION['staff_role'] ?? '') === 'super_admin';
    $targetIsSuperAdmin = ($staff['role'] === 'super_admin' || ($staff['role_id'] ?? 0) == 6);
    if ($canAdminReset && (!$targetIsSuperAdmin || ($_SESSION['staff_role'] ?? '') === 'super_admin')) { ?>
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
  
<script>
document.addEventListener("DOMContentLoaded", function() {
    const roleDeptMap = {
        'admin': [
            'Health Centre Administration',
            'NHIS/HMO Unit'
        ],
        'doctor': [
            'General Outpatient Department (GOPD)',
            'Specialist Clinics',
            'Accident & Emergency (A&E) Unit'
        ],
        'nurse': [
            'Nursing Services Department',
            'Accident & Emergency (A&E) Unit',
            'Observation / Inpatient Wards'
        ],
        'pharmacist': [
            'Pharmacy Department'
        ],
        'receptionist': [
            'Health Records / Medical Information Department'
        ]
    };

    const roleSelect = document.getElementById('edit_staff_role');
    const deptSelect = document.getElementById('edit_staff_dept');
    const currentDept = "<?php echo addslashes($staff['department'] ?? ''); ?>";

    function updateDepts(selectedRole) {
        if (!deptSelect) return;
        deptSelect.innerHTML = '';
        const depts = roleDeptMap[selectedRole.toLowerCase()] || [];
        depts.forEach(function(d) {
            const opt = document.createElement('option');
            opt.value = d;
            opt.textContent = d;
            if (d === currentDept) opt.selected = true;
            deptSelect.appendChild(opt);
        });
        if (depts.length === 0) {
            const opt = document.createElement('option');
            opt.value = currentDept;
            opt.textContent = currentDept || '-- Select Department --';
            deptSelect.appendChild(opt);
        }
    }

    if (roleSelect) {
        updateDepts(roleSelect.value);
        roleSelect.addEventListener('change', function() {
            updateDepts(this.value);
        });
    }
});
</script>

<?php include SHARED_PATH . "/footer.php"; ?>


