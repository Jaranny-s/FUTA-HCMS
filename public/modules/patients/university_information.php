<?php

require_once('../../../private/config.php');

require_password_reset();

if(!hasPermission('create_patient')){
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title=" Register Patient";
$specificCss="/assets/css/patient.css";
$specificJs="/assets/js/patient.js";

include(SHARED_PATH.'/header.php');

$currentStep=3;

if($_SERVER['REQUEST_METHOD'] == "POST"){

$_SESSION['patient_registration'] = array_merge(

$_SESSION['patient_registration']??[],

$_POST

);

if(isset($_POST['return_to_review']) && $_POST['return_to_review'] == '1'){

 redirect_to(
    url_wrap('/modules/patients/review.php')
    );

}
    
    
redirect_to(

url_wrap('/modules/patients/medical_information.php')

);

}

$patient = $_SESSION['patient_registration'] ?? [];
$patientCategory = $patient['patient_category'] ?? '';
$editing = isset($_GET['edit']) && $_GET['edit'] == '1';

?>

<div id="content">

<?php include(SHARED_PATH.'/navigation.php');?>

<main class="main-content">

    <i class="bi bi-arrow-left"></i> Back
</a>

<div class="top">
    <p class="top-head">Patient Registration</p>
    <p class="top-description">
        Register a new patient
    </p>
</div>

<?php
echo display_session_message();
echo display_errors($errors ?? []);
?>

<?php include(SHARED_PATH.'/patient_registration_progress.php');?>

<h2>University / Employment Information</h2>

<form method="post">
    
<?php if($editing){ ?>

<input type="hidden" name="return_to_review" value="1">

<?php } ?>

<input type="hidden" id="patient_category" value="<?php echo v_wrap($patient['patient_category'] ?? '');?>">
    
<?php if($patientCategory === "Student"){ ?>

<div id="student-fields">

    <h3>Student Information</h3>

    <div class="form-grid">

        <div class="form-group">

            <label for="matric_number">Matric Number</label>

            <input type="text" name="matric_number" id="matric_number" value="<?php echo v_wrap($patient['matric_number'] ?? ''); ?>">

        </div>

        <div class="form-group">

            <label for="faculty">Faculty</label>

            <input type="text" name="faculty" id="faculty" value="<?php echo v_wrap($patient['faculty'] ?? ''); ?>">

        </div>

        <div class="form-group">

            <label for="department">Department</label>

            <input type="text" name="department" id="department" value="<?php echo v_wrap($patient['department'] ?? ''); ?>">

        </div>

        <div class="form-group">

            <label for="level">Level</label>

            <input type="text" name="level" id="level" value="<?php echo v_wrap($patient['level'] ?? ''); ?>"
            >

        </div>

    </div>

</div>

<?php } ?>
    
<?php if($patientCategory === "Staff"){ ?>

<div id="staff-fields">

    <h3>Staff Information</h3>

    <div class="form-grid">

        <div class="form-group">

            <label for="staff_number">Staff Number</label>

            <input type="text" name="staff_number" id="staff_number" value="<?php echo v_wrap($patient['staff_number'] ?? ''); ?>">

        </div>

        <div class="form-group">

            <label for="position">Position</label>

            <input type="text" name="position" id="position" value="<?php echo v_wrap($patient['position'] ?? ''); ?>">

        </div>

    </div>

</div>

<?php } ?>

<?php if($patientCategory === "Dependant"){ ?>

<div id="dependant-fields">

    <h3>Dependant Information</h3>

    <div class="form-grid">

        <div class="form-group">

            <label for="relationship_to_principal">
                Relationship to Principal
            </label>

            <input type="text" name="relationship_to_principal" id="relationship_to_principal" value="<?php echo v_wrap($patient['relationship_to_principal'] ?? ''); ?>">

        </div>

        <div class="form-group">

            <label for="principal_patient_id">
                Principal Patient ID
            </label>

            <input type="text" name="principal_patient_id" id="principal_patient_id" value="<?php echo v_wrap($patient['principal_patient_id'] ?? ''); ?>">

        </div>

    </div>

</div>

<?php } ?>
    
<?php if($patientCategory === "External"){ ?>

<div id="external-fields">

    <h3>External Patient</h3>

    <div class="form-grid">

        <div class="form-group">

            <label for="occupation">Occupation</label>

            <input type="text" name="occupation" id="occupation" value="<?php echo v_wrap($patient['occupation'] ?? ''); ?>">

        </div>

        <div class="form-group">

            <label for="employer">Employer</label>

            <input type="text" name="employer" id="employer" value="<?php echo v_wrap($patient['employer'] ?? ''); ?>">

        </div>

    </div>

</div>

<?php } ?>

<div class="wizard-buttons">

<a class="btn btn-secondary"
href="<?php echo url_wrap('/modules/patients/contact_information.php' . ($editing ? '?edit=1' : '')); ?>"
>

← Previous

</a>

<button type="submit" class="btn btn-primary">

Next →

</button>

</div>

</form>

</main>

</div>

<?php include(SHARED_PATH . '/footer.php');?>
