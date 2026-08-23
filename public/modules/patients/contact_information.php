<?php
require_once('../../../private/config.php');

require_password_reset();

if (!hasPermission('create_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = " Register Patient";
$specificCss = "/assets/css/patient.css";
$specificJs = "/assets/js/patient.js";

include(SHARED_PATH.'/header.php');

$currentStep = 2;

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

        url_wrap('/modules/patients/university_information.php')

    );

}

$patient = $_SESSION['patient_registration'] ?? [];
$editing = isset($_GET['edit']) && $_GET['edit'] == '1';

?>

<div id="content">

<?php include(SHARED_PATH.'/navigation.php'); ?>

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

<?php include(SHARED_PATH.'/patient_registration_progress.php'); ?>

<h2>Contact Information</h2>

<form method="post">

<?php if($editing){ ?>

<input type="hidden" name="return_to_review" value="1">

<?php } ?>
    
<div class="form-grid">

<div class="form-group">

<label>Phone Number *</label>

<input type="text" name="phone" id="phone" value="<?php echo v_wrap($patient['phone'] ?? ''); ?>" required>

</div>
    
<div class="form-group">

<label for="alternate_phone">Alternate Phone</label>
    
<input type="text" name="alternate_phone" id="alternate_phone" value="<?php echo v_wrap($patient['alternate_phone'] ?? ''); ?>">

</div>
    
<div class="form-group">

<label>Email Address</label>

<input type="email" name="email" value="<?php echo v_wrap($patient['email'] ?? ''); ?>">

</div>

<div class="form-group">

<label>Residential Address *</label>

<textarea name="address" required><?php echo v_wrap($patient['address'] ?? ''); ?></textarea>

</div>

<div class="form-group">

<label>City of Residence</label>

<input type="text" name="city" value="<?php echo v_wrap($patient['city'] ?? ''); ?>">

</div>

<div class="form-group">

<label>State of Residence</label>

<input type="text" name="residential_state" id="residential_state"  value="<?php echo v_wrap($patient['residential_state'] ?? ''); ?>">

</div>

<div class="form-group">

<label>Postal Code</label>

<input type="text" name="postal_code" value="<?php echo v_wrap($patient['postal_code'] ?? ''); ?>">

</div>

</div>

<div class="wizard-buttons">

<a
class="btn btn-secondary"
href="<?php echo url_wrap('/modules/patients/basic_information.php'. ($editing ? '?edit=1' : ''));?>">

← Previous

</a>

<button
type="submit"
class="btn btn-primary">

Next →

</button>

</div>

</form>

</main>

</div>

<?php include(SHARED_PATH.'/footer.php'); ?>
