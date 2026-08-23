<?php

require_once('../../../private/config.php');

require_password_reset();

if (!hasPermission('view_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}


/*
|--------------------------------------------------------------------------
| Get Patient
|--------------------------------------------------------------------------
*/

$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "Invalid patient record.";
    redirect_to(url_wrap('/modules/patients/index.php'));
}

$patient = find_patient_by_id((int)$id);

if (!$patient) {
    $_SESSION['error'] = "Patient record could not be found.";
    redirect_to(url_wrap('/modules/patients/index.php'));
}


/*
|--------------------------------------------------------------------------
| Emergency Contact
|--------------------------------------------------------------------------
*/

$emergencyContact = find_primary_emergency_contact((int)$patient['id']);


/*
|--------------------------------------------------------------------------
| Principal Patient
|--------------------------------------------------------------------------
|
| Only relevant when this patient is a dependant.
|
*/

$principalPatient = null;

if (
    $patient['patient_category'] === 'Dependant' &&
    !empty($patient['principal_patient_id'])
) {
    $principalPatient = find_patient_by_id(
        (int)$patient['principal_patient_id']
    );
}


/*
|--------------------------------------------------------------------------
| Page Settings
|--------------------------------------------------------------------------
*/

$page_title = v_wrap(
    $patient['surname'] . ' ' . $patient['first_name']
);

$specificCss = "/assets/css/patient-view.css";
$specificJs = "/assets/js/patient-view.js";
$defaultPatientImage = 'default_profile_pic.png';

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
            <i class="bi bi-arrow-left"></i> Back
        </a>


        <div class="top">

            <p class="top-head">
                Patient Records
            </p>

            <p class="top-description">
                view of patient information and medical records
            </p>

            <?php if (!empty($patient['profile_image'])) { ?>
            <img src="<?php echo url_wrap('modules/patients/images/patient_pictures/' . v_wrap(ru_wrap($patient['profile_image']))); ?>" alt="Patient profile photo" class="patient-profile-header">
            <?php } else { ?>
            <img src="<?php echo url_wrap('/assets/images/' . v_wrap($defaultPatientImage));?>" alt="No Patient photo uploaded" class="patient-profile-header">
            <?php } ?>
            
        </div>


<div><?php echo display_session_message(); ?></div>

        <div class="role-head">

            <?php
            echo v_wrap(
                $patient['surname'] . ' ' .
                $patient['first_name'] . ' ' .
                $patient['middle_name']
            );
            ?>

            's Details

        </div>

        

        <div class="patient-summary">

            <div>
                <strong>Patient ID</strong>
                <span>
                    <?php echo v_wrap($patient['patient_id']); ?>
                </span>
            </div>

            <div>
                <strong>Category</strong>
                <span>
                    <?php echo v_wrap($patient['patient_category']); ?>
                </span>
            </div>

            <div>
                <strong>Status</strong>
                <span>
                    <?php echo v_wrap($patient['status']); ?>
                </span>
            </div>
            
            <div style="text-align: right;">
                <a href="<?php echo url_wrap('/modules/reception/check_in.php?patient_id=' . $patient['id']); ?>" class="btn btn-primary" style="background:#1bc03d; color:white; border:none; padding:10px 15px; border-radius:5px; text-decoration:none;">
                    <i class="bi bi-calendar-check"></i> Check-in Patient
                </a>
            </div>
            
        </div>


        <div class="tabs" role="tablist">

            <button
                role="tab"
                class="tab-btn active"
                aria-selected="true"
                aria-controls="patient-information"
                data-tab="patient-information"
            >
                Patient Information
            </button>


            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="contact-information"
                data-tab="contact-information"
            >
                Contact Information
            </button>


            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="university-information"
                data-tab="university-information"
            >
                University / Employment
            </button>


            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="medical-information"
                data-tab="medical-information"
            >
                Medical Information
            </button>


            <button
                role="tab"
                class="tab-btn"
                aria-selected="false"
                aria-controls="emergency-information"
                data-tab="emergency-information"
            >
                Emergency & Next of Kin
            </button>

        </div>


        <!-- =========================================================
             PATIENT INFORMATION
        ========================================================== -->

        <div
            id="patient-information"
            class="tab-content active"
            role="tabpanel"
        >

            <h3>Basic Information</h3>

            <dl>
                <dt>Patient ID:</dt>
                <dd><?php echo v_wrap($patient['patient_id']); ?></dd>
            </dl>

            <dl>
                <dt>Surname:</dt>
                <dd><?php echo v_wrap($patient['surname']); ?></dd>
            </dl>

            <dl>
                <dt>First Name:</dt>
                <dd><?php echo v_wrap($patient['first_name']); ?></dd>
            </dl>

            <dl>
                <dt>Middle Name:</dt>
                <dd><?php echo v_wrap($patient['middle_name']); ?></dd>
            </dl>

            <dl>
                <dt>Gender:</dt>
                <dd><?php echo v_wrap($patient['gender']); ?></dd>
            </dl>

            <dl>
                <dt>Date of Birth:</dt>
                <dd><?php echo v_wrap($patient['date_of_birth']); ?></dd>
            </dl>

            <dl>
                <dt>Nationality:</dt>
                <dd><?php echo v_wrap($patient['nationality']); ?></dd>
            </dl>

            <dl>
                <dt>State of Origin:</dt>
                <dd><?php echo v_wrap($patient['state_of_origin']); ?></dd>
            </dl>

            <dl>
                <dt>LGA:</dt>
                <dd><?php echo v_wrap($patient['lga']); ?></dd>
            </dl>

            <dl>
                <dt>Marital Status:</dt>
                <dd><?php echo v_wrap($patient['marital_status']); ?></dd>
            </dl>


            <?php if ($patient['patient_category'] === 'Dependant') { ?>

                <hr>

                <h3>Dependant Information</h3>

                <dl>
                    <dt>Relationship to Principal:</dt>
                    <dd>
                        <?php
                        echo v_wrap(
                            $patient['relationship_to_principal']
                        );
                        ?>
                    </dd>
                </dl>

                <dl>
                    <dt>Principal Patient:</dt>
                    <dd>

                        <?php if ($principalPatient) { ?>

                            <?php
                            echo v_wrap(
                                $principalPatient['patient_id']
                            );
                            ?>

                            —
                            <?php
                            echo v_wrap(
                                $principalPatient['surname'] .
                                ' ' .
                                $principalPatient['first_name']
                            );
                            ?>

                        <?php } else { ?>

                            Not available

                        <?php } ?>

                    </dd>
                </dl>

            <?php } ?>

        </div>


        <!-- =========================================================
             CONTACT INFORMATION
        ========================================================== -->

        <div
            id="contact-information"
            class="tab-content"
            role="tabpanel"
            hidden
        >

            <h3>Contact Information</h3>

            <dl>
                <dt>Phone:</dt>
                <dd><?php echo v_wrap($patient['phone']); ?></dd>
            </dl>

            <dl>
                <dt>Alternate Phone:</dt>
                <dd><?php echo v_wrap($patient['alternate_phone']); ?></dd>
            </dl>

            <dl>
                <dt>Email:</dt>
                <dd><?php echo v_wrap($patient['email']); ?></dd>
            </dl>

            <dl>
                <dt>Address:</dt>
                <dd><?php echo v_wrap($patient['address']); ?></dd>
            </dl>

            <dl>
                <dt>City:</dt>
                <dd><?php echo v_wrap($patient['city']); ?></dd>
            </dl>

            <dl>
                <dt>Residential State:</dt>
                <dd><?php echo v_wrap($patient['residential_state']); ?></dd>
            </dl>

            <dl>
                <dt>Postal Code:</dt>
                <dd><?php echo v_wrap($patient['postal_code']); ?></dd>
            </dl>

        </div>


        <!-- =========================================================
             UNIVERSITY / EMPLOYMENT
        ========================================================== -->

        <div
            id="university-information"
            class="tab-content"
            role="tabpanel"
            hidden
        >

            <?php if ($patient['patient_category'] === 'Student') { ?>

                <h3>Student Information</h3>

                <dl>
                    <dt>Matric Number:</dt>
                    <dd><?php echo v_wrap($patient['matric_number']); ?></dd>
                </dl>

                <dl>
                    <dt>Faculty:</dt>
                    <dd><?php echo v_wrap($patient['faculty']); ?></dd>
                </dl>

                <dl>
                    <dt>Department:</dt>
                    <dd><?php echo v_wrap($patient['department']); ?></dd>
                </dl>

                <dl>
                    <dt>Level:</dt>
                    <dd><?php echo v_wrap($patient['level']); ?></dd>
                </dl>


            <?php } elseif ($patient['patient_category'] === 'Staff') { ?>

                <h3>Staff Information</h3>

                <dl>
                    <dt>Staff Number:</dt>
                    <dd><?php echo v_wrap($patient['staff_number']); ?></dd>
                </dl>

                <dl>
                    <dt>Position:</dt>
                    <dd><?php echo v_wrap($patient['staff_position']); ?></dd>
                </dl>


            <?php } elseif ($patient['patient_category'] === 'External') { ?>

                <h3>Employment Information</h3>

                <dl>
                    <dt>Occupation:</dt>
                    <dd><?php echo v_wrap($patient['occupation']); ?></dd>
                </dl>

                <dl>
                    <dt>Employer:</dt>
                    <dd><?php echo v_wrap($patient['employer']); ?></dd>
                </dl>


            <?php } elseif ($patient['patient_category'] === 'Dependant') { ?>

                <h3>Dependant Information</h3>

                <dl>
                    <dt>Relationship to Principal:</dt>
                    <dd>
                        <?php
                        echo v_wrap(
                            $patient['relationship_to_principal']
                        );
                        ?>
                    </dd>
                </dl>

                <dl>
                    <dt>Principal Patient:</dt>
                    <dd>

                        <?php if ($principalPatient) { ?>

                            <?php
                            echo v_wrap(
                                $principalPatient['patient_id']
                            );
                            ?>

                            —
                            <?php
                            echo v_wrap(
                                $principalPatient['surname'] .
                                ' ' .
                                $principalPatient['first_name']
                            );
                            ?>

                        <?php } else { ?>

                            Not available

                        <?php } ?>

                    </dd>
                </dl>


            <?php } else { ?>

                <p>No university or employment information was provided.</p>

            <?php } ?>

        </div>


        <!-- =========================================================
             MEDICAL INFORMATION
        ========================================================== -->

        <div
            id="medical-information"
            class="tab-content"
            role="tabpanel"
            hidden
        >

            <h3>Medical Information</h3>

            <dl>
                <dt>Blood Group:</dt>
                <dd><?php echo v_wrap($patient['blood_group']); ?></dd>
            </dl>

            <dl>
                <dt>Genotype:</dt>
                <dd><?php echo v_wrap($patient['genotype']); ?></dd>
            </dl>

            <dl>
                <dt>Allergies:</dt>
                <dd><?php echo v_wrap($patient['allergies']); ?></dd>
            </dl>

            <dl>
                <dt>Chronic Conditions:</dt>
                <dd>
                    <?php
                    echo v_wrap(
                        $patient['chronic_conditions']
                    );
                    ?>
                </dd>
            </dl>

            <dl>
                <dt>Disabilities:</dt>
                <dd>
                    <?php
                    echo v_wrap(
                        $patient['disabilities']
                    );
                    ?>
                </dd>
            </dl>

        </div>


        <!-- =========================================================
             EMERGENCY & NEXT OF KIN
        ========================================================== -->

        <div
            id="emergency-information"
            class="tab-content"
            role="tabpanel"
            hidden
        >

            <h3>Emergency Contact</h3>

            <?php if ($emergencyContact) { ?>

                <dl>
                    <dt>Name:</dt>
                    <dd>
                        <?php
                        echo v_wrap(
                            $emergencyContact['contact_name']
                        );
                        ?>
                    </dd>
                </dl>

                <dl>
                    <dt>Relationship:</dt>
                    <dd>
                        <?php
                        echo v_wrap(
                            $emergencyContact['relationship']
                        );
                        ?>
                    </dd>
                </dl>

                <dl>
                    <dt>Phone:</dt>
                    <dd>
                        <?php
                        echo v_wrap(
                            $emergencyContact['phone']
                        );
                        ?>
                    </dd>
                </dl>

            <?php } else { ?>

                <p>No emergency contact information was provided.</p>

            <?php } ?>


            <hr>


            <h3>Next of Kin</h3>

            <dl>
                <dt>Name:</dt>
                <dd>
                    <?php
                    echo v_wrap(
                        $patient['next_of_kin_name']
                    );
                    ?>
                </dd>
            </dl>

            <dl>
                <dt>Phone:</dt>
                <dd>
                    <?php
                    echo v_wrap(
                        $patient['next_of_kin_phone']
                    );
                    ?>
                </dd>
            </dl>

            <dl>
                <dt>Relationship:</dt>
                <dd>
                    <?php
                    echo v_wrap(
                        $patient['next_of_kin_relationship']
                    );
                    ?>
                </dd>
            </dl>

        </div>


    </main>

</div>

<?php include(SHARED_PATH . '/footer.php'); ?>