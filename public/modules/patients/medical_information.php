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

$currentStep = 4;

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
        url_wrap('/modules/patients/emergency_information.php')
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

<h2>Medical Information</h2>

<form method="post">
    
<?php if($editing){ ?>

<input type="hidden" name="return_to_review" value="1">

<?php } ?>

<div class="form-grid">

    <div class="form-group">

        <label for="blood_group">
            Blood Group
        </label>

        <select name="blood_group" id="blood_group">

            <option value="">Select Blood Group</option>

            <option value="A+"
                <?php echo (($patient['blood_group'] ?? '') === 'A+') ? 'selected' : ''; ?>>
                A+
            </option>

            <option value="A-"
                <?php echo (($patient['blood_group'] ?? '') === 'A-') ? 'selected' : ''; ?>>
                A-
            </option>

            <option value="B+"
                <?php echo (($patient['blood_group'] ?? '') === 'B+') ? 'selected' : ''; ?>>
                B+
            </option>

            <option value="B-"
                <?php echo (($patient['blood_group'] ?? '') === 'B-') ? 'selected' : ''; ?>>
                B-
            </option>

            <option value="AB+"
                <?php echo (($patient['blood_group'] ?? '') === 'AB+') ? 'selected' : ''; ?>>
                AB+
            </option>

            <option value="AB-"
                <?php echo (($patient['blood_group'] ?? '') === 'AB-') ? 'selected' : ''; ?>>
                AB-
            </option>

            <option value="O+"
                <?php echo (($patient['blood_group'] ?? '') === 'O+') ? 'selected' : ''; ?>>
                O+
            </option>

            <option value="O-"
                <?php echo (($patient['blood_group'] ?? '') === 'O-') ? 'selected' : ''; ?>>
                O-
            </option>

        </select>

    </div>


    <div class="form-group">

        <label for="genotype">
            Genotype
        </label>

        <select name="genotype" id="genotype">

            <option value="">Select Genotype</option>

            <option value="AA"
                <?php echo (($patient['genotype'] ?? '') === 'AA') ? 'selected' : ''; ?>>
                AA
            </option>

            <option value="AS"
                <?php echo (($patient['genotype'] ?? '') === 'AS') ? 'selected' : ''; ?>>
                AS
            </option>

            <option value="SS"
                <?php echo (($patient['genotype'] ?? '') === 'SS') ? 'selected' : ''; ?>>
                SS
            </option>

            <option value="AC"
                <?php echo (($patient['genotype'] ?? '') === 'AC') ? 'selected' : ''; ?>>
                AC
            </option>

            <option value="SC"
                <?php echo (($patient['genotype'] ?? '') === 'SC') ? 'selected' : ''; ?>>
                SC
            </option>

        </select>

    </div>


    <div class="form-group">

        <label for="allergies">
            Allergies
        </label>

        <textarea
            name="allergies"
            id="allergies"
            rows="4"
            placeholder="Enter known allergies, if any"><?php echo v_wrap($patient['allergies'] ?? ''); ?></textarea>

    </div>


    <div class="form-group">

        <label for="chronic_conditions">
            Chronic Conditions
        </label>

        <textarea
            name="chronic_conditions"
            id="chronic_conditions"
            rows="4"
            placeholder="Enter known chronic medical conditions, if any"><?php echo v_wrap($patient['chronic_conditions'] ?? ''); ?></textarea>

    </div>


    <div class="form-group">

        <label for="disabilities">
            Disabilities
        </label>

        <textarea
            name="disabilities"
            id="disabilities"
            rows="4"
            placeholder="Enter any relevant disabilities, if any"><?php echo v_wrap($patient['disabilities'] ?? ''); ?></textarea>

    </div>

</div>


<div class="wizard-buttons">

<a
    class="btn btn-secondary"
    href="<?php echo url_wrap('/modules/patients/university_information.php'. ($editing ? '?edit=1' : '')); ?>"
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

<?php include(SHARED_PATH . '/footer.php'); ?>