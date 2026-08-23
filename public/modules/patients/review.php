<?php

require_once('../../../private/config.php');

require_password_reset();

if(!hasPermission('create_patient')){
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Review Patient Registration";
$specificCss = "/assets/css/patient.css";

include(SHARED_PATH.'/header.php');

$currentStep = 6;

$defaultPatientImage = 'default_profile_pic.png';
$patient = $_SESSION['patient_registration'] ?? [];
$imageName =
    $patient['profile_image']
    ?? $defaultPatientImage;

?>

<div id="content">

<?php include(SHARED_PATH.'/navigation.php'); ?>

<main class="main-content">

<a
    href="<?php echo url_wrap('/modules/patients/emergency_information.php'); ?>"
    class="btn btn-primary"
    id="link_layout"
>
    ← Back
</a>

<div class="top">

<p class="top-head">Patient Registration</p>

<p class="top-description">
    Review all patient registration information
</p>

</div>

<?php

echo display_session_message();
echo display_errors($errors ?? []);

?>

<?php include(SHARED_PATH.'/patient_registration_progress.php'); ?>


<h2>Review Patient Information</h2>

<p class="review-description">
    Please carefully review the information entered. You can go back to
    any previous step to make corrections before registering the patient.
</p>
    
<!-- PROFILE PICTURE -->
 <div class="profile-picture-area">   
<?php if (!empty($patient['profile_image'])) { ?>

    <img
        src="<?php echo url_wrap('modules/patients/images/patient_pictures/' . v_wrap(ru_wrap($patient['profile_image']))); ?>"
        alt="Patient profile photo"
        class="patient-profile-image"
    > 
     
     <?php } else { ?>
    <img src="<?php echo url_wrap('/assets/images/' . v_wrap($imageName));?>"
                    alt="No Patient photo uploaded"
                    class="patient-profile-image"
                > 
    

<?php } ?>
    </div>

<!-- BASIC INFORMATION -->

<div class="review-section">

    <div class="review-section-header">

        <h3>Basic Information</h3>

        <a
            href="<?php echo url_wrap('/modules/patients/basic_information.php?edit=1'); ?>"
            class="review-edit"
        >
            Edit
        </a>

    </div>


    <div class="review-grid">

        <div class="review-item">
            <span class="review-label">Surname</span>
            <span class="review-value">
                <?php echo v_wrap($patient['surname'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">First Name</span>
            <span class="review-value">
                <?php echo v_wrap($patient['first_name'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Middle Name</span>
            <span class="review-value">
                <?php echo v_wrap($patient['middle_name'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Gender</span>
            <span class="review-value">
                <?php echo v_wrap($patient['gender'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Date of Birth</span>
            <span class="review-value">
                <?php echo v_wrap($patient['date_of_birth'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Nationality</span>
            <span class="review-value">
                <?php echo v_wrap($patient['nationality'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">State of Origin</span>
            <span class="review-value">
                <?php echo v_wrap($patient['state_of_origin'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">LGA</span>
            <span class="review-value">
                <?php echo v_wrap($patient['lga'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Marital Status</span>
            <span class="review-value">
                <?php echo v_wrap($patient['marital_status'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Patient Category</span>
            <span class="review-value">
                <?php echo v_wrap($patient['patient_category'] ?? 'Not provided'); ?>
            </span>
        </div>

    </div>

</div>


<!-- CONTACT INFORMATION -->

<div class="review-section">

    <div class="review-section-header">

        <h3>Contact Information</h3>

        <a
            href="<?php echo url_wrap('/modules/patients/contact_information.php?edit=1'); ?>"
            class="review-edit"
        >
            Edit
        </a>

    </div>


    <div class="review-grid">

        <div class="review-item">
            <span class="review-label">Phone</span>
            <span class="review-value">
                <?php echo v_wrap($patient['phone'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Alternate Phone</span>
            <span class="review-value">
                <?php echo v_wrap($patient['alternate_phone'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Email</span>
            <span class="review-value">
                <?php echo v_wrap($patient['email'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">City</span>
            <span class="review-value">
                <?php echo v_wrap($patient['city'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Residential State</span>
            <span class="review-value">
                <?php echo v_wrap($patient['residential_state'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item">
            <span class="review-label">Postal Code</span>
            <span class="review-value">
                <?php echo v_wrap($patient['postal_code'] ?? 'Not provided'); ?>
            </span>
        </div>


        <div class="review-item review-full-width">

            <span class="review-label">Address</span>

            <span class="review-value">
                <?php echo v_wrap($patient['address'] ?? 'Not provided'); ?>
            </span>

        </div>

    </div>

</div>


<!-- UNIVERSITY / EMPLOYMENT INFORMATION -->

<div class="review-section">

    <div class="review-section-header">

        <h3>University / Employment Information</h3>

        <a
            href="<?php echo url_wrap('/modules/patients/university_information.php?edit=1'); ?>"
            class="review-edit"
        >
            Edit
        </a>

    </div>


    <div class="review-grid">

        <?php if(($patient['patient_category'] ?? '') === 'Student'){ ?>

            <div class="review-item">
                <span class="review-label">Matric Number</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['matric_number'] ?? 'Not provided'); ?>
                </span>
            </div>

            <div class="review-item">
                <span class="review-label">Faculty</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['faculty'] ?? 'Not provided'); ?>
                </span>
            </div>

            <div class="review-item">
                <span class="review-label">Department</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['department'] ?? 'Not provided'); ?>
                </span>
            </div>

            <div class="review-item">
                <span class="review-label">Level</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['level'] ?? 'Not provided'); ?>
                </span>
            </div>

        <?php } elseif(($patient['patient_category'] ?? '') === 'Staff'){ ?>

            <div class="review-item">
                <span class="review-label">Staff Number</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['staff_number'] ?? 'Not provided'); ?>
                </span>
            </div>

            <div class="review-item">
                <span class="review-label">Position</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['position'] ?? 'Not provided'); ?>
                </span>
            </div>

        <?php } elseif(($patient['patient_category'] ?? '') === 'Dependant'){ ?>

            <div class="review-item">
                <span class="review-label">Relationship to Principal</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['relationship_to_principal'] ?? 'Not provided'); ?>
                </span>
            </div>

            <div class="review-item">
                <span class="review-label">Principal Patient</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['principal_patient_id'] ?? 'Not provided'); ?>
                </span>
            </div>

        <?php } elseif(($patient['patient_category'] ?? '') === 'External'){ ?>

            <div class="review-item">
                <span class="review-label">Occupation</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['occupation'] ?? 'Not provided'); ?>
                </span>
            </div>

            <div class="review-item">
                <span class="review-label">Employer</span>
                <span class="review-value">
                    <?php echo v_wrap($patient['employer'] ?? 'Not provided'); ?>
                </span>
            </div>

        <?php } ?>

    </div>

</div>


<!-- MEDICAL INFORMATION -->

<div class="review-section">

    <div class="review-section-header">

        <h3>Medical Information</h3>

        <a
            href="<?php echo url_wrap('/modules/patients/medical_information.php?edit=1'); ?>"
            class="review-edit"
        >
            Edit
        </a>

    </div>


    <div class="review-grid">

        <div class="review-item">

            <span class="review-label">Blood Group</span>

            <span class="review-value">
                <?php echo v_wrap($patient['blood_group'] ?? 'Not provided'); ?>
            </span>

        </div>


        <div class="review-item">

            <span class="review-label">Genotype</span>

            <span class="review-value">
                <?php echo v_wrap($patient['genotype'] ?? 'Not provided'); ?>
            </span>

        </div>


        <div class="review-item review-full-width">

            <span class="review-label">Allergies</span>

            <span class="review-value">
                <?php echo v_wrap($patient['allergies'] ?? 'Not provided'); ?>
            </span>

        </div>


        <div class="review-item review-full-width">

            <span class="review-label">Chronic Conditions</span>

            <span class="review-value">
                <?php echo v_wrap($patient['chronic_conditions'] ?? 'Not provided'); ?>
            </span>

        </div>


        <div class="review-item review-full-width">

            <span class="review-label">Disabilities</span>

            <span class="review-value">
                <?php echo v_wrap($patient['disabilities'] ?? 'Not provided'); ?>
            </span>

        </div>

    </div>

</div>


<!-- EMERGENCY INFORMATION -->

<div class="review-section">

    <div class="review-section-header">

        <h3>Emergency & Next-of-Kin Information</h3>

        <a
            href="<?php echo url_wrap('/modules/patients/emergency_information.php?edit=1'); ?>"
            class="review-edit"
        >
            Edit
        </a>

    </div>


    <div class="review-subsection">

        <h4>Emergency Contact</h4>

        <div class="review-grid">

            <div class="review-item">

                <span class="review-label">Name</span>

                <span class="review-value">
                    <?php echo v_wrap($patient['emergency_name'] ?? 'Not provided'); ?>
                </span>

            </div>


            <div class="review-item">

                <span class="review-label">Phone</span>

                <span class="review-value">
                    <?php echo v_wrap($patient['emergency_phone'] ?? 'Not provided'); ?>
                </span>

            </div>


            <div class="review-item">

                <span class="review-label">Relationship</span>

                <span class="review-value">
                    <?php echo v_wrap($patient['emergency_relationship'] ?? 'Not provided'); ?>
                </span>

            </div>

        </div>

    </div>


    <div class="review-subsection">

        <h4>Next of Kin</h4>

        <div class="review-grid">

            <div class="review-item">

                <span class="review-label">Name</span>

                <span class="review-value">
                    <?php echo v_wrap($patient['next_of_kin_name'] ?? 'Not provided'); ?>
                </span>

            </div>


            <div class="review-item">

                <span class="review-label">Phone</span>

                <span class="review-value">
                    <?php echo v_wrap($patient['next_of_kin_phone'] ?? 'Not provided'); ?>
                </span>

            </div>


            <div class="review-item">

                <span class="review-label">Relationship</span>

                <span class="review-value">
                    <?php echo v_wrap($patient['next_of_kin_relationship'] ?? 'Not provided'); ?>
                </span>

            </div>

        </div>

    </div>

</div>


<!-- FINAL SUBMISSION -->

<div class="review-submit-box">

    <p>
        Please make sure all information is correct before completing
        the registration.
    </p>

    <form
        method="post"
        action="<?php echo url_wrap('/patient/create.php'); ?>"
    >

        <button
            type="submit"
            class="btn btn-primary review-submit-btn"
        >
            Register Patient
        </button>

    </form>

</div>


</main>

</div>

<?php include(SHARED_PATH . '/footer.php'); ?>