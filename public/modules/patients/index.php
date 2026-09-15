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

$tab = $_GET['tab'] ?? 'active';
$isArchivedTab = ($tab === 'archived');
$statusFilter = $isArchivedTab ? 'Archived' : null;

$totalPages = total_page_count_for_patients($limit, $statusFilter);

$search = $_GET['search'] ?? null;
$startDate = $_GET['start'] ?? null;
$endDate = $_GET['end'] ?? null;
$onlyStudent = isset($_GET['Student']);
$onlyStaff = isset($_GET['Staff']);

$all_patients = find_all_patients($search, $startDate, $endDate, $onlyStudent, $onlyStaff, $limit, $offset, $statusFilter);

require_once(PRIVATE_PATH . '/data/futa_departments.php');

include(SHARED_PATH . '/header.php'); ?>

<div id="content">
  
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  
  <main class="main-content">
    
  <i class="bi bi-arrow-left"></i> Back
</a>
    
  <div class="top">
    <p class="top-head"><?php echo $isArchivedTab ? 'Archived Patient Records' : 'Patient Records'; ?></p> 
    <p class="top-description"><?php echo $isArchivedTab ? 'Cold-storage archive for patients inactive for 5+ years or permanently archived. Clinical histories are preserved.' : 'List of all active and registered patients.'; ?></p>
  </div>
   
  
  <div id="ajax-message" class="ajax-message" hidden></div>
  <div><?php echo display_session_message(); ?></div>
  <?php if (isset($_SESSION['error'])) { echo "<div style='color:#d93025; background:#fce8e6; padding:10px; border-radius:5px; margin-bottom:15px; border:1px solid #d93025;'>" . v_wrap($_SESSION['error']) . "</div>"; unset($_SESSION['error']); } ?>


<?php if (hasPermission('view_patient')) { ?>
    
<?php 
require_once(PRIVATE_PATH . '/data/countries.php');
require_once(PRIVATE_PATH . '/data/nigerian_states.php');
require_once(PRIVATE_PATH . '/data/nigerian_lgas.php');
?>

<?php 
$canManagePatients = in_array($_SESSION['staff_role'] ?? '', ['receptionist', 'admin', 'super_admin']);
?>
<div class="above-tabs">
  <?php if ($canManagePatients) { ?>
  <button data-modal-target="registerPatientModal" class="add-staff" style="margin-right: 20px; background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:600;"> Register Patients </button>
  <?php } ?>
  <?php if (hasPermission('import_patients')) { ?>
  <a id="link_layout" class="add-staff" href="<?php echo url_wrap('/modules/patients/import.php'); ?>"> Import Patients </a>
  <?php } ?>
</div>

<!-- ========================================== -->
<!-- 1. PATIENT REGISTRATION MULTI-STEP MODAL   -->
<!-- ========================================== -->
<div id="registerPatientModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 650px; max-height: 90vh; overflow-y: auto;">
        <button class="modal-close" data-modal-close>&times;</button>
        <h3 class="modal-title"><i class="bi bi-person-plus"></i> Patient Registration</h3>
        
        <form action="<?php echo url_wrap('/modules/patients/new_patient_processor.php'); ?>" method="post" enctype="multipart/form-data" id="patientRegistrationForm">
            
            <!-- Step 1: Basic Information -->
            <div class="modal-step" data-step="1">
                <h4 style="margin-top:0; color:#0F4E74;">Step 1: Basic Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <!-- Duplicate / Former Patient Detection Alert -->
                <div id="regDuplicatePatientAlert" style="display:none; background:#fff8e1; border:1px solid #ffe082; padding:14px 16px; border-radius:8px; margin-bottom:18px; box-shadow:0 2px 6px rgba(0,0,0,0.05);">
                    <div style="font-weight:600; color:#b78103; display:flex; align-items:center; gap:8px; margin-bottom:6px; font-size:0.95rem;">
                        <i class="bi bi-person-exclamation" style="font-size:1.25rem;"></i> Matching Historical Patient Record Found
                    </div>
                    <div id="regDuplicateDetails" style="font-size:0.88rem; color:#444; line-height:1.5;"></div>
                    <div style="margin-top:12px; display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" id="btnTransitionExisting" class="btn" style="background:#0F4E74; color:white; border:none; padding:8px 16px; border-radius:5px; font-size:0.85rem; cursor:pointer; font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                            <i class="bi bi-arrow-repeat"></i> Transition & Reactivate Existing Record
                        </button>
                        <button type="button" id="btnDismissDuplicate" class="btn" style="background:#e0e0e0; color:#333; border:none; padding:8px 14px; border-radius:5px; font-size:0.85rem; cursor:pointer;">
                            Dismiss & Register as New
                        </button>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Patient Category *</label>
                    <select name="patient_category" class="category-select" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Student">Student</option>
                        <option value="Staff">Staff</option>
                        <option value="Dependant">Dependant</option>
                        <option value="External">External</option>
                    </select>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-weight:600;">Surname *</label>
                        <input type="text" name="surname" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">First Name *</label>
                        <input type="text" name="first_name" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">Gender / Sex *</label>
                        <select name="gender" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-weight:600;">Date of Birth *</label>
                        <input type="date" name="date_of_birth" class="dob-input" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label>Age (Years)</label>
                        <input type="number" name="age" class="age-input" readonly placeholder="Auto-calculated" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px; background:#f5f5f5;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Marital Status</label>
                    <select name="marital_status" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Nationality / Country *</label>
                    <select name="nationality" class="country-select" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Nigeria" selected>Nigeria</option>
                        <?php foreach($countries as $c) { if($c === 'Nigeria') continue; ?>
                            <option value="<?php echo v_wrap($c); ?>"><?php echo v_wrap($c); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="state-lga-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="state-group">
                        <label style="font-weight:600;">State of Origin</label>
                        <select name="state_of_origin" class="state-select" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">-- Select State --</option>
                            <?php foreach($states as $st) { ?>
                                <option value="<?php echo v_wrap($st); ?>"><?php echo v_wrap($st); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="lga-group">
                        <label style="font-weight:600;">LGA of Origin</label>
                        <select name="lga" class="lga-select" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">-- Select LGA --</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Profile Image (Optional)</label>
                    <input type="file" name="profile_image" accept="image/*" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="text-align:right;">
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 22px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 2: Contact Information -->
            <div class="modal-step" data-step="2" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 2: Contact Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-weight:600;">Primary Phone Number *</label>
                        <input type="text" name="phone" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label>Alternate Phone Number</label>
                        <input type="text" name="alternate_phone" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Email Address</label>
                    <input type="email" name="email" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Residential Address *</label>
                    <textarea name="address" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px; height:55px;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div>
                        <label>City / Town</label>
                        <input type="text" name="city" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label>Residential State</label>
                        <input type="text" name="residential_state" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 3: Emergency Information & Next of Kin -->
            <div class="modal-step" data-step="3" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 3: Emergency Contact & Next of Kin</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <h5 style="margin:0 0 10px 0; color:#0F4E74; font-size:0.95rem;"><i class="bi bi-telephone-fill"></i> Primary Emergency Contact</h5>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Emergency Contact Name *</label>
                    <input type="text" name="emergency_contact_name" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div>
                        <label style="font-weight:600;">Relationship to Patient *</label>
                        <input type="text" name="emergency_contact_relationship" placeholder="e.g. Parent, Spouse, Sibling" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">Emergency Contact Phone *</label>
                        <input type="text" name="emergency_contact_phone" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin:20px 0 10px 0; padding-top:10px; border-top:1px dashed #ddd;">
                    <h5 style="margin:0; color:#0F4E74; font-size:0.95rem;"><i class="bi bi-people-fill"></i> Next of Kin Details</h5>
                    <button type="button" class="btn-copy-emergency" style="background:#e8f4fd; color:#0F4E74; border:1px solid #0F4E74; padding:3px 8px; border-radius:4px; font-size:0.8rem; cursor:pointer;">Same as Emergency Contact</button>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Next of Kin Name</label>
                    <input type="text" name="next_of_kin_name" placeholder="Full name of next of kin" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div>
                        <label style="font-weight:600;">Relationship</label>
                        <input type="text" name="next_of_kin_relationship" placeholder="e.g. Father, Mother, Spouse" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">Next of Kin Phone</label>
                        <input type="text" name="next_of_kin_phone" placeholder="Phone number" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 4: Medical Information -->
            <div class="modal-step" data-step="4" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 4: Medical Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label>Blood Group</label>
                        <select name="blood_group" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
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
                    <div>
                        <label>Genotype</label>
                        <select name="genotype" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">Unknown</option>
                            <option value="AA">AA</option>
                            <option value="AS">AS</option>
                            <option value="SS">SS</option>
                            <option value="AC">AC</option>
                            <option value="SC">SC</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Allergies</label>
                    <textarea name="allergies" placeholder="e.g. Penicillin, Peanuts (or None)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px; height:45px;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Chronic Conditions</label>
                    <textarea name="chronic_conditions" placeholder="e.g. Asthma, Hypertension (or None)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px; height:45px;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Disabilities / Special Needs</label>
                    <input type="text" name="disabilities" placeholder="None" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 5: University / Employment Information -->
            <div class="modal-step" data-step="5" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 5: University / Employment Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <!-- Student Fields -->
                <div class="category-block student-block">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:600;">Matriculation Number *</label>
                        <input type="text" name="matric_number" placeholder="e.g. CSC/2021/1001" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <div>
                            <label>School / Faculty</label>
                            <input type="text" name="faculty" placeholder="Auto-fills from department" readonly style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px; background:#f9fbfd;">
                        </div>
                        <div>
                            <label>Department</label>
                            <select name="department" class="futa-dept-select" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                                <?php echo render_futa_department_options(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Level</label>
                        <select name="level" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="100">100 Level</option>
                            <option value="200">200 Level</option>
                            <option value="300">300 Level</option>
                            <option value="400">400 Level</option>
                            <option value="500">500 Level</option>
                            <option value="600">600 Level (MBBS)</option>
                            <option value="Postgraduate">Postgraduate</option>
                        </select>
                    </div>
                </div>

                <!-- Staff Fields -->
                <div class="category-block staff-block" style="display:none;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:600;">FUTA Staff ID / Number *</label>
                        <input type="text" name="staff_number" placeholder="e.g. FUTA/19980045/26/0004" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        <small style="color:#666; font-size:0.8rem; display:block; margin-top:3px;">Format: FUTA/Employment Number/Year/Hospital Number (e.g. FUTA/19980045/26/0004)</small>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div>
                            <label>Staff Department / Unit</label>
                            <input type="text" name="staff_department" placeholder="e.g. Registry / Works" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                        <div>
                            <label>Position / Designation</label>
                            <input type="text" name="staff_position" placeholder="e.g. Lecturer I / Senior Admin" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                    </div>
                </div>

                <!-- Dependant Fields -->
                <div class="category-block dependant-block" style="display:none;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:600;">Relationship to Principal Staff *</label>
                        <select name="relationship_to_principal" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Spouse">Spouse</option>
                            <option value="Child">Child</option>
                            <option value="Ward">Ward</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px; position:relative;">
                        <label style="font-weight:600;">Principal Staff Member (Search by Name, Staff ID, or Department) *</label>
                        <input type="hidden" name="principal_patient_id" class="principal-patient-id-input">
                        <div class="principal-search-wrapper" style="position:relative;">
                            <input type="text" class="principal-search-input" placeholder="Type staff name, staff ID or patient ID (min 2 chars)..." autocomplete="off" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <div class="principal-results-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:999; background:white; border:1px solid #ccc; border-radius:0 0 6px 6px; max-height:220px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
                        </div>
                        <div class="selected-principal-card" style="display:none; margin-top:10px; padding:10px 14px; background:#e8f4fd; border:1.5px solid #0F4E74; border-radius:6px; align-items:center; justify-content:space-between;">
                            <div class="principal-info" style="display:flex; align-items:center; gap:12px;">
                                <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>" class="principal-avatar" alt="Avatar" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #0F4E74;">
                                <div>
                                    <div class="principal-name" style="font-weight:700; color:#0F4E74; font-size:0.95rem;"></div>
                                    <div class="principal-meta" style="font-size:0.82rem; color:#555;"></div>
                                </div>
                            </div>
                            <button type="button" class="btn-remove-principal" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; font-size:0.8rem; cursor:pointer;"><i class="bi bi-x-circle"></i> Change</button>
                        </div>
                    </div>
                </div>

                <!-- External Fields -->
                <div class="category-block external-block" style="display:none;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Occupation</label>
                        <input type="text" name="occupation" placeholder="e.g. Businessman, Consultant" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Employer / Organization Address</label>
                        <input type="text" name="employer" placeholder="Organization name and city" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next (Review) &rarr;</button>
                </div>
            </div>

            <!-- Step 6: Review Section -->
            <div class="modal-step" data-step="6" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 6: Review Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="review-card" style="background:#f9fbfd; border:1px solid #e1e8ed; border-radius:8px; padding:15px; margin-bottom:15px; max-height:260px; overflow-y:auto; font-size:0.9rem; line-height:1.6;">
                    <p style="color:#888;">Please review entered patient information before completing registration.</p>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="submit" class="btn" style="background:#28a745; color:white; border:none; padding:10px 25px; border-radius:5px; cursor:pointer; font-weight:600;"><i class="bi bi-check-circle"></i> Register Patient</button>
                </div>
            </div>
            
        </form>
    </div>
</div>

<!-- ========================================== -->
<!-- 2. PATIENT EDIT MULTI-STEP MODAL           -->
<!-- ========================================== -->
<div id="editPatientModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 650px; max-height: 90vh; overflow-y: auto;">
        <button class="modal-close" data-modal-close>&times;</button>
        <h3 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Patient Record</h3>
        
        <form id="editPatientForm" action="<?php echo url_wrap('/modules/patients/edit_patient_processor.php'); ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id" id="edit_patient_id">
            
            <!-- Step 1: Basic Information -->
            <div class="modal-step" data-step="1">
                <h4 style="margin-top:0; color:#0F4E74;">Step 1: Basic Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-weight:600;">Patient Category *</label>
                        <select name="patient_category" class="category-select" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Student">Student</option>
                            <option value="Staff">Staff</option>
                            <option value="Dependant">Dependant</option>
                            <option value="External">External</option>
                        </select>
                    </div>
                    <?php if ($canManagePatients) { ?>
                    <div>
                        <label style="font-weight:600;">Patient Status *</label>
                        <select name="status" class="status-select" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="Archived">Archived</option>
                            <option value="Deceased">Deceased</option>
                        </select>
                    </div>
                    <?php } ?>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-weight:600;">Surname *</label>
                        <input type="text" name="surname" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">First Name *</label>
                        <input type="text" name="first_name" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label>Middle Name</label>
                        <input type="text" name="middle_name" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">Gender / Sex *</label>
                        <select name="gender" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-weight:600;">Date of Birth *</label>
                        <input type="date" name="date_of_birth" class="dob-input" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label>Age (Years)</label>
                        <input type="number" name="age" class="age-input" readonly placeholder="Auto-calculated" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px; background:#f5f5f5;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Marital Status</label>
                    <select name="marital_status" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Single">Single</option>
                        <option value="Married">Married</option>
                        <option value="Divorced">Divorced</option>
                        <option value="Widowed">Widowed</option>
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Nationality / Country *</label>
                    <select name="nationality" class="country-select" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        <option value="Nigeria" selected>Nigeria</option>
                        <?php foreach($countries as $c) { if($c === 'Nigeria') continue; ?>
                            <option value="<?php echo v_wrap($c); ?>"><?php echo v_wrap($c); ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="state-lga-container" style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div class="state-group">
                        <label style="font-weight:600;">State of Origin</label>
                        <select name="state_of_origin" class="state-select" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">-- Select State --</option>
                            <?php foreach($states as $st) { ?>
                                <option value="<?php echo v_wrap($st); ?>"><?php echo v_wrap($st); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="lga-group">
                        <label style="font-weight:600;">LGA of Origin</label>
                        <select name="lga" class="lga-select" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">-- Select LGA --</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Update Profile Image (Optional)</label>
                    <input type="file" name="profile_image" accept="image/*" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="text-align:right;">
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 22px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 2: Contact Information -->
            <div class="modal-step" data-step="2" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 2: Contact Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label style="font-weight:600;">Primary Phone Number *</label>
                        <input type="text" name="phone" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label>Alternate Phone Number</label>
                        <input type="text" name="alternate_phone" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Email Address</label>
                    <input type="email" name="email" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>

                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Residential Address *</label>
                    <textarea name="address" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px; height:55px;"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div>
                        <label>City / Town</label>
                        <input type="text" name="city" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label>Residential State</label>
                        <input type="text" name="residential_state" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 3: Emergency Information & Next of Kin -->
            <div class="modal-step" data-step="3" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 3: Emergency Contact & Next of Kin</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <h5 style="margin:0 0 10px 0; color:#0F4E74; font-size:0.95rem;"><i class="bi bi-telephone-fill"></i> Primary Emergency Contact</h5>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Emergency Contact Name *</label>
                    <input type="text" name="emergency_contact_name" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div>
                        <label style="font-weight:600;">Relationship to Patient *</label>
                        <input type="text" name="emergency_contact_relationship" placeholder="e.g. Parent, Spouse, Sibling" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">Emergency Contact Phone *</label>
                        <input type="text" name="emergency_contact_phone" required style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>

                <div style="display:flex; justify-content:space-between; align-items:center; margin:20px 0 10px 0; padding-top:10px; border-top:1px dashed #ddd;">
                    <h5 style="margin:0; color:#0F4E74; font-size:0.95rem;"><i class="bi bi-people-fill"></i> Next of Kin Details</h5>
                    <button type="button" class="btn-copy-emergency" style="background:#e8f4fd; color:#0F4E74; border:1px solid #0F4E74; padding:3px 8px; border-radius:4px; font-size:0.8rem; cursor:pointer;">Same as Emergency Contact</button>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label style="font-weight:600;">Next of Kin Name</label>
                    <input type="text" name="next_of_kin_name" placeholder="Full name of next of kin" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                    <div>
                        <label style="font-weight:600;">Relationship</label>
                        <input type="text" name="next_of_kin_relationship" placeholder="e.g. Father, Mother, Spouse" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div>
                        <label style="font-weight:600;">Next of Kin Phone</label>
                        <input type="text" name="next_of_kin_phone" placeholder="Phone number" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 4: Medical Information -->
            <div class="modal-step" data-step="4" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 4: Medical Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                    <div>
                        <label>Blood Group</label>
                        <select name="blood_group" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
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
                    <div>
                        <label>Genotype</label>
                        <select name="genotype" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="">Unknown</option>
                            <option value="AA">AA</option>
                            <option value="AS">AS</option>
                            <option value="SS">SS</option>
                            <option value="AC">AC</option>
                            <option value="SC">SC</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Allergies</label>
                    <textarea name="allergies" placeholder="e.g. Penicillin (or None)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px; height:45px;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 12px;">
                    <label>Chronic Conditions</label>
                    <textarea name="chronic_conditions" placeholder="e.g. Hypertension (or None)" style="width:100%; padding:8px; border:1px solid #ddd; border-radius:5px; height:45px;"></textarea>
                </div>
                <div class="form-group" style="margin-bottom: 15px;">
                    <label>Disabilities / Special Needs</label>
                    <input type="text" name="disabilities" placeholder="None" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next &rarr;</button>
                </div>
            </div>
            
            <!-- Step 5: University / Employment Information -->
            <div class="modal-step" data-step="5" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 5: University / Employment Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <!-- Student Fields -->
                <div class="category-block student-block">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:600;">Matriculation Number *</label>
                        <input type="text" name="matric_number" placeholder="e.g. CSC/2021/1001" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                        <div>
                            <label>School / Faculty</label>
                            <input type="text" name="faculty" readonly style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px; background:#f9fbfd;">
                        </div>
                        <div>
                            <label>Department</label>
                            <select name="department" class="futa-dept-select" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                                <?php echo render_futa_department_options(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Level</label>
                        <select name="level" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="100">100 Level</option>
                            <option value="200">200 Level</option>
                            <option value="300">300 Level</option>
                            <option value="400">400 Level</option>
                            <option value="500">500 Level</option>
                            <option value="600">600 Level (MBBS)</option>
                            <option value="Postgraduate">Postgraduate</option>
                        </select>
                    </div>
                </div>

                <!-- Staff Fields -->
                <div class="category-block staff-block" style="display:none;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:600;">FUTA Staff ID / Number *</label>
                        <input type="text" name="staff_number" placeholder="e.g. FUTA/19980045/26/0004" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        <small style="color:#666; font-size:0.8rem; display:block; margin-top:3px;">Format: FUTA/Employment Number/Year/Hospital Number (e.g. FUTA/19980045/26/0004)</small>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px;">
                        <div>
                            <label>Staff Department / Unit</label>
                            <input type="text" name="staff_department" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                        <div>
                            <label>Position / Designation</label>
                            <input type="text" name="staff_position" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                    </div>
                </div>

                <!-- Dependant Fields -->
                <div class="category-block dependant-block" style="display:none;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label style="font-weight:600;">Relationship to Principal Staff *</label>
                        <select name="relationship_to_principal" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <option value="Spouse">Spouse</option>
                            <option value="Child">Child</option>
                            <option value="Ward">Ward</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 15px; position:relative;">
                        <label style="font-weight:600;">Principal Staff Member (Search by Name, Staff ID, or Department) *</label>
                        <input type="hidden" name="principal_patient_id" class="principal-patient-id-input">
                        <div class="principal-search-wrapper" style="position:relative;">
                            <input type="text" class="principal-search-input" placeholder="Type staff name, staff ID or patient ID (min 2 chars)..." autocomplete="off" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                            <div class="principal-results-dropdown" style="display:none; position:absolute; top:100%; left:0; right:0; z-index:999; background:white; border:1px solid #ccc; border-radius:0 0 6px 6px; max-height:220px; overflow-y:auto; box-shadow:0 4px 12px rgba(0,0,0,0.15);"></div>
                        </div>
                        <div class="selected-principal-card" style="display:none; margin-top:10px; padding:10px 14px; background:#e8f4fd; border:1.5px solid #0F4E74; border-radius:6px; align-items:center; justify-content:space-between;">
                            <div class="principal-info" style="display:flex; align-items:center; gap:12px;">
                                <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>" class="principal-avatar" alt="Avatar" style="width:42px; height:42px; border-radius:50%; object-fit:cover; border:2px solid #0F4E74;">
                                <div>
                                    <div class="principal-name" style="font-weight:700; color:#0F4E74; font-size:0.95rem;"></div>
                                    <div class="principal-meta" style="font-size:0.82rem; color:#555;"></div>
                                </div>
                            </div>
                            <button type="button" class="btn-remove-principal" style="background:#dc3545; color:white; border:none; padding:5px 10px; border-radius:4px; font-size:0.8rem; cursor:pointer;"><i class="bi bi-x-circle"></i> Change</button>
                        </div>
                    </div>
                </div>

                <!-- External Fields -->
                <div class="category-block external-block" style="display:none;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label>Occupation</label>
                        <input type="text" name="occupation" placeholder="e.g. Businessman" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label>Employer / Organization Address</label>
                        <input type="text" name="employer" style="width:100%; padding:9px; border:1px solid #ddd; border-radius:5px;">
                    </div>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="button" class="btn next-step" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">Next (Review) &rarr;</button>
                </div>
            </div>

            <!-- Step 6: Review Section -->
            <div class="modal-step" data-step="6" style="display:none;">
                <h4 style="margin-top:0; color:#0F4E74;">Step 6: Review Information</h4>
                <hr style="margin-bottom:15px; border:0; border-top:1px solid #eee;" />
                
                <div class="review-card" style="background:#f9fbfd; border:1px solid #e1e8ed; border-radius:8px; padding:15px; margin-bottom:15px; max-height:260px; overflow-y:auto; font-size:0.9rem; line-height:1.6;">
                    <p style="color:#888;">Reviewing changes...</p>
                </div>
                
                <div style="display:flex; justify-content:space-between;">
                    <button type="button" class="btn prev-step" style="background:#ccc; color:#333; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">&larr; Prev</button>
                    <button type="submit" class="btn" style="background:#28a745; color:white; border:none; padding:10px 25px; border-radius:5px; cursor:pointer; font-weight:600;"><i class="bi bi-check-circle"></i> Save Changes</button>
                </div>
            </div>
            
        </form>
    </div>
</div>

<script>
const nigeriaLGAs = <?php echo json_encode($lgas); ?>;

document.addEventListener("DOMContentLoaded", function() {

    // 1. Unified Wizard Step Navigator & Validator
    function setupWizard(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const form = modal.querySelector('form');
        const nextBtns = modal.querySelectorAll('.next-step');
        const prevBtns = modal.querySelectorAll('.prev-step');
        const categorySelect = form.querySelector('.category-select');
        const countrySelect = form.querySelector('.country-select');
        const stateSelect = form.querySelector('.state-select');
        const lgaSelect = form.querySelector('.lga-select');
        const stateGroup = form.querySelector('.state-group');
        const lgaGroup = form.querySelector('.lga-group');
        const dobInput = form.querySelector('.dob-input');
        const ageInput = form.querySelector('.age-input');

        // Age calculation
        if (dobInput && ageInput) {
            function calcAge() {
                if (!dobInput.value) { ageInput.value = ''; return; }
                const dob = new Date(dobInput.value);
                const today = new Date();
                let age = today.getFullYear() - dob.getFullYear();
                const m = today.getMonth() - dob.getMonth();
                if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                    age--;
                }
                ageInput.value = age >= 0 ? age : 0;
            }
            dobInput.addEventListener('change', calcAge);
            dobInput.addEventListener('input', calcAge);
        }

        // Country -> State -> LGA Toggle
        function updateNationality(selectedState = '', selectedLga = '') {
            if (!countrySelect) return;
            const isNigeria = countrySelect.value === 'Nigeria';
            if (stateGroup) stateGroup.style.display = isNigeria ? 'block' : 'none';
            if (lgaGroup) lgaGroup.style.display = isNigeria ? 'block' : 'none';

            if (isNigeria && stateSelect) {
                if (selectedState) stateSelect.value = selectedState;
                populateLGAs(stateSelect.value, selectedLga);
            } else if (stateSelect && lgaSelect) {
                stateSelect.value = '';
                lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
            }
        }

        function populateLGAs(stateName, selectLga = '') {
            if (!lgaSelect) return;
            lgaSelect.innerHTML = '<option value="">-- Select LGA --</option>';
            if (stateName && nigeriaLGAs[stateName]) {
                nigeriaLGAs[stateName].forEach(function(l) {
                    const opt = document.createElement('option');
                    opt.value = l;
                    opt.textContent = l;
                    if (l === selectLga) opt.selected = true;
                    lgaSelect.appendChild(opt);
                });
            }
        }

        if (countrySelect) {
            countrySelect.addEventListener('change', function() { updateNationality(); });
        }
        if (stateSelect) {
            stateSelect.addEventListener('change', function() { populateLGAs(this.value); });
        }

        // Auto-fill Faculty from Department
        const deptSelect = form.querySelector('.futa-dept-select');
        const facultyInput = form.querySelector('[name="faculty"]');
        if (deptSelect && facultyInput) {
            deptSelect.addEventListener('change', function() {
                const opt = deptSelect.options[deptSelect.selectedIndex];
                const fac = opt ? (opt.getAttribute('data-faculty') || '') : '';
                if (fac) facultyInput.value = fac;
            });
        }

        // Dynamic Category Switching for Step 5
        function updateCategoryFields(cat) {
            const studentBlock = form.querySelector('.student-block');
            const staffBlock = form.querySelector('.staff-block');
            const dependantBlock = form.querySelector('.dependant-block');
            const externalBlock = form.querySelector('.external-block');

            if (studentBlock) studentBlock.style.display = 'none';
            if (staffBlock) staffBlock.style.display = 'none';
            if (dependantBlock) dependantBlock.style.display = 'none';
            if (externalBlock) externalBlock.style.display = 'none';

            if (cat === 'Student' && studentBlock) studentBlock.style.display = 'block';
            else if (cat === 'Staff' && staffBlock) staffBlock.style.display = 'block';
            else if (cat === 'Dependant' && dependantBlock) dependantBlock.style.display = 'block';
            else if (cat === 'External' && externalBlock) externalBlock.style.display = 'block';
        }

        if (categorySelect) {
            categorySelect.addEventListener('change', function() { updateCategoryFields(this.value); });
            updateCategoryFields(categorySelect.value);
        }

        // Next button handler
        nextBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const currentStep = this.closest('.modal-step');
                const requiredFields = currentStep.querySelectorAll('[required]');
                let valid = true;
                requiredFields.forEach(field => {
                    if (field.offsetParent !== null && !field.value.trim()) {
                        field.style.borderColor = '#dc3545';
                        valid = false;
                    } else {
                        field.style.borderColor = '#ddd';
                    }
                });

                if (!valid) {
                    alert("Please fill out all required fields before proceeding.");
                    return;
                }

                const nextStepNum = parseInt(currentStep.getAttribute('data-step')) + 1;
                const nextStep = modal.querySelector(`.modal-step[data-step="${nextStepNum}"]`);
                if (nextStep) {
                    if (nextStepNum === 6) {
                        buildReviewSummary(form, nextStep.querySelector('.review-card'));
                    }
                    currentStep.style.display = 'none';
                    nextStep.style.display = 'block';
                }
            });
        });

        // Prev button handler
        prevBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const currentStep = this.closest('.modal-step');
                const prevStepNum = parseInt(currentStep.getAttribute('data-step')) - 1;
                const prevStep = modal.querySelector(`.modal-step[data-step="${prevStepNum}"]`);
                if (prevStep) {
                    currentStep.style.display = 'none';
                    prevStep.style.display = 'block';
                }
            });
        });

        // Quick copy emergency contact to Next of Kin
        modal.querySelectorAll('.btn-copy-emergency').forEach(copyBtn => {
            copyBtn.addEventListener('click', function() {
                const emName = form.querySelector('[name="emergency_contact_name"]')?.value || '';
                const emRel = form.querySelector('[name="emergency_contact_relationship"]')?.value || '';
                const emPhone = form.querySelector('[name="emergency_contact_phone"]')?.value || '';

                const nokName = form.querySelector('[name="next_of_kin_name"]');
                const nokRel = form.querySelector('[name="next_of_kin_relationship"]');
                const nokPhone = form.querySelector('[name="next_of_kin_phone"]');

                if (nokName) nokName.value = emName;
                if (nokRel) nokRel.value = emRel;
                if (nokPhone) nokPhone.value = emPhone;
            });
        });

        // Setup Principal Staff Autocomplete for Dependant Category
        function setupPrincipalAutocomplete(modalEl, isEdit) {
            const hiddenInput = modalEl.querySelector('.principal-patient-id-input');
            const searchInput = modalEl.querySelector('.principal-search-input');
            const dropdown = modalEl.querySelector('.principal-results-dropdown');
            const card = modalEl.querySelector('.selected-principal-card');
            const avatar = card ? card.querySelector('.principal-avatar') : null;
            const nameEl = card ? card.querySelector('.principal-name') : null;
            const metaEl = card ? card.querySelector('.principal-meta') : null;
            const removeBtn = card ? card.querySelector('.btn-remove-principal') : null;
            let searchTimeout = null;

            if (!hiddenInput || !searchInput || !dropdown || !card) return;

            function setPrincipal(p) {
                hiddenInput.value = p.id;
                if (nameEl) nameEl.textContent = `${p.surname} ${p.first_name} ${p.middle_name || ''}`;
                if (metaEl) metaEl.textContent = `Staff ID: ${p.staff_number || p.patient_id || 'N/A'} | Dept: ${p.department || 'N/A'}`;
                if (avatar) {
                    const imgSrc = p.profile_image 
                        ? `<?php echo url_wrap('/modules/patients/images/patient_pictures/'); ?>${encodeURIComponent(p.profile_image)}`
                        : `<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>`;
                    avatar.src = imgSrc;
                    avatar.onerror = function() {
                        this.onerror = null;
                        this.src = `<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>`;
                    };
                }
                card.style.display = 'flex';
                searchInput.style.display = 'none';
                dropdown.style.display = 'none';
            }

            function clearPrincipal() {
                hiddenInput.value = '';
                searchInput.value = '';
                searchInput.style.display = 'block';
                card.style.display = 'none';
                dropdown.style.display = 'none';
                dropdown.innerHTML = '';
            }

            if (removeBtn) {
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    clearPrincipal();
                });
            }

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                if (query.length < 2) {
                    dropdown.style.display = 'none';
                    dropdown.innerHTML = '';
                    return;
                }

                searchTimeout = setTimeout(() => {
                    const editId = isEdit ? (document.getElementById('edit_patient_id')?.value || 0) : 0;
                    const url = `<?php echo url_wrap('/modules/patients/search_principal.php'); ?>?q=${encodeURIComponent(query)}&current_id=${editId}`;
                    
                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            if (!data.success || !data.patients || data.patients.length === 0) {
                                dropdown.innerHTML = '<div style="padding:10px; color:#888; text-align:center;">No matching staff found</div>';
                                dropdown.style.display = 'block';
                                return;
                            }

                            let html = '';
                            data.patients.forEach(p => {
                                const img = p.profile_image 
                                    ? `<?php echo url_wrap('/modules/patients/images/patient_pictures/'); ?>${encodeURIComponent(p.profile_image)}`
                                    : `<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>`;
                                const pName = `${p.surname} ${p.first_name} ${p.middle_name || ''}`;
                                const pMeta = `ID: ${p.staff_number || p.patient_id} | ${p.department || 'N/A'}`;

                                html += `
                                    <div class="principal-item" data-json='${JSON.stringify(p).replace(/'/g, "&apos;")}' style="display:flex; align-items:center; gap:10px; padding:8px 12px; cursor:pointer; border-bottom:1px solid #f0f0f0;">
                                        <img src="${img}" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>';" style="width:36px; height:36px; border-radius:50%; object-fit:cover; border:1px solid #0F4E74;">
                                        <div>
                                            <div style="font-weight:600; color:#0F4E74; font-size:0.9rem;">${pName}</div>
                                            <div style="font-size:0.8rem; color:#666;">${pMeta}</div>
                                        </div>
                                    </div>
                                `;
                            });

                            dropdown.innerHTML = html;
                            dropdown.style.display = 'block';

                            dropdown.querySelectorAll('.principal-item').forEach(item => {
                                item.addEventListener('click', function() {
                                    const pData = JSON.parse(this.getAttribute('data-json'));
                                    setPrincipal(pData);
                                });
                            });
                        })
                        .catch(err => {
                            console.error('Error searching principal:', err);
                        });
                }, 300);
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!modalEl.contains(e.target)) {
                    dropdown.style.display = 'none';
                }
            });

            modalEl.setPrincipal = setPrincipal;
            modalEl.clearPrincipal = clearPrincipal;
        }

        setupPrincipalAutocomplete(modal, modalId === 'editPatientModal');

        // Step 6 Review Builder
        function buildReviewSummary(formEl, reviewCard) {
            if (!reviewCard) return;
            const cat = formEl.querySelector('[name="patient_category"]')?.value || 'Student';
            const surname = formEl.querySelector('[name="surname"]')?.value || '';
            const firstName = formEl.querySelector('[name="first_name"]')?.value || '';
            const middleName = formEl.querySelector('[name="middle_name"]')?.value || '';
            const gender = formEl.querySelector('[name="gender"]')?.value || '';
            const dob = formEl.querySelector('[name="date_of_birth"]')?.value || '';
            const phone = formEl.querySelector('[name="phone"]')?.value || '';
            const email = formEl.querySelector('[name="email"]')?.value || 'None';
            const address = formEl.querySelector('[name="address"]')?.value || '';
            const state = formEl.querySelector('[name="state_of_origin"]')?.value || 'None';
            const lga = formEl.querySelector('[name="lga"]')?.value || 'None';
            const blood = formEl.querySelector('[name="blood_group"]')?.value || 'Unknown';
            const genotype = formEl.querySelector('[name="genotype"]')?.value || 'Unknown';
            const emName = formEl.querySelector('[name="emergency_contact_name"]')?.value || '';
            const emPhone = formEl.querySelector('[name="emergency_contact_phone"]')?.value || '';
            const emRel = formEl.querySelector('[name="emergency_contact_relationship"]')?.value || '';
            const nokName = formEl.querySelector('[name="next_of_kin_name"]')?.value || '';
            const nokPhone = formEl.querySelector('[name="next_of_kin_phone"]')?.value || '';
            const nokRel = formEl.querySelector('[name="next_of_kin_relationship"]')?.value || '';

            let principalSummary = 'None';
            if (cat === 'Dependant') {
                const pName = formEl.querySelector('.selected-principal-card .principal-name')?.textContent;
                const pMeta = formEl.querySelector('.selected-principal-card .principal-meta')?.textContent;
                principalSummary = pName ? `${pName} (${pMeta})` : (formEl.querySelector('.principal-patient-id-input')?.value || 'Not selected');
            }

            let catInfoHtml = '';
            if (cat === 'Student') {
                catInfoHtml = `<strong>Matric No:</strong> ${formEl.querySelector('[name="matric_number"]')?.value || 'N/A'} | <strong>Faculty:</strong> ${formEl.querySelector('[name="faculty"]')?.value || 'N/A'} | <strong>Dept:</strong> ${formEl.querySelector('[name="department"]')?.value || 'N/A'} | <strong>Level:</strong> ${formEl.querySelector('[name="level"]')?.value || 'N/A'}`;
            } else if (cat === 'Staff') {
                catInfoHtml = `<strong>Staff No:</strong> ${formEl.querySelector('[name="staff_number"]')?.value || 'N/A'} | <strong>Dept:</strong> ${formEl.querySelector('[name="staff_department"]')?.value || 'N/A'} | <strong>Position:</strong> ${formEl.querySelector('[name="staff_position"]')?.value || 'N/A'}`;
            } else if (cat === 'Dependant') {
                catInfoHtml = `<strong>Relationship:</strong> ${formEl.querySelector('[name="relationship_to_principal"]')?.value || 'N/A'} | <strong>Principal Staff:</strong> ${principalSummary}`;
            } else if (cat === 'External') {
                catInfoHtml = `<strong>Occupation:</strong> ${formEl.querySelector('[name="occupation"]')?.value || 'N/A'} | <strong>Employer:</strong> ${formEl.querySelector('[name="employer"]')?.value || 'N/A'}`;
            }

            reviewCard.innerHTML = `
                <div style="margin-bottom: 8px;"><strong>Full Name:</strong> ${surname} ${firstName} ${middleName} (${gender})</div>
                <div style="margin-bottom: 8px;"><strong>Category:</strong> <span class="badge status-active">${cat}</span> | <strong>DOB:</strong> ${dob}</div>
                <div style="margin-bottom: 8px;"><strong>Origin:</strong> ${state}, LGA: ${lga}</div>
                <div style="margin-bottom: 8px;"><strong>Contact:</strong> ${phone} | ${email}</div>
                <div style="margin-bottom: 8px;"><strong>Address:</strong> ${address}</div>
                <div style="margin-bottom: 8px;"><strong>Emergency Contact:</strong> ${emName} (${emRel}) - ${emPhone}</div>
                <div style="margin-bottom: 8px;"><strong>Next of Kin:</strong> ${nokName ? nokName + ' (' + (nokRel || 'N/A') + ') - ' + (nokPhone || 'N/A') : 'None provided'}</div>
                <div style="margin-bottom: 8px;"><strong>Medical:</strong> Blood Group: ${blood}, Genotype: ${genotype}</div>
                <div style="margin-bottom: 8px;">${catInfoHtml}</div>
            `;
        }

        // Expose helper on modal element for external populator
        modal.resetToStepOne = function() {
            modal.querySelectorAll('.modal-step').forEach((s, idx) => {
                s.style.display = idx === 0 ? 'block' : 'none';
            });
        };

        modal.updateCountryAndCategory = function(cat, country, stateVal, lgaVal) {
            if (categorySelect) {
                categorySelect.value = cat;
                updateCategoryFields(cat);
            }
            if (countrySelect) {
                countrySelect.value = country || 'Nigeria';
            }
            updateNationality(stateVal, lgaVal);
        };
    }

    setupWizard('registerPatientModal');
    setupWizard('editPatientModal');

    // Register button click: ensure fresh step 1 view
    const regBtn = document.querySelector('[data-modal-target="registerPatientModal"]');
    if (regBtn) {
        regBtn.addEventListener('click', function() {
            const regModal = document.getElementById('registerPatientModal');
            if (regModal) {
                if (regModal.resetToStepOne) regModal.resetToStepOne();
                if (regModal.clearPrincipal) regModal.clearPrincipal();
            }
        });
    }

    // 2. Edit Patient Data Population Function
    function populateEditPatientData(modal, data) {
        const form = modal.querySelector('form');
        if (!form || !data) return;

        if (data.id) document.getElementById('edit_patient_id').value = data.id;
        if (data.status && form.querySelector('[name="status"]')) {
            form.querySelector('[name="status"]').value = data.status;
        }
        if (data.surname) form.querySelector('[name="surname"]').value = data.surname;
        if (data.first_name) form.querySelector('[name="first_name"]').value = data.first_name;
        if (data.middle_name) form.querySelector('[name="middle_name"]').value = data.middle_name;
        if (data.gender) form.querySelector('[name="gender"]').value = data.gender;
        if (data.date_of_birth) {
            const dobF = form.querySelector('[name="date_of_birth"]');
            dobF.value = data.date_of_birth;
            dobF.dispatchEvent(new Event('change'));
        }
        if (data.marital_status) form.querySelector('[name="marital_status"]').value = data.marital_status;

        // Contact
        if (data.phone) form.querySelector('[name="phone"]').value = data.phone;
        if (data.alternate_phone) form.querySelector('[name="alternate_phone"]').value = data.alternate_phone;
        if (data.email) form.querySelector('[name="email"]').value = data.email;
        if (data.address) form.querySelector('[name="address"]').value = data.address;
        if (data.city) form.querySelector('[name="city"]').value = data.city;
        if (data.residential_state) form.querySelector('[name="residential_state"]').value = data.residential_state;

        // Emergency & Next of Kin
        if (data.emergency_contact_name) form.querySelector('[name="emergency_contact_name"]').value = data.emergency_contact_name;
        if (data.emergency_contact_phone) form.querySelector('[name="emergency_contact_phone"]').value = data.emergency_contact_phone;
        if (data.emergency_contact_relationship) form.querySelector('[name="emergency_contact_relationship"]').value = data.emergency_contact_relationship;

        if (data.next_of_kin_name) form.querySelector('[name="next_of_kin_name"]').value = data.next_of_kin_name;
        if (data.next_of_kin_phone) form.querySelector('[name="next_of_kin_phone"]').value = data.next_of_kin_phone;
        if (data.next_of_kin_relationship) form.querySelector('[name="next_of_kin_relationship"]').value = data.next_of_kin_relationship;

        // Medical
        if (data.blood_group) form.querySelector('[name="blood_group"]').value = data.blood_group;
        if (data.genotype) form.querySelector('[name="genotype"]').value = data.genotype;
        if (data.allergies) form.querySelector('[name="allergies"]').value = data.allergies;
        if (data.chronic_conditions) form.querySelector('[name="chronic_conditions"]').value = data.chronic_conditions;
        if (data.disabilities) form.querySelector('[name="disabilities"]').value = data.disabilities;

        // Category-specific fields
        if (data.matric_number) form.querySelector('[name="matric_number"]').value = data.matric_number;
        if (data.department) {
            const deptField = form.querySelector('[name="department"]');
            if (deptField) {
                deptField.value = data.department;
                deptField.dispatchEvent(new Event('change'));
            }
        }
        if (data.faculty) form.querySelector('[name="faculty"]').value = data.faculty;
        if (data.level) {
            const cleanLvl = data.level.replace(/[^0-9]/g, '');
            const lvlSelect = form.querySelector('[name="level"]');
            if (lvlSelect) {
                if (lvlSelect.querySelector(`option[value="${cleanLvl}"]`)) {
                    lvlSelect.value = cleanLvl;
                } else {
                    lvlSelect.value = data.level;
                }
            }
        }

        if (data.staff_number) form.querySelector('[name="staff_number"]').value = data.staff_number;
        if (data.department) form.querySelector('[name="staff_department"]').value = data.department;
        if (data.staff_position) form.querySelector('[name="staff_position"]').value = data.staff_position;

        if (data.relationship_to_principal) form.querySelector('[name="relationship_to_principal"]').value = data.relationship_to_principal;
        if (data.principal_display && modal.setPrincipal) {
            modal.setPrincipal(data.principal_display);
        } else if (data.principal_patient_id) {
            modal.querySelector('.principal-patient-id-input').value = data.principal_patient_id;
        } else if (modal.clearPrincipal) {
            modal.clearPrincipal();
        }

        if (data.occupation) form.querySelector('[name="occupation"]').value = data.occupation;
        if (data.employer) form.querySelector('[name="employer"]').value = data.employer;

        // Initialize Country, State, LGA & Category dynamic display
        const cat = data.patient_category || 'Student';
        const nat = data.nationality || 'Nigeria';
        const st = data.state_of_origin || '';
        const lga = data.lga || '';
        if (modal.updateCountryAndCategory) {
            modal.updateCountryAndCategory(cat, nat, st, lga);
        }
    }

    // Bind Edit Buttons on Table
    document.querySelectorAll('.edit-patient').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const patientId = this.getAttribute('data-patient-id');
            const modal = document.getElementById('editPatientModal');
            const form = document.getElementById('editPatientForm');
            if (!modal || !form) return;

            if (modal.resetToStepOne) modal.resetToStepOne();
            if (modal.clearPrincipal) modal.clearPrincipal();
            form.reset();
            document.getElementById('edit_patient_id').value = '';

            modal.classList.add('active');

            fetch("<?php echo url_wrap('/modules/ajax/get_patient.php'); ?>?id=" + patientId)
                .then(response => {
                    if (!response.ok) throw new Error('HTTP error ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        alert(data.error);
                        return;
                    }
                    populateEditPatientData(modal, data);
                })
                .catch(err => {
                    console.error('Error loading patient data:', err);
                    alert('Error loading patient data. Please try again.');
                });
        });
    });

    // 3. Real-time Duplicate / Former Patient Detection on Registration
    const regModalEl = document.getElementById('registerPatientModal');
    if (regModalEl) {
        const regForm = regModalEl.querySelector('form');
        const dupAlert = document.getElementById('regDuplicatePatientAlert');
        const dupDetails = document.getElementById('regDuplicateDetails');
        const btnTransition = document.getElementById('btnTransitionExisting');
        const btnDismiss = document.getElementById('btnDismissDuplicate');
        let detectedPatientId = null;

        function runDuplicateCheck() {
            if (!regForm) return;
            const matric = regForm.querySelector('[name="matric_number"]')?.value.trim() || '';
            const phone = regForm.querySelector('[name="phone"]')?.value.trim() || '';
            const email = regForm.querySelector('[name="email"]')?.value.trim() || '';
            const surname = regForm.querySelector('[name="surname"]')?.value.trim() || '';
            const first = regForm.querySelector('[name="first_name"]')?.value.trim() || '';
            const dob = regForm.querySelector('[name="date_of_birth"]')?.value.trim() || '';

            if (!matric && !phone && !email && (!surname || !first || !dob)) {
                return;
            }

            const params = new URLSearchParams();
            if (matric) params.append('matric_number', matric);
            if (phone) params.append('phone', phone);
            if (email) params.append('email', email);
            if (surname) params.append('surname', surname);
            if (first) params.append('first_name', first);
            if (dob) params.append('date_of_birth', dob);

            fetch("<?php echo url_wrap('/modules/patients/check_duplicate_patient.php'); ?>?" + params.toString())
                .then(res => res.json())
                .then(data => {
                    if (data.found && data.patient) {
                        detectedPatientId = data.patient.id;
                        if (dupAlert && dupDetails) {
                            dupDetails.innerHTML = `<strong>${data.patient.full_name}</strong> (${data.patient.patient_id}) was previously registered as a <strong>${data.patient.patient_category}</strong> (Status: <strong>${data.patient.status}</strong>, Dept: ${data.patient.department || 'N/A'}, Registered: ${data.patient.registered_date}).<br><span style="color:#0F4E74; font-size:0.83rem;">Matched by: ${data.reason}</span>`;
                            dupAlert.style.display = 'block';
                        }
                    } else {
                        if (dupAlert) dupAlert.style.display = 'none';
                        detectedPatientId = null;
                    }
                })
                .catch(err => console.error('Duplicate check error:', err));
        }

        ['matric_number', 'phone', 'email', 'surname', 'first_name', 'date_of_birth'].forEach(fieldName => {
            const el = regForm.querySelector(`[name="${fieldName}"]`);
            if (el) {
                el.addEventListener('blur', runDuplicateCheck);
                el.addEventListener('change', runDuplicateCheck);
            }
        });

        if (btnDismiss) {
            btnDismiss.addEventListener('click', function() {
                if (dupAlert) dupAlert.style.display = 'none';
            });
        }

        if (btnTransition) {
            btnTransition.addEventListener('click', function() {
                if (!detectedPatientId) return;
                regModalEl.classList.remove('active');
                if (dupAlert) dupAlert.style.display = 'none';

                const editModal = document.getElementById('editPatientModal');
                if (editModal) {
                    editModal.classList.add('active');
                    if (editModal.resetToStepOne) editModal.resetToStepOne();
                    if (editModal.clearPrincipal) editModal.clearPrincipal();

                    fetch("<?php echo url_wrap('/modules/ajax/get_patient.php'); ?>?id=" + detectedPatientId)
                        .then(res => res.json())
                        .then(pdata => {
                            if (pdata && !pdata.error) {
                                populateEditPatientData(editModal, pdata);
                                // Ensure status is defaulted to Active for reactivating
                                const statusSelect = editModal.querySelector('[name="status"]');
                                if (statusSelect) statusSelect.value = 'Active';
                            }
                        })
                        .catch(err => console.error(err));
                }
            });
        }
    }
});
</script>

<?php } ?>
    
  <div class="tabs" role="tablist">
    <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="tab-btn <?php if(!$isArchivedTab) echo 'active'; ?>" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
      <i class="bi bi-people"></i> Active Patients
    </a>
    <?php if ($canManagePatients) { ?>
    <a href="<?php echo url_wrap('/modules/patients/index.php?tab=archived'); ?>" class="tab-btn <?php if($isArchivedTab) echo 'active'; ?>" style="text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
      <i class="bi bi-archive"></i> Archived Records
      <?php 
      $archCountRes = $db_1->query("SELECT COUNT(*) as c FROM patients WHERE status = 'Archived'");
      $archCount = (int)($archCountRes->fetch_assoc()['c'] ?? 0);
      if ($archCount > 0) {
          echo "<span class='badge' style='background:#6c757d; color:white; font-size:0.75rem; padding:2px 7px; border-radius:10px;'>{$archCount}</span>";
      }
      ?>
    </a>
    <?php } ?>
    <button role="tab" class="tab-btn" aria-selected="false" aria-controls="patient-statistics" data-tab="patient-statistics">
      <i class="bi bi-graph-up"></i> Patient Statistics
    </button>
  </div>
    
  <div id="patient" class="tab-content active" role="tabpanel" aria-labelledby="patient-tab">
       
     <form method="GET" style="display: flex; flex-direction: row; align-items: center; justify-content: space-between;">
     <?php if ($isArchivedTab) { ?>
       <input type="hidden" name="tab" value="archived">
     <?php } ?>
	
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
      
      <?php if ($all_patients->num_rows === 0) { ?>
        <tr>
          <td colspan="11" style="text-align: center; padding: 40px 20px; color: #888;">
            <i class="bi bi-folder-x" style="font-size: 2.2rem; display: block; margin-bottom: 8px; color: #aaa;"></i>
            No <?php echo $isArchivedTab ? 'archived' : 'matching'; ?> patient records found.
          </td>
        </tr>
      <?php } ?>
      
      <?php while($patient = $all_patients->fetch_assoc()) { ?>
        
        <tr>
          <td> <?php if (!empty($patient['profile_image'])) { ?>
            <img src="<?php echo url_wrap('modules/patients/images/patient_pictures/' . v_wrap(ru_wrap($patient['profile_image']))); ?>" alt="Patient profile photo" class="patient-profile-thumbnail" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>';"
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
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage));?>" alt="No Patient photo uploaded" class="patient-profile-thumbnail" onerror="this.onerror=null; this.src='<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage)); ?>';"
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
          <td><?php if ($canManagePatients && hasPermission('edit_patient')) { ?><a href="#" class="edit-patient" data-modal-target="editPatientModal" data-patient-id="<?php echo v_wrap($patient['id']); ?>"><i class="bi bi-pencil-square"></i></a><?php } ?></td>
          <td><?php if (hasPermission('view_medical_history')) { ?><a class="view-history" href="<?php echo url_wrap('/modules/patients/history.php?id=' . v_wrap(u_wrap($patient['id']))); ?>"><i class="bi bi-clock-history"></i></a><?php } ?></td>
        </tr>
      <?php } // close foreach statement ?>
       </table>
       
       
    </div>
    
    <div style="margin-top:20px; text-align: center;">

<?php
$queryString = http_build_query([
  'tab' => $isArchivedTab ? 'archived' : null,
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
    <?php
    // Fetch real-time statistics
    $p_total_res = $db_1->query("SELECT 
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_count,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) AS today_count,
        SUM(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS month_count
        FROM patients");
    $p_summary = $p_total_res ? $p_total_res->fetch_assoc() : ['total' => 0, 'active_count' => 0, 'today_count' => 0, 'month_count' => 0];
    $total_pts = (int)($p_summary['total'] ?? 0);
    $active_pts = (int)($p_summary['active_count'] ?? 0);
    $today_pts = (int)($p_summary['today_count'] ?? 0);
    $month_pts = (int)($p_summary['month_count'] ?? 0);

    // Categories
    $cat_res = $db_1->query("SELECT patient_category, COUNT(*) as cnt FROM patients GROUP BY patient_category ORDER BY cnt DESC");
    $cat_data = [];
    if ($cat_res) {
        while ($r = $cat_res->fetch_assoc()) {
            $cat_data[$r['patient_category']] = (int)$r['cnt'];
        }
    }

    // Gender
    $gen_res = $db_1->query("SELECT gender, COUNT(*) as cnt FROM patients GROUP BY gender");
    $gen_data = [];
    if ($gen_res) {
        while ($r = $gen_res->fetch_assoc()) {
            $gen_data[$r['gender']] = (int)$r['cnt'];
        }
    }

    // Blood Groups
    $bg_res = $db_1->query("SELECT blood_group, COUNT(*) as cnt FROM patients WHERE blood_group IS NOT NULL AND blood_group != '' GROUP BY blood_group ORDER BY cnt DESC");
    $bg_data = [];
    if ($bg_res) {
        while ($r = $bg_res->fetch_assoc()) {
            $bg_data[$r['blood_group']] = (int)$r['cnt'];
        }
    }

    // Genotypes
    $gt_res = $db_1->query("SELECT genotype, COUNT(*) as cnt FROM patients WHERE genotype IS NOT NULL AND genotype != '' GROUP BY genotype ORDER BY cnt DESC");
    $gt_data = [];
    if ($gt_res) {
        while ($r = $gt_res->fetch_assoc()) {
            $gt_data[$r['genotype']] = (int)$r['cnt'];
        }
    }

    // Registration sources
    $src_res = $db_1->query("SELECT registration_source, COUNT(*) as cnt FROM patients WHERE registration_source IS NOT NULL AND registration_source != '' GROUP BY registration_source ORDER BY cnt DESC");
    $src_data = [];
    if ($src_res) {
        while ($r = $src_res->fetch_assoc()) {
            $src_data[$r['registration_source']] = (int)$r['cnt'];
        }
    }

    // Top Student Departments
    $dept_res = $db_1->query("SELECT department, COUNT(*) as cnt FROM patients WHERE patient_category = 'Student' AND department IS NOT NULL AND department != '' GROUP BY department ORDER BY cnt DESC LIMIT 5");
    $dept_data = [];
    if ($dept_res) {
        while ($r = $dept_res->fetch_assoc()) {
            $dept_data[$r['department']] = (int)$r['cnt'];
        }
    }
    ?>
    <div style="padding: 20px 10px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div>
                <h3 style="margin: 0; color: #0F4E74; font-size: 1.4rem;">Patient Demographics & Clinic Analytics</h3>
                <p style="margin: 4px 0 0 0; color: #666; font-size: 0.9rem;">Real-time overview of registered patients and clinical distributions.</p>
            </div>
            <button onclick="window.location.reload();" class="btn" style="background:#eaf2f8; color:#0F4E74; border:1px solid #b3d1e6; padding:8px 14px; border-radius:6px; cursor:pointer; font-weight:600; font-size:0.85rem;">
                <i class="bi bi-arrow-clockwise"></i> Refresh Data
            </button>
        </div>

        <!-- KPI Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px; margin-bottom: 30px;">
            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; border-left: 5px solid #0F4E74; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <div style="font-size: 0.82rem; font-weight: 600; color: #666; text-transform: uppercase;">Total Registered</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #0F4E74; margin: 6px 0;"><?php echo number_format($total_pts); ?></div>
                <div style="font-size: 0.8rem; color: #28a745;"><i class="bi bi-check-circle-fill"></i> <?php echo number_format($active_pts); ?> Active Patients</div>
            </div>

            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; border-left: 5px solid #17a2b8; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <div style="font-size: 0.82rem; font-weight: 600; color: #666; text-transform: uppercase;">Registered Today</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #17a2b8; margin: 6px 0;"><?php echo number_format($today_pts); ?></div>
                <div style="font-size: 0.8rem; color: #666;"><i class="bi bi-calendar-event"></i> <?php echo date('M d, Y'); ?></div>
            </div>

            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; border-left: 5px solid #ffc107; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <div style="font-size: 0.82rem; font-weight: 600; color: #666; text-transform: uppercase;">Registered This Month</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #b78103; margin: 6px 0;"><?php echo number_format($month_pts); ?></div>
                <div style="font-size: 0.8rem; color: #666;"><i class="bi bi-calendar3"></i> In <?php echo date('F Y'); ?></div>
            </div>

            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; border-left: 5px solid #28a745; padding: 18px 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <div style="font-size: 0.82rem; font-weight: 600; color: #666; text-transform: uppercase;">Primary Category</div>
                <div style="font-size: 1.8rem; font-weight: 700; color: #28a745; margin: 6px 0;">
                    <?php echo number_format($cat_data['Student'] ?? 0); ?>
                </div>
                <div style="font-size: 0.8rem; color: #666;"><i class="bi bi-mortarboard-fill"></i> Students Enrolled</div>
            </div>
        </div>

        <!-- Two Column Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px; margin-bottom: 25px;">
            <!-- Category Breakdown -->
            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <h4 style="margin: 0 0 15px 0; color: #0F4E74; font-size: 1.1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="bi bi-pie-chart-fill" style="color: #0F4E74;"></i> Breakdown by Patient Category
                </h4>
                <?php 
                $cat_colors = ['Student' => '#0F4E74', 'Staff' => '#28a745', 'Dependant' => '#fd7e14', 'External' => '#6f42c1'];
                if (empty($cat_data)): ?>
                    <p style="color:#888; font-size:0.9rem;">No patient data available yet.</p>
                <?php else:
                    foreach ($cat_data as $cat_name => $count):
                        $pct = $total_pts > 0 ? round(($count / $total_pts) * 100, 1) : 0;
                        $color = $cat_colors[$cat_name] ?? '#6c757d';
                ?>
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 0.9rem; margin-bottom: 5px;">
                            <span style="font-weight: 600; color: #333;"><?php echo htmlspecialchars($cat_name); ?></span>
                            <span style="color: #666; font-size: 0.85rem;"><strong><?php echo number_format($count); ?></strong> (<?php echo $pct; ?>%)</span>
                        </div>
                        <div style="height: 8px; background: #e9ecef; border-radius: 4px; overflow: hidden;">
                            <div style="height: 100%; width: <?php echo $pct; ?>%; background: <?php echo $color; ?>; border-radius: 4px;"></div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>

            <!-- Gender & Registration Source -->
            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <h4 style="margin: 0 0 15px 0; color: #0F4E74; font-size: 1.1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="bi bi-gender-ambiguous" style="color: #0F4E74;"></i> Gender & Registration Intake
                </h4>
                <div style="margin-bottom: 20px;">
                    <div style="font-size: 0.85rem; font-weight: 600; color: #666; margin-bottom: 8px;">Gender Ratio:</div>
                    <div style="display: flex; gap: 15px;">
                        <?php 
                        $male_cnt = $gen_data['Male'] ?? 0;
                        $female_cnt = $gen_data['Female'] ?? 0;
                        $m_pct = $total_pts > 0 ? round(($male_cnt / $total_pts) * 100, 1) : 0;
                        $f_pct = $total_pts > 0 ? round(($female_cnt / $total_pts) * 100, 1) : 0;
                        ?>
                        <div style="flex: 1; background: #eaf2f8; border-radius: 8px; padding: 12px 15px; border-left: 4px solid #0F4E74;">
                            <div style="font-size: 0.8rem; color: #666;">Male</div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: #0F4E74;"><?php echo number_format($male_cnt); ?> <span style="font-size: 0.8rem; font-weight: normal; color: #666;">(<?php echo $m_pct; ?>%)</span></div>
                        </div>
                        <div style="flex: 1; background: #fdf2f8; border-radius: 8px; padding: 12px 15px; border-left: 4px solid #e83e8c;">
                            <div style="font-size: 0.8rem; color: #666;">Female</div>
                            <div style="font-size: 1.3rem; font-weight: 700; color: #e83e8c;"><?php echo number_format($female_cnt); ?> <span style="font-size: 0.8rem; font-weight: normal; color: #666;">(<?php echo $f_pct; ?>%)</span></div>
                        </div>
                    </div>
                </div>

                <div>
                    <div style="font-size: 0.85rem; font-weight: 600; color: #666; margin-bottom: 8px;">Intake Method (Source):</div>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        <?php if (empty($src_data)): ?>
                            <span style="color:#888; font-size:0.85rem;">None recorded</span>
                        <?php else:
                            foreach ($src_data as $src_name => $count):
                        ?>
                            <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px; padding: 6px 12px; font-size: 0.85rem;">
                                <strong><?php echo htmlspecialchars($src_name ?: 'Standard'); ?>:</strong> <?php echo number_format($count); ?>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Third Row: Blood Groups, Genotypes & Top Departments -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
            <!-- Blood Group Distribution -->
            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <h4 style="margin: 0 0 15px 0; color: #0F4E74; font-size: 1.1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="bi bi-droplet-fill" style="color: #dc3545;"></i> Blood Group Distribution
                </h4>
                <?php if (empty($bg_data)): ?>
                    <p style="color:#888; font-size:0.85rem;">No blood group data recorded yet.</p>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(70px, 1fr)); gap: 10px;">
                        <?php foreach ($bg_data as $bg => $count): ?>
                            <div style="background: #fff5f5; border: 1px solid #fed7d7; border-radius: 8px; text-align: center; padding: 10px 5px;">
                                <div style="font-weight: 700; color: #c53030; font-size: 1.1rem;"><?php echo htmlspecialchars($bg); ?></div>
                                <div style="font-size: 0.8rem; color: #4a5568; margin-top: 3px;"><?php echo number_format($count); ?> pts</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Genotype Distribution -->
            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <h4 style="margin: 0 0 15px 0; color: #0F4E74; font-size: 1.1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="bi bi-activity" style="color: #28a745;"></i> Genotype Distribution
                </h4>
                <?php if (empty($gt_data)): ?>
                    <p style="color:#888; font-size:0.85rem;">No genotype data recorded yet.</p>
                <?php else: ?>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(70px, 1fr)); gap: 10px;">
                        <?php foreach ($gt_data as $gt => $count): ?>
                            <div style="background: #f0fff4; border: 1px solid #c6f6d5; border-radius: 8px; text-align: center; padding: 10px 5px;">
                                <div style="font-weight: 700; color: #22543d; font-size: 1.1rem;"><?php echo htmlspecialchars($gt); ?></div>
                                <div style="font-size: 0.8rem; color: #4a5568; margin-top: 3px;"><?php echo number_format($count); ?> pts</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Top Student Departments -->
            <div style="background: #fff; border-radius: 10px; border: 1px solid #e1e8ed; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.03);">
                <h4 style="margin: 0 0 15px 0; color: #0F4E74; font-size: 1.1rem; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px;">
                    <i class="bi bi-building" style="color: #0F4E74;"></i> Top Student Departments
                </h4>
                <?php if (empty($dept_data)): ?>
                    <p style="color:#888; font-size:0.85rem;">No student department data recorded yet.</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid #e9ecef; text-align: left;">
                                <th style="padding: 6px 8px; color: #555;">Department</th>
                                <th style="padding: 6px 8px; color: #555; text-align: right;">Enrolled</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dept_data as $dept => $count): ?>
                                <tr style="border-bottom: 1px solid #f8f9fa;">
                                    <td style="padding: 8px; color: #333; font-weight: 500;"><?php echo htmlspecialchars($dept); ?></td>
                                    <td style="padding: 8px; text-align: right; font-weight: 600; color: #0F4E74;"><?php echo number_format($count); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
      
 </main>
  
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
