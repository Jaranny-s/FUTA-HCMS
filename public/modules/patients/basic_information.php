<?php
require_once('../../../private/config.php');
require_once(PRIVATE_PATH.'/data/countries.php');
require_once(PRIVATE_PATH.'/data/nigerian_states.php');
require_once(PRIVATE_PATH.'/data/nigerian_lgas.php');

require_password_reset();

if (!hasPermission('create_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Register Patient";
$specificCss = "/assets/css/patient.css";
$specificJs = "/assets/js/patient.js";


include(SHARED_PATH . '/header.php');





$currentStep = 1;

/*
|--------------------------------------------------------------------------
| Patient registration session
|--------------------------------------------------------------------------
*/

$patient = $_SESSION['patient_registration'] ?? [];


/*
|--------------------------------------------------------------------------
| Default patient image
|--------------------------------------------------------------------------
|
| CHANGE THIS filename if your generic/default patient image has
| a different filename.
|
*/

$defaultPatientImage = 'default_profile_pic.png';


/*
|--------------------------------------------------------------------------
| POST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |----------------------------------------------------------------------
    | Start with the existing registration data.
    |----------------------------------------------------------------------
    */

    $patient = $_SESSION['patient_registration'] ?? [];


    /*
    |----------------------------------------------------------------------
    | Save normal form fields into the registration session.
    |----------------------------------------------------------------------
    */

    $patient = array_merge(
        $patient,
        $_POST
    );


    /*
    |----------------------------------------------------------------------
    | Existing image
    |----------------------------------------------------------------------
    */

    $oldImageName =
        $patient['profile_image']
        ?? $defaultPatientImage;

    $imageName = $oldImageName;


    /*
    |----------------------------------------------------------------------
    | New image uploaded?
    |----------------------------------------------------------------------
    */

    if (
        isset($_FILES['profile_image']) &&
        $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        $uploadResult =
            upload_patient_image(
                $_FILES['profile_image']
            );


        if (!$uploadResult['success']) {

            $errors[] =
                $uploadResult['error'];

        } else {

            /*
            |------------------------------------------------------------------
            | New upload succeeded.
            |------------------------------------------------------------------
            */

            $imageName =
                $uploadResult['filename'];


            /*
            |------------------------------------------------------------------
            | Delete the previous uploaded image.
            |------------------------------------------------------------------
            |
            | NEVER delete the generic/default image.
            |
            */

            if (
                !empty($oldImageName) &&
                $oldImageName !== $defaultPatientImage &&
                $oldImageName !== $imageName
            ) {

                delete_patient_image(
                    $oldImageName
                );

            }

        }

    }


    /*
    |----------------------------------------------------------------------
    | Always keep the current image filename in the session.
    |----------------------------------------------------------------------
    */

    $patient['profile_image'] =
        $imageName;


    $_SESSION['patient_registration'] =
        $patient;


    /*
    |----------------------------------------------------------------------
    | If editing from Review, return to Review.
    |----------------------------------------------------------------------
    */

    if (
        isset($_POST['return_to_review']) &&
        $_POST['return_to_review'] === '1'
    ) {

        redirect_to(
            url_wrap(
                '/modules/patients/review.php'
            )
        );

    }


    /*
    |----------------------------------------------------------------------
    | Continue registration.
    |----------------------------------------------------------------------
    */

    redirect_to(
        url_wrap(
            '/modules/patients/contact_information.php'
        )
    );

}


/*
|--------------------------------------------------------------------------
| Reload registration data after POST / normal page load
|--------------------------------------------------------------------------
*/

$patient =
    $_SESSION['patient_registration'] ?? [];


$imageName =
    $patient['profile_image']
    ?? $defaultPatientImage;


$editing =
    isset($_GET['edit']) &&
    $_GET['edit'] === '1';


$savedNationality =
    $patient['nationality']
    ?? 'Nigeria';

$savedState =
    $patient['state_of_origin']
    ?? '';

$savedLga =
    $patient['lga']
    ?? '';


?>

<div id="content">

<?php include(SHARED_PATH . '/navigation.php'); ?>

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
    
<?php include(SHARED_PATH . '/patient_registration_progress.php'); ?>
    
<form method="post" enctype="multipart/form-data" id="patient-form">
    
    
    <div class="form-step active" id="step-1">

<h2>Basic Information</h2>

<?php if($editing){ ?>

<input type="hidden" name="return_to_review" value="1">

<?php } ?>
        
<div class="form-grid">
    
    <div class="form-group">

        <label>System Patient ID</label>

        <input type="text" value="Automatically Generated" readonly>

    </div>
    
    <div class="form-group">

        <label for="surname">Surname <span class="required">*</span></label>

        <input type="text" name="surname" id="surname" value="<?php echo v_wrap($patient['surname'] ?? ''); ?>" required>

    </div>
    
    <div class="form-group">

        <label for="first_name">First Name <span class="required">*</span></label>

        <input type="text" name="first_name" id="first_name" value="<?php echo v_wrap($patient['first_name'] ?? ''); ?>" required>

    </div>
    
    <div class="form-group">

        <label for="middle_name">Middle Name</label>

        <input type="text" name="middle_name" id="middle_name" value="<?php echo v_wrap($patient['middle_name'] ?? ''); ?>">

    </div>
    
    <div class="form-group">

        <label for="gender">Gender<span class="required">*</span></label>

        <select name="gender" id="gender" required>

            <option value="">Select</option>

            <option value="Male" <?php if(($patient['gender'] ?? '') == "Male") echo "selected"; ?>> Male </option>

        <option value="Female" <?php if(($patient['gender'] ?? '') == "Female") echo "selected"; ?>> Female </option>

        </select>

    </div>
    
    <div class="form-group">

        <label for="date_of_birth">Date of Birth</label>

        <input type="date" name="date_of_birth" id="date_of_birth" value="<?php echo v_wrap($patient['date_of_birth'] ?? ''); ?>">

    </div>
    
    <div class="form-group">

        <label>Age</label>

        <input type="text" id="age" value="<?php echo v_wrap($patient['age'] ?? ''); ?>" readonly>

    </div>
    
    <div class="form-group">

        <label for="nationality">Nationality</label>

       <select name="nationality" id="nationality">

    <?php foreach($countries as $country){ ?>

        <option value="<?php echo v_wrap($country); ?>"
            <?php echo ($savedNationality === $country) ? 'selected' : ''; ?>>

            <?php echo v_wrap($country); ?>

        </option>

    <?php } ?>

</select>
        
    </div>
    
    <div class="form-group" id="state-div">
    
        <select name="state_of_origin" id="state">

    <option value="">Select State of Origin</option>

    <?php foreach($states as $state){ ?>

        <option value="<?php echo v_wrap($state); ?>"
            <?php echo ($savedState === $state) ? 'selected' : ''; ?>>

            <?php echo v_wrap($state); ?>

        </option>

    <?php } ?>

</select>
        
    </div>
    
    <div class="form-group" id="lga-div">

        <div class="form-group" id="lga-div">

    <select name="lga" id="lga">

        <option value="">Select LGA of Origin</option>

    </select>

</div>
        
    </div>
    
    <div class="form-group">

        <label>Marital Status</label>

        <select name="marital_status">

            <option value="">Select</option>

            <option value="Single" <?php if(($patient['marital_status'] ?? '') == "Single") echo "selected"; ?>> Single </option>

            <option value="Married" <?php if(($patient['marital_status'] ?? '') == "Married") echo "selected"; ?>> Married </option>

            <option value="Divorced" <?php if(($patient['marital_status'] ?? '') == "Divorced") echo "selected"; ?>> Divorced </option>

           <option value="Widowed" <?php if(($patient['marital_status'] ?? '') == "Widowed") echo "selected"; ?>> Widowed </option>

        </select>

    </div>
    
    <div class="form-group">

        <label>Patient Category</label>

        <select name="patient_category" id="patient_category" required>

            <option value="">Select</option>

            <option value="Student" <?php if(($patient['patient_category'] ?? '') == "Student") echo "selected"; ?>> Student </option>

            <option value="Staff" <?php if(($patient['patient_category'] ?? '') == "Staff") echo "selected"; ?>> Staff </option>

            <option value="Dependant" <?php if(($patient['patient_category'] ?? '') == "Dependant") echo "selected"; ?>> Dependant </option>

            <option value="External" <?php if(($patient['patient_category'] ?? '') == "External") echo "selected"; ?>> External </option>

        </select>

    </div>
    
    <div class="form-group">

    <label>Patient Photo</label>


    <div
        id="patient-photo-area"
        class="patient-photo-editor"
    >

        <div id="patient-photo-preview">

            <?php if (!empty($imageName)): ?>

                <img
                    src="<?php
                        echo url_wrap(
                            '/assets/images/' .
                            v_wrap($imageName)
                        );
                    ?>"
                    alt="Patient photo"
                    class="patient-profile-image"
                >

            <?php endif; ?>

        </div>


        <div class="patient-photo-info">

            <?php if ($imageName !== $defaultPatientImage): ?>

                <p id="current-photo-name">
                    Current photo:
                    <strong>
                        <?php
                        echo v_wrap($imageName);
                        ?>
                    </strong>
                </p>

            <?php else: ?>

                <p id="current-photo-name">
                    No personal photo uploaded.
                </p>

            <?php endif; ?>

        </div>


        <input
            type="file"
            name="profile_image"
            id="profile_image"
            accept=".jpg,.jpeg,.png,.webp"
        >


        <small>
            Leave this empty to keep the current photo.
        </small>


        <?php if ($imageName !== $defaultPatientImage): ?>

            <button
                type="button"
                id="open-registration-delete"
                class="btn btn-danger"
                data-image="<?php echo v_wrap($imageName); ?>"
            >
                <i class="bi bi-trash"></i>
                Delete Photo
            </button>

        <?php endif; ?>


        <!-- =========================================================
             HIDDEN DELETE CONFIRMATION
        ========================================================== -->

        <div
            id="registration-photo-delete-confirm"
            class="photo-delete-confirm"
            hidden
        >

            <p>
                Are you sure you want to delete this patient's profile
                photo?
            </p>

            <div class="photo-delete-actions">

                <button
                    type="button"
                    id="confirm-registration-delete"
                    class="btn btn-danger"
                >
                    Yes, Delete Photo
                </button>

                <button
                    type="button"
                    id="cancel-registration-delete"
                    class="btn btn-secondary"
                >
                    Cancel
                </button>

            </div>

        </div>


        <div
            id="registration-photo-message"
            class="ajax-message"
            hidden
        ></div>

    </div>

</div>
    
</div>
        
<div class="wizard-buttons">

<a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="btn btn-secondary">Cancel</a>

<button type="submit" class="btn btn-primary"> Next → </button>

</div>

</div>
    
</form>
    
</main>
    
</div>

<script>

const nigeriaLGAs =
<?php echo json_encode($lgas); ?>;

const savedState =
"<?php echo v_wrap($patient['state_of_origin'] ?? ''); ?>";

const savedLGA =
"<?php echo v_wrap($patient['lga'] ?? ''); ?>";

</script>
    
<?php include(SHARED_PATH . '/footer.php'); ?>
