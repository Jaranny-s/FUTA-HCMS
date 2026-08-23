<?php

require_once('../../../private/config.php');

require_password_reset();

if (!hasPermission('edit_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}


/*
|--------------------------------------------------------------------------
| Get Patient ID
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    redirect_to(
        url_wrap('/modules/patients/index.php')
    );
}

$id = (int)$id;


/*
|--------------------------------------------------------------------------
| Find Patient
|--------------------------------------------------------------------------
*/

$patient = find_patient_by_id($id);

if (!$patient) {
    $_SESSION['error'] =
        "The requested patient could not be found.";

    redirect_to(
        url_wrap('/modules/patients/index.php')
    );
}


/*
|--------------------------------------------------------------------------
| Find Primary Emergency Contact
|--------------------------------------------------------------------------
*/

$emergencyContact =
    find_primary_emergency_contact($id);

$principalPatientName = '';

if (!empty($patient['principal_patient_id'])) {

    $principalPatient =
        find_patient_by_id(
            (int)$patient['principal_patient_id']
        );

    if ($principalPatient) {

        $principalPatientName =
            $principalPatient['surname'] . ', ' .
            $principalPatient['first_name'];

        if (!empty($principalPatient['middle_name'])) {

            $principalPatientName .=
                ' ' . $principalPatient['middle_name'];
        }
    }
}

$defaultPatientImage = 'default_profile_pic.png';

$currentImage =
    $patient['profile_image']
    ?? $defaultPatientImage;


$newImageName =
    $currentImage;

/*
|--------------------------------------------------------------------------
| Handle Form Submission
|--------------------------------------------------------------------------
*/

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Collect submitted patient information
    |--------------------------------------------------------------------------
    */

    $patient['surname'] =
        trim($_POST['surname'] ?? '');

    $patient['first_name'] =
        trim($_POST['first_name'] ?? '');

    $patient['middle_name'] =
        trim($_POST['middle_name'] ?? '');

    $patient['gender'] =
        $_POST['gender'] ?? '';

    $patient['nationality'] =
        trim($_POST['nationality'] ?? '');

    $patient['state_of_origin'] =
        trim($_POST['state_of_origin'] ?? '');

    $patient['lga'] =
        trim($_POST['lga'] ?? '');

    $patient['marital_status'] =
        $_POST['marital_status'] ?? '';

    $patient['date_of_birth'] =
        $_POST['date_of_birth'] ?? '';
    
    $patient['profile_image'] =
    $newImageName;



    /*
    |--------------------------------------------------------------------------
    | Contact
    |--------------------------------------------------------------------------
    */

    $patient['phone'] =
        trim($_POST['phone'] ?? '');

    $patient['alternate_phone'] =
        trim($_POST['alternate_phone'] ?? '');

    $patient['email'] =
        trim($_POST['email'] ?? '');

    $patient['address'] =
        trim($_POST['address'] ?? '');

    $patient['city'] =
        trim($_POST['city'] ?? '');

    $patient['residential_state'] =
        trim($_POST['residential_state'] ?? '');

    $patient['postal_code'] =
        trim($_POST['postal_code'] ?? '');

    $patient['state'] =
        trim($_POST['state'] ?? '');



    /*
    |--------------------------------------------------------------------------
    | University / Employment
    |--------------------------------------------------------------------------
    */

    $patient['patient_category'] =
        $_POST['patient_category'] ?? '';

    $patient['matric_number'] =
        trim($_POST['matric_number'] ?? '');

    $patient['staff_number'] =
        trim($_POST['staff_number'] ?? '');

    $patient['faculty'] =
        trim($_POST['faculty'] ?? '');

    $patient['department'] =
        trim($_POST['department'] ?? '');

    $patient['level'] =
        trim($_POST['level'] ?? '');

    $patient['staff_position'] =
        trim($_POST['staff_position'] ?? '');

    $patient['occupation'] =
        trim($_POST['occupation'] ?? '');

    $patient['employer'] =
        trim($_POST['employer'] ?? '');

    $patient['relationship_to_principal'] =
        trim($_POST['relationship_to_principal'] ?? '');

    $principalPatientPublicId =
    trim($_POST['principal_patient_public_id'] ?? '');

$patient['principal_patient_id'] = null;


if ($principalPatientPublicId !== '') {

    $principalPatient =
        find_patient_by_patient_id(
            $principalPatientPublicId
        );

    if ($principalPatient) {

        $patient['principal_patient_id'] =
            (int)$principalPatient['id'];

    } else {

        $errors[] =
            "The selected principal patient could not be found.";
    }
}

/*
|--------------------------------------------------------------------------
| Prevent patient from being their own principal
|--------------------------------------------------------------------------
*/

if (
    $patient['principal_patient_id'] !== null &&
    (int)$patient['principal_patient_id'] === (int)$id
) {

    $errors[] =
        "A patient cannot be their own principal patient.";

    $patient['principal_patient_id'] = null;
}


    /*
    |--------------------------------------------------------------------------
    | Medical Information
    |--------------------------------------------------------------------------
    */

    $patient['blood_group'] =
        trim($_POST['blood_group'] ?? '');

    $patient['genotype'] =
        trim($_POST['genotype'] ?? '');

    $patient['allergies'] =
        trim($_POST['allergies'] ?? '');

    $patient['chronic_conditions'] =
        trim($_POST['chronic_conditions'] ?? '');

    $patient['disabilities'] =
        trim($_POST['disabilities'] ?? '');



    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    $patient['status'] =
        $_POST['status'] ?? '';



    /*
    |--------------------------------------------------------------------------
    | Emergency Contact
    |--------------------------------------------------------------------------
    */

    $patient['emergency_name'] =
        trim($_POST['emergency_name'] ?? '');

    $patient['emergency_phone'] =
        trim($_POST['emergency_phone'] ?? '');

    $patient['emergency_relationship'] =
        trim($_POST['emergency_relationship'] ?? '');



    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    $result = false;

if (empty($errors)) {
    
    /*
|--------------------------------------------------------------------------
| Patient profile image
|--------------------------------------------------------------------------
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

        $newImageName =
            $uploadResult['filename'];


        /*
        |------------------------------------------------------------------
        | Delete previous uploaded image after new upload succeeds.
        |------------------------------------------------------------------
        */

        if (
            !empty($currentImage) &&
            $currentImage !== $defaultPatientImage &&
            $currentImage !== $newImageName
        ) {

            delete_patient_image(
                $currentImage
            );

        }

    }

}

    $result = update_patient($patient);
    update_patient_profile_image(
    $id,
    $newImageName
);

}
else {

    /*
    |--------------------------------------------------------------
    | Keep validation errors on this page.
    | Do not attempt database update.
    |--------------------------------------------------------------
    */

    $result = $errors;

}


if ($result === true) {
    $_SESSION['message'] =
        "Patient information updated successfully!";

    redirect_to(
        url_wrap(
            '/modules/patients/view.php?id=' . $id
        )
    );

} else {

    $errors = $result;

    $emergencyContact = [
        'contact_name' =>
            $patient['emergency_name'],

        'phone' =>
            $patient['emergency_phone'],

        'relationship' =>
            $patient['emergency_relationship']
    ];
}
}


/*
|--------------------------------------------------------------------------
| Page Information
|--------------------------------------------------------------------------
*/

$page_title = v_wrap($patient['surname'] . ', ' . $patient['first_name']);

$specificCss = "/assets/css/patient-view.css";
$specificJs = "/assets/js/patient-view.js";

include(SHARED_PATH . '/header.php');

?>

<div id="content">

    <?php include(SHARED_PATH . '/navigation.php'); ?>

    <main class="main-content">

        <a
            href="javascript:history.back()"
            class="btn btn-primary"
            id="link_layout"
        >
            <i class="bi bi-arrow-left"></i>
            Back
        </a>


        <div class="top">

            <p class="top-head">
                Patient Edit
            </p>

            <p class="top-description">
                update information for an existing patient
            </p>

            <?php if (!empty($patient['profile_image'])) { ?>
            <img src="<?php echo url_wrap('modules/patients/images/patient_pictures/' . v_wrap(ru_wrap($patient['profile_image']))); ?>" alt="Patient profile photo" class="patient-profile-header">
            <?php if ($currentImage !== $patient['profile_image']) {
            $currentImage == $defaultPatientImage;
                }?>
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($newImageName));?>" alt="No Patient photo uploaded" class="patient-profile-header">
            <?php } ?>
        </div>


        <?php if (!empty($errors)): ?>

            <div class="errors">

                <?php foreach ($errors as $error): ?>

                    <p>
                        <?php echo v_wrap($error); ?>
                    </p>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

<?php echo display_session_message(); ?>
        
        
        
        <div class="role-head">

            <?php
            echo v_wrap(
                $patient['surname'] . ', ' .
                $patient['first_name']
            );
            ?>

            — Edit Patient Information

        </div>


        <!-- =====================================================
             TABS
        ====================================================== -->

        <div class="tabs" role="tablist">

            <button
                role="tab"
                class="tab-btn active"
                aria-selected="true"
                aria-controls="patient-basic"
                data-tab="patient-basic"
            >
                Basic Information
            </button>

            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="patient-contact"
                data-tab="patient-contact"
            >
                Contact Information
            </button>

            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="patient-affiliation"
                data-tab="patient-affiliation"
            >
                University / Employment
            </button>

            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="patient-medical"
                data-tab="patient-medical"
            >
                Medical Information
            </button>

            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="patient-emergency"
                data-tab="patient-emergency"
            >
                Emergency Contact
            </button>

            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="patient-status"
                data-tab="patient-status"
            >
                Status
            </button>

            <button
                id="patient-delete-tab-btn"
                type="button"
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="patient-pic-delete"
                data-tab="patient-pic-delete"
                style="display:none;"
            >
                Delete Picture
            </button>
            
        </div>


        <form
            action="<?php echo v_wrap($_SERVER['PHP_SELF']); ?>?id=<?php echo $id; ?>"
            method="post" enctype="multipart/form-data"
        >


            <!-- =================================================
                 BASIC INFORMATION
            ================================================== -->

            <div
                id="patient-basic"
                class="tab-content active"
                role="tabpanel"
            >

                <dl>
                    <dt>Surname:</dt>
                    <dd>
                        <input
                            type="text"
                            name="surname"
                            value="<?php echo v_wrap($patient['surname']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>First Name:</dt>
                    <dd>
                        <input
                            type="text"
                            name="first_name"
                            value="<?php echo v_wrap($patient['first_name']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Middle Name:</dt>
                    <dd>
                        <input
                            type="text"
                            name="middle_name"
                            value="<?php echo v_wrap($patient['middle_name']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Gender:</dt>
                    <dd>

                        <select name="gender">

                            <option value="">
                                Select gender
                            </option>

                            <option value="Male" <?php if ($patient['gender'] === 'Male') {echo 'selected';}?>> Male</option>

                            <option
                                value="Female" <?php if ($patient['gender'] === 'Female') {
                                    echo 'selected';
                                }
                                ?>
                            > Female
                            </option>

                        </select>

                    </dd>
                </dl>


                <dl>
                    <dt>Nationality:</dt>
                    <dd>
                        <input
                            type="text"
                            name="nationality"
                            value="<?php echo v_wrap($patient['nationality']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>State of Origin:</dt>
                    <dd>
                        <input
                            type="text"
                            name="state_of_origin"
                            value="<?php echo v_wrap($patient['state_of_origin']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>LGA:</dt>
                    <dd>
                        <input
                            type="text"
                            name="lga"
                            value="<?php echo v_wrap($patient['lga']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Marital Status:</dt>
                    <dd>

                        <select name="marital_status">

                            <option value="">
                                Select marital status
                            </option>

                            <?php
                            $maritalOptions = [
                                'Single',
                                'Married',
                                'Divorced',
                                'Widowed'
                            ];

                            foreach ($maritalOptions as $option):
                            ?>

                                <option
                                    value="<?php echo $option; ?>"
                                    <?php
                                    if (
                                        $patient['marital_status']
                                        === $option
                                    ) {
                                        echo 'selected';
                                    }
                                    ?>
                                >
                                    <?php echo $option; ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </dd>
                </dl>


                <dl>
                    <dt>Date of Birth:</dt>
                    <dd>
                        <input
                            type="date"
                            name="date_of_birth"
                            value="<?php echo v_wrap($patient['date_of_birth']); ?>"
                        >
                    </dd>
                </dl>

                <dl>

    <dt>Patient Photo:</dt>

    <dd>

        <div id="patient-edit-photo-area">

            <img
                id="patient-edit-photo"
                src="<?php
                    echo url_wrap(
                        '/images/patient_pictures/' .
                        v_wrap($currentImage)
                    );
                ?>"
                alt="Patient profile photo"
                class="patient-profile-image"
            >


            <p id="patient-edit-photo-name">

                <?php if (
                    $currentImage !== $defaultPatientImage
                ): ?>

                    Current photo:
                    <?php
                    echo v_wrap($currentImage);
                    ?>

                <?php else: ?>

                    
                    No personal photo uploaded.

                <?php endif; ?>

            </p>


            <input
                type="file"
                name="profile_image"
                accept=".jpg,.jpeg,.png,.webp"
            >


            <?php if (
                $currentImage !== $defaultPatientImage
            ): ?>

                <span
                    id="open-patient-delete-tab"
                    data-id="<?php echo (int)$id; ?>"
                    data-image="<?php
                        echo v_wrap($currentImage);
                    ?>"
                    style="cursor:pointer;"
                    title="Delete patient photo"
                >
                    <i class="bi bi-trash text-danger"></i>
                </span>

            <?php endif; ?>

        </div>

    </dd>

</dl>
            </div>


            <!-- =================================================
                 CONTACT INFORMATION
            ================================================== -->

            <div
                id="patient-contact"
                class="tab-content"
                role="tabpanel"
                hidden
            >

                <dl>
                    <dt>Phone:</dt>
                    <dd>
                        <input
                            type="text"
                            name="phone"
                            value="<?php echo v_wrap($patient['phone']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Alternate Phone:</dt>
                    <dd>
                        <input
                            type="text"
                            name="alternate_phone"
                            value="<?php echo v_wrap($patient['alternate_phone']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Email:</dt>
                    <dd>
                        <input
                            type="email"
                            name="email"
                            value="<?php echo v_wrap($patient['email']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Address:</dt>
                    <dd>
                        <textarea name="address"><?php
                            echo v_wrap($patient['address']);
                        ?></textarea>
                    </dd>
                </dl>


                <dl>
                    <dt>City:</dt>
                    <dd>
                        <input
                            type="text"
                            name="city"
                            value="<?php echo v_wrap($patient['city']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Residential State:</dt>
                    <dd>
                        <input
                            type="text"
                            name="residential_state"
                            value="<?php echo v_wrap($patient['residential_state']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Postal Code:</dt>
                    <dd>
                        <input
                            type="text"
                            name="postal_code"
                            value="<?php echo v_wrap($patient['postal_code']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>State:</dt>
                    <dd>
                        <input
                            type="text"
                            name="state"
                            value="<?php echo v_wrap($patient['state']); ?>"
                        >
                    </dd>
                </dl>

            </div>


            <!-- =================================================
                 UNIVERSITY / EMPLOYMENT
            ================================================== -->

            <div
                id="patient-affiliation"
                class="tab-content"
                role="tabpanel"
                hidden
            >

                <dl>
                    <dt>Patient Category:</dt>
                    <dd>
                        <input
                            type="text"
                            name="patient_category"
                            value="<?php echo v_wrap($patient['patient_category']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Matric Number:</dt>
                    <dd>
                        <input
                            type="text"
                            name="matric_number"
                            value="<?php echo v_wrap($patient['matric_number']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Staff Number:</dt>
                    <dd>
                        <input
                            type="text"
                            name="staff_number"
                            value="<?php echo v_wrap($patient['staff_number']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Faculty:</dt>
                    <dd>
                        <input
                            type="text"
                            name="faculty"
                            value="<?php echo v_wrap($patient['faculty']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Department:</dt>
                    <dd>
                        <input
                            type="text"
                            name="department"
                            value="<?php echo v_wrap($patient['department']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Level:</dt>
                    <dd>
                        <input
                            type="text"
                            name="level"
                            value="<?php echo v_wrap($patient['level']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Staff Position:</dt>
                    <dd>
                        <input
                            type="text"
                            name="staff_position"
                            value="<?php echo v_wrap($patient['staff_position']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Occupation:</dt>
                    <dd>
                        <input
                            type="text"
                            name="occupation"
                            value="<?php echo v_wrap($patient['occupation']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Employer:</dt>
                    <dd>
                        <input
                            type="text"
                            name="employer"
                            value="<?php echo v_wrap($patient['employer']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Relationship to Principal:</dt>
                    <dd>
                        <input
                            type="text"
                            name="relationship_to_principal"
                            value="<?php echo v_wrap($patient['relationship_to_principal']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
    <dt>Principal Patient:</dt>

    <dd>

        <?php

        $principalPatient = null;

        if (!empty($patient['principal_patient_id'])) {

            $principalPatient = find_patient_by_id(
                (int)$patient['principal_patient_id']
            );
        }

        ?>

        <div class="principal-search">

            <!-- SEARCH BOX -->
            <div class="principal-search-input">

                <i class="bi bi-search"></i>

                <input
    type="text"
    id="principal_patient_search"
    placeholder="Search patient by name..."
    autocomplete="off"
    data-current-id="<?php echo $id; ?>"
    data-search-url="<?php echo url_wrap('/modules/patients/search_principal.php'); ?>">

            </div>


            <!-- AJAX SEARCH RESULTS -->
            <div
                id="principal-search-results"
                class="principal-search-results"
            ></div>


            <!-- CURRENTLY SELECTED PRINCIPAL -->
            <div
                id="selected-principal-patient"
                class="selected-principal-patient"
            >

                <?php if ($principalPatient): ?>

                    <div class="selected-principal">

                        <span>
                            <?php
                            echo v_wrap(
                                $principalPatient['surname'] . ', ' .
                                $principalPatient['first_name'] . ' ' .
                                ($principalPatient['middle_name'] ?? '')
                            );
                            ?>
                        </span>

                        <button
                            type="button"
                            id="clear-principal-patient"
                            class="clear-principal"
                            aria-label="Remove principal patient"
                        >
                            &times;
                        </button>

                    </div>

                <?php endif; ?>

            </div>


            <!-- ONLY VALUE SUBMITTED TO PHP -->
            <input
                type="hidden"
                name="principal_patient_public_id"
                id="principal_patient_public_id"
                value="<?php

                    if ($principalPatient) {

                        echo v_wrap(
                            $principalPatient['patient_id']
                        );

                    }

                ?>"
            >

        </div>

    </dd>
</dl>
            </div>


            <!-- =================================================
                 MEDICAL INFORMATION
            ================================================== -->

            <div
                id="patient-medical"
                class="tab-content"
                role="tabpanel"
                hidden
            >

                <dl>
                    <dt>Blood Group:</dt>
                    <dd>
                        <input
                            type="text"
                            name="blood_group"
                            value="<?php echo v_wrap($patient['blood_group']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Genotype:</dt>
                    <dd>
                        <input
                            type="text"
                            name="genotype"
                            value="<?php echo v_wrap($patient['genotype']); ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Allergies:</dt>
                    <dd>
                        <textarea name="allergies"><?php
                            echo v_wrap($patient['allergies']);
                        ?></textarea>
                    </dd>
                </dl>


                <dl>
                    <dt>Chronic Conditions:</dt>
                    <dd>
                        <textarea name="chronic_conditions"><?php
                            echo v_wrap($patient['chronic_conditions']);
                        ?></textarea>
                    </dd>
                </dl>


                <dl>
                    <dt>Disabilities:</dt>
                    <dd>
                        <textarea name="disabilities"><?php
                            echo v_wrap($patient['disabilities']);
                        ?></textarea>
                    </dd>
                </dl>

            </div>


            <!-- =================================================
                 EMERGENCY CONTACT
            ================================================== -->

            <div
                id="patient-emergency"
                class="tab-content"
                role="tabpanel"
                hidden
            >

                <dl>
                    <dt>Contact Name:</dt>
                    <dd>
                        <input
                            type="text"
                            name="emergency_name"
                            value="<?php
                                echo v_wrap(
                                    $emergencyContact['contact_name']
                                    ?? ''
                                );
                            ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Phone:</dt>
                    <dd>
                        <input
                            type="text"
                            name="emergency_phone"
                            value="<?php
                                echo v_wrap(
                                    $emergencyContact['phone']
                                    ?? ''
                                );
                            ?>"
                        >
                    </dd>
                </dl>


                <dl>
                    <dt>Relationship:</dt>
                    <dd>
                        <input
                            type="text"
                            name="emergency_relationship"
                            value="<?php
                                echo v_wrap(
                                    $emergencyContact['relationship']
                                    ?? ''
                                );
                            ?>"
                        >
                    </dd>
                </dl>

            </div>


            <!-- =================================================
                 STATUS
            ================================================== -->

            <div
                id="patient-status"
                class="tab-content"
                role="tabpanel"
                hidden
            >

                <dl>
                    <dt>Patient Status:</dt>

                    <dd>

                        <select name="status">

                            <option
                                value="Active"
                                <?php
                                if ($patient['status'] === 'Active') {
                                    echo 'selected';
                                }
                                ?>
                            >
                                Active
                            </option>

                            <option
                                value="Inactive"
                                <?php
                                if ($patient['status'] === 'Inactive') {
                                    echo 'selected';
                                }
                                ?>
                            >
                                Inactive
                            </option>
                            
                            <option
                                value="Archived"
                                <?php
                                if ($patient['status'] === 'Archived') {
                                    echo 'selected';
                                }
                                ?>
                            >
                                Archived
                            </option>
                            
                            <option
                                value="Deceased"
                                <?php
                                if ($patient['status'] === 'Deceased') {
                                    echo 'selected';
                                }
                                ?>
                            >
                                Deceased
                            </option>

                        </select>

                    </dd>
                </dl>


                <div class="edit-actions">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Patient Changes
                    </button>

                    <a
                        href="<?php
                            echo url_wrap(
                                '/modules/patients/view.php?id=' . $id
                            );
                        ?>"
                        class="btn btn-primary"
                    >
                        Cancel
                    </a>

                </div>
                
            </div>
            
            <div
                id="patient-pic-delete"
                class="tab-content"
                hidden
                role="tabpanel"
            >

                <p>
                    Are you sure you want to delete this patient's profile image?
                </p>

                <div id="submit-response">

                    <button
                        type="button"
                        id="confirm-patient-delete"
                        class="btn btn-danger"
                    >
                        Yes, Delete Image
                    </button>

                    <button
                        type="button"
                        id="cancel-patient-delete"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </button>

                </div>

            </div>

        </form>

    </main>

</div>

<?php include(SHARED_PATH . '/footer.php'); ?>