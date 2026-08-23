<?php 
require_once('../../../private/config.php'); 

require_password_reset();

if (!hasPermission('view_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

?>


<?php 
$page_title = 'Patient List';

$specificCss = '/assets/css/add_staff.css';


$defaultPatientImage = 'default_profile_pic.png';

$page = $_GET['page'] ?? 1;
$limit = 20;
$offset = ($page - 1) * $limit;

if ($page < 1) {
	$page = 1;
	}

$totalPages = total_page_count_for_patients($limit);

$search = $_GET['search'] ?? null;
$startDate = $_GET['start'] ?? null;
$endDate = $_GET['end'] ?? null;
$onlyStudent = isset($_GET['Student']);
$onlyStaff = isset($_GET['Staff']);

$all_patients = find_all_patients($search, $startDate, $endDate, $onlyStudent, $onlyStaff, $limit, $offset);


include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    
  <i class="bi bi-arrow-left"></i> Back
</a>
    
  <div class="top">
    <p class="top-head">Patient Records </p> 
    <p class="top-description">list of all registered patients</p>
  </div>
   
  
  <div><?php echo display_session_message(); ?></div>

<?php if (hasPermission('view_patient')) { ?>
    
<div class="above-tabs">
  <a id="link_layout" class="add-staff" href="<?php echo url_wrap('/modules/patients/new.php'); ?>" style="margin-right: 20px"> Register Patients </a>
  <a id="link_layout" class="add-staff" href="<?php echo url_wrap('/patient/new.php'); ?>"> Import Patients </a>
</div>
    
<?php } ?>
    
  <div class="tabs" role="tablist">
  <button role="tab" class="tab-btn active" aria-selected="true" aria-controls="patient" data-tab="patient">
    All Patients
  </button>
  <button role="tab" class="tab-btn" aria-selected="false" aria-controls="patient-statistics" data-tab="patient-statistics">
    Patient Statistics
  </button>
  </div>
    
    
    
     <div id="patient" class="tab-content active" role="tabpanel" aria-labelledby="patient-tab">
       
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
			<input type="checkbox" name="Student"<?php if($onlyStudent) echo 'checked'; ?>> Student only 
		</label>
       
       <label>
			<input type="checkbox" name="Staff"<?php if($onlyStaff) echo 'checked'; ?>> Staff only 
		</label>
		
	<div>
		<button id="filterBtn" type="submit">Filter</button></div>
	</form>   
       
    <table class="staff-list">
  	  <tr>
        <th>Photo</th>
        <th>Patient ID</th>
        <th>Surname</th>
        <th>First Name</th>
        <th>Middle Name</th>
        <th>Phone</th>
        <th>Category</th>
        <th>Status</th>
  	    <th>View</th>
  	    <th>Edit</th>
        <th>Visit History</th>
        
  	  </tr>
      
      
      <?php while($patient = $all_patients->fetch_assoc()) { ?>
        
        <tr>
          <td> <?php if (!empty($patient['profile_image'])) { ?>
            <img src="<?php echo url_wrap('modules/patients/images/patient_pictures/' . v_wrap(ru_wrap($patient['profile_image']))); ?>" alt="Patient profile photo" class="patient-profile-thumbnail"
                <?php if (($patient['status']) == 'Active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($patient['status']) == 'Inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>
                 <?php if (($patient['status']) == 'Archived') { ?>
                 style=" border: 1.5px solid #63e3ff;"
                 <?php } ?>
                 <?php if (($patient['status']) == 'Deceased') { ?>
                 style=" border: 1px solid;"
                 <?php } ?>>
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage));?>" alt="No Patient photo uploaded" class="patient-profile-thumbnail"
                 <?php if (($patient['status']) == 'Active') { ?>
                 style=" border: 1.5px solid #0F4E74;"
                 <?php } ?>
                 <?php if (($patient['status']) == 'Inactive') { ?>
                 style=" border: 1.5px solid red;"
                 <?php } ?>
                 <?php if (($patient['status']) == 'Archived') { ?>
                 style=" border: 1.5px solid #63e3ff;"
                 <?php } ?>
                 <?php if (($patient['status']) == 'Deceased') { ?>
                 style=" border: 1px solid;"
                 <?php } ?>>
          <?php } ?>
          </td>
          <td><?php echo v_wrap($patient['patient_id']); ?></td>
          <td><?php echo v_wrap($patient['surname']); ?></td>
          <td><?php echo v_wrap($patient['first_name']); ?></td>
          <td><?php echo v_wrap($patient['middle_name']); ?></td>
          <td><?php echo v_wrap($patient['phone']); ?></td>
          <td><?php echo v_wrap($patient['patient_category']); ?></td>
          <td><?php echo v_wrap($patient['status']); ?></td>
          <td><?php if (hasPermission('view_patient')) { ?><a class="view-patient" href="<?php echo url_wrap('/modules/patients/view.php?id=' . v_wrap(u_wrap($patient['id']))); ?>"><i class="bi bi-eye"></i></a><?php } ?></td>
          <td><?php if (hasPermission('edit_patient')) { ?><a class="edit-patient" href="<?php echo url_wrap('/modules/patients/edit.php?id=' . v_wrap(u_wrap($patient['id']))); ?>"><i class="bi bi-pencil-square"></i></a><?php } ?></td>
          <td><?php if (hasPermission('view_medical_history')) { ?><a class="edit-patient" href="<?php echo url_wrap('/modules/patients/history.php?id=' . v_wrap(u_wrap($patient['id']))); ?>"><i class="bi bi-clock-history"></i></a><?php } ?></td>
        </tr>
      <?php } // close foreach statement ?>
       </table>
       
       
    </div>
    
    <div style="margin-top:20px; text-align: center;">

<?php
$queryString = http_build_query([
  'search' => $search,
  'start' => $startDate,
  'end' => $endDate,
  'Student' => $onlyStudent ? 1 : null,
  'Staff' => $onlyStaff ? 1 : null
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

    
  <div id="patient-statistics" class="tab-content" role="tabpanel" aria-labelledby="patient-statistics-tab" hidden>
    <h3>test part</h3>
    </div>
      
 </main>
  
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
