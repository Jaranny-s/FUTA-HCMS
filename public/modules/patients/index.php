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
  <button data-modal-target="registerPatientModal" class="add-staff" style="margin-right: 20px; background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:600;"> Register Patients </button>
  <a id="link_layout" class="add-staff" href="<?php echo url_wrap('/modules/patients/import.php'); ?>"> Import Patients </a>
</div>

<!-- Patient Registration Multi-Step Modal -->
<div id="registerPatientModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px; max-height: 85vh; overflow-y: auto;">
        <button class="modal-close" data-modal-close>&times;</button>
        <h3 class="modal-title"><i class="bi bi-person-plus"></i> Patient Registration</h3>
        
        <form action="<?php echo url_wrap('/modules/patients/new_patient_processor.php'); ?>" method="post" enctype="multipart/form-data" id="patientRegistrationForm">
            
            <!-- Step 1: Basic Information -->
            <div class="modal-step" data-step="1">
                <h4 style="margin-top:0; color:#444;">Step 1: Basic Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Patient Category *</label>
                    <select name="patient_category" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Student">Student</option>
                        <option value="Staff">Staff</option>
                        <option value="Dependant">Dependant</option>
                        <option value="External">External</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Surname *</label>
                        <input type="text" name="surname" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div style="flex:1;">
                        <label>First Name *</label>
                        <input type="text" name="first_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Sex *</label>
                        <select name="sex" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Date of Birth *</label>
                        <input type="date" name="date_of_birth" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Profile Image (Optional)</label>
                    <input type="file" name="profile_image" accept="image/*" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="text-align:right;">
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 2: Contact Information -->
            <div class="modal-step" data-step="2" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 2: Contact Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Email Address</label>
                    <input type="email" name="email" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Residential Address *</label>
                    <textarea name="address" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; height:60px;"></textarea>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 3: Emergency Information -->
            <div class="modal-step" data-step="3" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 3: Emergency Contact</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Emergency Contact Name *</label>
                    <input type="text" name="emergency_contact_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Emergency Contact Phone *</label>
                    <input type="text" name="emergency_contact_phone" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Relationship *</label>
                    <input type="text" name="emergency_contact_relationship" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 4: Medical Information -->
            <div class="modal-step" data-step="4" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 4: Medical Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Blood Group</label>
                        <select name="blood_group" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">Unknown</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Genotype</label>
                        <select name="genotype" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">Unknown</option>
                            <option value="AA">AA</option>
                            <option value="AS">AS</option>
                            <option value="SS">SS</option>
                            <option value="AC">AC</option>
                            <option value="SC">SC</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Allergies</label>
                    <textarea name="allergies" placeholder="None" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; height:50px;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Chronic Conditions</label>
                    <textarea name="chronic_conditions" placeholder="None" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; height:50px;"></textarea>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 5: University Information -->
            <div class="modal-step" data-step="5" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 5: FUTA Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Matric / Staff Number</label>
                    <input type="text" name="matric_number" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Faculty / School</label>
                    <input type="text" name="faculty" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Department</label>
                    <input type="text" name="department" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="submit" class="btn" style="background:#28a745; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Complete Registration</button>
                </div>
            </div>
            
        </form>
    </div>
</div>

<div id="editPatientModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 600px; max-height: 85vh; overflow-y: auto;">
        <button class="modal-close" data-modal-close>&times;</button>
        <h3 class="modal-title"><i class="bi bi-person-plus"></i> Patient Registration</h3>
        
        <form id="editPatientForm" action="<?php echo url_wrap('/modules/patients/edit_patient_processor.php'); ?>" method="post" enctype="multipart/form-data" id="patientRegistrationForm">
            
            <!-- Step 1: Basic Information -->
            <div class="modal-step" data-step="1">
                <h4 style="margin-top:0; color:#444;">Step 1: Basic Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Patient Category *</label>
                    <select name="patient_category" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Student">Student</option>
                        <option value="Staff">Staff</option>
                        <option value="Dependant">Dependant</option>
                        <option value="External">External</option>
                    </select>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Surname *</label>
                        <input type="text" name="surname" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div style="flex:1;">
                        <label>First Name *</label>
                        <input type="text" name="first_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Sex *</label>
                        <select name="sex" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Date of Birth *</label>
                        <input type="date" name="date_of_birth" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Profile Image (Optional)</label>
                    <input type="file" name="profile_image" accept="image/*" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="text-align:right;">
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 2: Contact Information -->
            <div class="modal-step" data-step="2" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 2: Contact Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Phone Number *</label>
                    <input type="text" name="phone" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Email Address</label>
                    <input type="email" name="email" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Residential Address *</label>
                    <textarea name="address" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; height:60px;"></textarea>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 3: Emergency Information -->
            <div class="modal-step" data-step="3" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 3: Emergency Contact</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Emergency Contact Name *</label>
                    <input type="text" name="emergency_contact_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Emergency Contact Phone *</label>
                    <input type="text" name="emergency_contact_phone" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Relationship *</label>
                    <input type="text" name="emergency_contact_relationship" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 4: Medical Information -->
            <div class="modal-step" data-step="4" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 4: Medical Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px; display: flex; gap: 10px;">
                    <div style="flex:1;">
                        <label>Blood Group</label>
                        <select name="blood_group" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">Unknown</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                        </select>
                    </div>
                    <div style="flex:1;">
                        <label>Genotype</label>
                        <select name="genotype" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">Unknown</option>
                            <option value="AA">AA</option>
                            <option value="AS">AS</option>
                            <option value="SS">SS</option>
                            <option value="AC">AC</option>
                            <option value="SC">SC</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Allergies</label>
                    <textarea name="allergies" placeholder="None" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; height:50px;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Chronic Conditions</label>
                    <textarea name="chronic_conditions" placeholder="None" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; height:50px;"></textarea>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 5: University Information -->
            <div class="modal-step" data-step="5" style="display:none;">
                <h4 style="margin-top:0; color:#444;">Step 5: FUTA Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Matric / Staff Number</label>
                    <input type="text" name="matric_number" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Faculty / School</label>
                    <input type="text" name="faculty" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Department</label>
                    <input type="text" name="department" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="submit" class="btn" style="background:#28a745; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Complete Update</button>
                </div>
            </div>
            
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const nextBtns = document.querySelectorAll('.next-step');
    const prevBtns = document.querySelectorAll('.prev-step');
    const steps = document.querySelectorAll('.modal-step');
    
    nextBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const currentStep = this.closest('.modal-step');
            const requiredFields = currentStep.querySelectorAll('[required]');
            let valid = true;
            requiredFields.forEach(field => {
                if (!field.value) {
                    field.style.borderColor = 'red';
                    valid = false;
                } else {
                    field.style.borderColor = '#ddd';
                }
            });
            if (!valid) {
                alert("Please fill out all required fields.");
                return;
            }
            
            const nextStepNum = parseInt(currentStep.getAttribute('data-step')) + 1;
            const nextStep = document.querySelector(`.modal-step[data-step="${nextStepNum}"]`);
            if (nextStep) {
                currentStep.style.display = 'none';
                nextStep.style.display = 'block';
            }
        });
    });
    
    prevBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const currentStep = this.closest('.modal-step');
            const prevStepNum = parseInt(currentStep.getAttribute('data-step')) - 1;
            const prevStep = document.querySelector(`.modal-step[data-step="${prevStepNum}"]`);
            if (prevStep) {
                currentStep.style.display = 'none';
                prevStep.style.display = 'block';
            }
        });
    });
});
</script>

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
