<?php 
require_once('../../private/config.php'); 

require_password_reset();

if (!hasPermission('view_staff')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}


?>


<?php 
$page_title = 'Staff List';

$specificCss = '/assets/css/add_staff.css';
$defaultStaffImage = 'default_profile_pic.png';

$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

if ($page < 1) {
	$page = 1;
	}

$totalPages = total_page_count_for_staff($limit);

$role = $_GET['role'] ?? null;
$search = $_GET['search'] ?? null;
$startDate = $_GET['start'] ?? null;
$endDate = $_GET['end'] ?? null;
$onlyAdmin = isset($_GET['admin']);
$onlyDoctor = isset($_GET['doctor']);
$onlyNurse = isset($_GET['nurse']);
$onlyReceptionist = isset($_GET['receptionist']);
$onlyPharmacist = isset($_GET['pharmacist']);


$admin = find_staff_by_role($role, $search, $startDate, $endDate, $onlyAdmin, $onlyDoctor, $onlyNurse, $onlyReceptionist, $onlyPharmacist, $limit, $offset);


include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    
  <div class="top">
    <p class="top-head">Staff Records </p> 
    <p class="top-description">list of all health centre staff</p>
  </div>
   
  
  <div id="ajax-message" class="ajax-message" hidden></div>
  <div><?php echo display_session_message(); ?></div>
  <?php if (isset($_SESSION['error'])) { echo "<div style='color:#d93025; background:#fce8e6; padding:10px; border-radius:5px; margin-bottom:15px; border:1px solid #d93025;'>" . v_wrap($_SESSION['error']) . "</div>"; unset($_SESSION['error']); } ?>

<?php if (hasPermission('view_staff')) { ?>
    
<div class="above-tabs">
  <button data-modal-target="addStaffModal" class="add-staff" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:600;"> + Add Staff</button>
</div>

<!-- Add Staff Modal -->
<div id="addStaffModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" data-modal-close>&times;</button>
        <h3 class="modal-title"><i class="bi bi-person-plus"></i> Add New Staff</h3>
        
        <form action="<?php echo url_wrap('/staff/new.php'); ?>" method="post" enctype="multipart/form-data">
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Full Name *</label>
                <input type="text" name="full_name" placeholder="Last name, First name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Email *</label>
                <input type="email" name="email" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Password</label>
                <small>Automatically Generated</small>
              </div>

            <div class="form-group" style="margin-bottom: 15px;">
                <label>Role *</label>
                <select name="role" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    <option value="" disabled selected>Select staff role</option>
                    <option value="admin">Admin</option>
                    <option value="doctor">Doctor</option>
                    <option value="nurse">Nurse</option>
                    <option value="pharmacist">Pharmacist</option>
                    <option value="receptionist">Receptionist</option>
                </select>
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Department *</label>
                <input type="text" name="department" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>
            
            <div class="form-group" style="margin-bottom: 15px;">
                <label>Professional Headshot *</label>
                <input type="file" name="profile_image" accept="image/*" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width: 100%;">Register Staff</button>
        </form>
    </div>
</div>
    
<?php } ?>
    
  <div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="admin" data-tab="admin">
    Admin
  </button>
    <button role="tab" class="tab-btn" aria-selected="false" aria-controls="doctors" data-tab="doctors">
    Doctors
  </button>
    <button role="tab" class="tab-btn" aria-selected="false" aria-controls="nurses" data-tab="nurses">
    Nurses
  </button>
    <button role="tab" class="tab-btn" aria-selected="false" aria-controls="pharmacists" data-tab="pharmacists">
    Pharmacists
  </button>
    <button role="tab" class="tab-btn" aria-selected="false" aria-controls="receptionists" data-tab="receptionists">
    Receptionists
  </button>
    <button id="delete-tab-btn" role="tab" class="tab-btn" aria-selected="false" aria-controls="staff-delete" data-tab="staff-delete">
   Delete Staff Account
      </button>
  </div>
    
    
    
     <div id="admin" class="tab-content active" role="tabpanel" aria-labelledby="admin-tab">
       
       <form method="GET" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
	
	<div>
	
	<span style="color: #666666; height: 15px; border: 1px solid #666666; border-left: 3px solid green; padding: 4px 2px;">
	
	<i class="bi bi-search"></i>
	
		<input type="text" name="search" placeholder="Search first/middle/surname..." 
		style="color: #666666; outline: none; border: none; margin: 15px 0; width: 200px; height: 15px;" 
		value="<?php echo v_wrap($search ?? ''); ?>">
		
		</span></div>
	
	   <label>
		 Start Date <input type="date" name="start" value="<?php echo $startDate ?? ''; ?>"> 
	   </label>
	   
	   <label>
		 End Date <input type="date" name="end" value="<?php echo $endDate ?? ''; ?>"> 
	   </label>

		<label>
			<input type="checkbox" name="Admin"<?php if($onlyAdmin) echo 'checked'; ?>> Admin only 
		</label>
		
	<div>
		<button id="filterBtn" type="submit">Filter</button></div>
	</form> 
       
    <table class="staff-list">
  	  <tr>
        <th>Photo</th>
        <th>Staff ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Time Registered</th>
  	    <th>View</th>
  	    <th>Edit</th>
        <th>Delete</th>
        
  	  </tr>
      <?php 
      $admin = find_admin_and_super_admin();
      
      while($staff = $admin->fetch_assoc()) { ?>
        
        <tr>
          <td><?php if (!empty($staff['profile_image']) && file_exists(__DIR__ . '/images/staff_pictures/' . $staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('/staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="Staff profile photo" class="patient-profile-thumbnail"
                <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="No Staff photo uploaded" class="patient-profile-thumbnail"
                 <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
          <?php } ?></td>
          <td><?php echo v_wrap($staff['system_staff_id']); ?></td>
          <td><?php echo v_wrap($staff['full_name']); ?></td>
          <td><?php echo v_wrap($staff['email']); ?></td>
          <td><?php echo v_wrap($staff['department']); ?></td>
          <td><?php echo v_wrap($staff['created_at']); ?></td>
          <td><a class="view-staff" href="<?php echo url_wrap('/staff/view.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-eye"></i></a></td>
          <td><a class="edit-staff" href="<?php echo url_wrap('/staff/edit.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-pencil-square"></i></a></td>
          <td><?php if (hasPermission('delete_staff')) { ?><span class="open-delete-tab" data-id="<?php echo v_wrap($staff['id']); ?>" data-name="<?php echo v_wrap($staff['full_name']); ?>" style="cursor:pointer;" ><i class="bi bi-trash"></i></span><?php } ?></td>
    	  </tr>
      <?php } // close while statement ?>
       </table>
       
       
  </div>
      
    
    <div id="doctors" class="tab-content" role="tabpanel" aria-labelledby="doctors-tab" hidden>
      
      <form method="GET" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
	
	<div>
	
	<span style="color: #666666; height: 15px; border: 1px solid #666666; border-left: 3px solid green; padding: 4px 2px;">
	
	<i class="bi bi-search"></i>
	
		<input type="text" name="search" placeholder="Search first/middle/surname..." 
		style="color: #666666; outline: none; border: none; margin: 15px 0; width: 200px; height: 15px;" 
		value="<?php echo v_wrap($search ?? ''); ?>">
		
		</span></div>
	
	   <label>
		 Start Date <input type="date" name="start" value="<?php echo $startDate ?? ''; ?>"> 
	   </label>
	   
	   <label>
		 End Date <input type="date" name="end" value="<?php echo $endDate ?? ''; ?>"> 
	   </label>

		<label>
			<input type="checkbox" name="Doctor"<?php if($onlyDoctor) echo 'checked'; ?>> Doctor only 
		</label>
		
	<div>
		<button id="filterBtn" type="submit">Filter</button></div>
	</form> 
      
   <table class="staff-list">
  	  <tr>
        <th>Photo</th>
        <th>Staff ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Time Registered</th>
  	    <th>View</th>
  	    <th>Edit</th>
        <th>Delete</th>
  	  </tr>
      <?php 
      $admin = find_staff_by_role('doctor');
      
      while($staff = $admin->fetch_assoc()) { ?>
        
        <tr>
          <td><?php if (!empty($staff['profile_image']) && file_exists(__DIR__ . '/images/staff_pictures/' . $staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('/staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="Staff profile photo" class="patient-profile-thumbnail"
                <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="No Staff photo uploaded" class="patient-profile-thumbnail"
                 <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
          <?php } ?></td>
          <td><?php echo v_wrap($staff['system_staff_id']); ?></td>
          <td><?php echo v_wrap($staff['full_name']); ?></td>
          <td><?php echo v_wrap($staff['email']); ?></td>
          <td><?php echo v_wrap($staff['department']); ?></td>
          <td><?php echo v_wrap($staff['created_at']); ?></td>
          <td><a class="view-staff" href="<?php echo url_wrap('/staff/view.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-eye"></i></a></td>
          <td><a class="edit-staff" href="<?php echo url_wrap('/staff/edit.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-pencil-square"></i></a></td>
          <td><?php if (hasPermission('delete_staff')) { ?><span class="open-delete-tab" data-id="<?php echo v_wrap($staff['id']); ?>" data-name="<?php echo v_wrap($staff['full_name']); ?>" style="cursor:pointer;" ><i class="bi bi-trash"></i></span><?php } ?></td>
    	  </tr>
      <?php } // close while statement ?>

       </table>
  </div>
    
    
    <div id="nurses" class="tab-content" role="tabpanel" aria-labelledby="nurses-tab" hidden>
      
      <form method="GET" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
	
	<div>
	
	<span style="color: #666666; height: 15px; border: 1px solid #666666; border-left: 3px solid green; padding: 4px 2px;">
	
	<i class="bi bi-search"></i>
	
		<input type="text" name="search" placeholder="Search first/middle/surname..." 
		style="color: #666666; outline: none; border: none; margin: 15px 0; width: 200px; height: 15px;" 
		value="<?php echo v_wrap($search ?? ''); ?>">
		
		</span></div>
	
	   <label>
		 Start Date <input type="date" name="start" value="<?php echo $startDate ?? ''; ?>"> 
	   </label>
	   
	   <label>
		 End Date <input type="date" name="end" value="<?php echo $endDate ?? ''; ?>"> 
	   </label>

		<label>
			<input type="checkbox" name="Nurse"<?php if($onlyNurse) echo 'checked'; ?>> Nurse only 
		</label>
		
	<div>
		<button id="filterBtn" type="submit">Filter</button></div>
	</form> 
      
    <table class="staff-list">
  	  <tr>
        <th>Photo</th>
        <th>Staff ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Time Registered</th>
  	    <th>View</th>
  	    <th>Edit</th>
        <th>Delete</th>
  	  </tr>
      <?php 
      $admin = find_staff_by_role('nurse');
      
      while($staff = $admin->fetch_assoc()) { ?>
        
        <tr>
          <td><?php if (!empty($staff['profile_image']) && file_exists(__DIR__ . '/images/staff_pictures/' . $staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('/staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="Staff profile photo" class="patient-profile-thumbnail"
                <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="No Staff photo uploaded" class="patient-profile-thumbnail"
                 <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
          <?php } ?></td>
          <td><?php echo v_wrap($staff['system_staff_id']); ?></td>
          <td><?php echo v_wrap($staff['full_name']); ?></td>
          <td><?php echo v_wrap($staff['email']); ?></td>
          <td><?php echo v_wrap($staff['department']); ?></td>
          <td><?php echo v_wrap($staff['created_at']); ?></td>
          <td><a class="view-staff" href="<?php echo url_wrap('/staff/view.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-eye"></i></a></td>
          <td><a class="edit-staff" href="<?php echo url_wrap('/staff/edit.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-pencil-square"></i></a></td>
          <td><?php if (hasPermission('delete_staff')) { ?><span class="open-delete-tab" data-id="<?php echo v_wrap($staff['id']); ?>" data-name="<?php echo v_wrap($staff['full_name']); ?>" style="cursor:pointer;" ><i class="bi bi-trash"></i></span><?php } ?></td>
    	  </tr>
      <?php } // close while statement ?>

       </table>
  </div>
    
  
    
    <div id="pharmacists" class="tab-content" role="tabpanel" aria-labelledby="pharmacists-tab" hidden>
      
      <form method="GET" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
	
	<div>
	
	<span style="color: #666666; height: 15px; border: 1px solid #666666; border-left: 3px solid green; padding: 4px 2px;">
	
	<i class="bi bi-search"></i>
	
		<input type="text" name="search" placeholder="Search first/middle/surname..." 
		style="color: #666666; outline: none; border: none; margin: 15px 0; width: 200px; height: 15px;" 
		value="<?php echo v_wrap($search ?? ''); ?>">
		
		</span></div>
	
	   <label>
		 Start Date <input type="date" name="start" value="<?php echo $startDate ?? ''; ?>"> 
	   </label>
	   
	   <label>
		 End Date <input type="date" name="end" value="<?php echo $endDate ?? ''; ?>"> 
	   </label>

		<label>
			<input type="checkbox" name="Pharmacist"<?php if($onlyPharmacist) echo 'checked'; ?>> Pharmacist only 
		</label>
		
	<div>
		<button id="filterBtn" type="submit">Filter</button></div>
	</form> 
      
    <table class="staff-list">
  	  <tr>
        <th>Photo</th>
        <th>Staff ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Time Registered</th>
  	    <th>View</th>
  	    <th>Edit</th>
        <th>Delete</th>
  	  </tr>
      <?php 
      $admin = find_staff_by_role('pharmacist');
      
      while($staff = $admin->fetch_assoc()) { ?>
        
        <tr>
          <td><?php if (!empty($staff['profile_image']) && file_exists(__DIR__ . '/images/staff_pictures/' . $staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('/staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="Staff profile photo" class="patient-profile-thumbnail"
                <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="No Staff photo uploaded" class="patient-profile-thumbnail"
                 <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
          <?php } ?></td>
          <td><?php echo v_wrap($staff['system_staff_id']); ?></td>
          <td><?php echo v_wrap($staff['full_name']); ?></td>
          <td><?php echo v_wrap($staff['email']); ?></td>
          <td><?php echo v_wrap($staff['department']); ?></td>
          <td><?php echo v_wrap($staff['created_at']); ?></td>
          <td><a class="view-staff" href="<?php echo url_wrap('/staff/view.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-eye"></i></a></td>
          <td><a class="edit-staff" href="<?php echo url_wrap('/staff/edit.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-pencil-square"></i></a></td>
          <td><?php if (hasPermission('delete_staff')) { ?><span class="open-delete-tab" data-id="<?php echo v_wrap($staff['id']); ?>" data-name="<?php echo v_wrap($staff['full_name']); ?>" style="cursor:pointer;" ><i class="bi bi-trash"></i></span><?php } ?></td>
    	  </tr>
      <?php } // close while statement ?>

       </table>
  </div>
    
    
    <div id="receptionists" class="tab-content" role="tabpanel" aria-labelledby="receptionists-tab" hidden>
      
      <form method="GET" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
	
	<div>
	
	<span style="color: #666666; height: 15px; border: 1px solid #666666; border-left: 3px solid green; padding: 4px 2px;">
	
	<i class="bi bi-search"></i>
	
		<input type="text" name="search" placeholder="Search first/middle/surname..." 
		style="color: #666666; outline: none; border: none; margin: 15px 0; width: 200px; height: 15px;" 
		value="<?php echo v_wrap($search ?? ''); ?>">
		
		</span></div>
	
	   <label>
		 Start Date <input type="date" name="start" value="<?php echo $startDate ?? ''; ?>"> 
	   </label>
	   
	   <label>
		 End Date <input type="date" name="end" value="<?php echo $endDate ?? ''; ?>"> 
	   </label>

		<label>
			<input type="checkbox" name="Receptionist"<?php if($onlyReceptionist) echo 'checked'; ?>> Receptionist only 
		</label>
		
	<div>
		<button id="filterBtn" type="submit">Filter</button></div>
	</form> 
      
    <table class="staff-list">
  	  <tr>
        <th>Photo</th>
        <th>Staff ID</th>
        <th>Full Name</th>
        <th>Email</th>
        <th>Department</th>
        <th>Time Registered</th>
  	    <th>View</th>
  	    <th>Edit</th>
        <th>Delete</th>
  	  </tr>
      <?php 
      $admin = find_staff_by_role('receptionist');
      
      while($staff = $admin->fetch_assoc()) { ?>
        
        <tr>
          <td><?php if (!empty($staff['profile_image']) && file_exists(__DIR__ . '/images/staff_pictures/' . $staff['profile_image'])) { ?>
            <img src="<?php echo url_wrap('/staff/images/staff_pictures/' . v_wrap(ru_wrap($staff['profile_image']))); ?>" alt="Staff profile photo" class="patient-profile-thumbnail"
                <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultStaffImage));?>" alt="No Staff photo uploaded" class="patient-profile-thumbnail"
                 <?php if (($staff['status']) == 'active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($staff['status']) == 'inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>>
          <?php } ?></td>
          <td><?php echo v_wrap($staff['system_staff_id']); ?></td>
          <td><?php echo v_wrap($staff['full_name']); ?></td>
          <td><?php echo v_wrap($staff['email']); ?></td>
          <td><?php echo v_wrap($staff['department']); ?></td>
          <td><?php echo v_wrap($staff['created_at']); ?></td>
          <td><a class="view-staff" href="<?php echo url_wrap('/staff/view.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-eye"></i></a></td>
          <td><a class="edit-staff" href="<?php echo url_wrap('/staff/edit.php?id=' . v_wrap(u_wrap($staff['id']))); ?>"><i class="bi bi-pencil-square"></i></a></td>
          <td><?php if (hasPermission('delete_staff')) { ?><span class="open-delete-tab" data-id="<?php echo v_wrap($staff['id']); ?>" data-name="<?php echo v_wrap($staff['full_name']); ?>" style="cursor:pointer;" ><i class="bi bi-trash"></i></span><?php } ?></td>
    	  </tr>
      <?php } // close while statement ?>

       </table>
  </div>
    
    <div style="margin-top:20px; text-align: center;">

<?php
$queryString = http_build_query([
  'search' => $search,
  'start' => $startDate,
  'end' => $endDate,
  'admin' => $onlyAdmin ? 1 : null,
  'doctor' => $onlyDoctor ? 1 : null,
  'nurse' => $onlyNurse ? 1 : null,
  'pharmacist' => $onlyPharmacist ? 1 : null,
  'receptionist' => $onlyReceptionist ? 1 : null
]);
?>

  
  <?php if ($page > 1) { ?>
  <a href="?page=<?php echo $page - 1; ?>&<?php echo $queryString; ?>">⬅ Prev</a>
<?php } ?>

  <span> Page <?php echo $page; ?> </span>
  
	<?php if ($page < $totalPages) { ?>
		<a href="?page=<?php echo $page + 1; ?>&<?php echo $queryString; ?>">Next ➡</a>
	<?php } ?>
  

</div>
        
        <?php // for deleting a staff account ?>
    <div id="staff-delete" class="tab-content" hidden role="tabpanel" aria-labelledby="staff-delete-tab">
          
          
      <p></p>
          
      
      <form action="<?php echo url_wrap('/staff/index.php'); ?>" method="post">
        
     <?php //  <input type="hidden" name="delete_image" value="<?php echo $staff['profile_image']; " />?>
        
        <div id="submit-response">
          <button type="button" id="confirm-delete" value="Yes, Delete Account" class="btn btn-danger">Yes, Delete Account</button>
          <button type="button" class="btn btn-secondary" id="cancel-delete">Cancel</button>
        </div>
        
      </form>
    </div>
    
     </main>
  
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
