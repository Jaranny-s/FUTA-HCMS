<?php

require_once('../../../private/config.php');

require_password_reset();

if(!hasPermission('create_patient')){
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Register Patient";
$specificCss = "/assets/css/patient.css";
$specificJs = "/assets/js/patient.js";

include(SHARED_PATH.'/header.php');

$currentStep = 5;

if($_SERVER['REQUEST_METHOD'] == "POST"){

    $_SESSION['patient_registration'] = array_merge(
        $_SESSION['patient_registration'] ?? [],
        $_POST
    );
    
    if(isset($_POST['return_to_review']) && $_POST['return_to_review'] == '1'){

    redirect_to(
      url_wrap('/modules/patients/review.php')
        );

}

    redirect_to(
        url_wrap('/modules/patients/review.php')
    );
}

$patient = $_SESSION['patient_registration'] ?? [];
$editing = isset($_GET['edit']) && $_GET['edit'] == '1';

?>

<div id="content">

<?php include(SHARED_PATH.'/navigation.php'); ?>

<main class="main-content">

<a href="javascript:history.back()" class="btn btn-primary" id="link_layout">
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

<?php include(SHARED_PATH.'/patient_registration_progress.php'); ?>

<h2>Emergency & Next-of-Kin Information</h2>

<form method="post">
    
<?php if($editing){ ?>

<input type="hidden" name="return_to_review" value="1">

<?php } ?>

<h3>Emergency Contact</h3>

<div class="form-grid">

    <div class="form-group">

        <label for="emergency_name">
            Contact Name
        </label>

        <input
            type="text"
            name="emergency_name"
            id="emergency_name"
            value="<?php echo v_wrap($patient['emergency_name'] ?? ''); ?>"
        >

    </div>


    <div class="form-group">

        <label for="emergency_phone">
            Phone Number
        </label>

        <input
            type="text"
            name="emergency_phone"
            id="emergency_phone"
            value="<?php echo v_wrap($patient['emergency_phone'] ?? ''); ?>"
        >

    </div>


    <div class="form-group">

        <label for="emergency_relationship">
            Relationship
        </label>

        <input
            type="text"
            name="emergency_relationship"
            id="emergency_relationship"
            value="<?php echo v_wrap($patient['emergency_relationship'] ?? ''); ?>"
        >

    </div>

</div>


<h3>Next of Kin</h3>

<div class="form-grid">

    <div class="form-group">

        <label for="next_of_kin_name">
            Name
        </label>

        <input
            type="text"
            name="next_of_kin_name"
            id="next_of_kin_name"
            value="<?php echo v_wrap($patient['next_of_kin_name'] ?? ''); ?>"
        >

    </div>


    <div class="form-group">

        <label for="next_of_kin_phone">
            Phone Number
        </label>

        <input
            type="text"
            name="next_of_kin_phone"
            id="next_of_kin_phone"
            value="<?php echo v_wrap($patient['next_of_kin_phone'] ?? ''); ?>"
        >

    </div>


    <div class="form-group">

        <label for="next_of_kin_relationship">
            Relationship
        </label>

        <input
            type="text"
            name="next_of_kin_relationship"
            id="next_of_kin_relationship"
            value="<?php echo v_wrap($patient['next_of_kin_relationship'] ?? ''); ?>"
        >

    </div>

</div>


<div class="wizard-buttons">

<a
    class="btn btn-secondary"
    href="<?php echo url_wrap('/modules/patients/medical_information.php'. ($editing ? '?edit=1' : '')); ?>"
>
    ← Previous
</a>

<button type="submit" class="btn btn-primary">
    Review →
</button>

</div>

</form>

</main>

</div>

<?php include(SHARED_PATH . '/footer.php'); ?>