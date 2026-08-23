<?php

require_once('../../private/config.php');

require_password_reset();

if (!hasPermission('create_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

/*
|--------------------------------------------------------------------------
| Only allow POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to(url_wrap('/modules/patients/basic_information.php'));
}


/*
|--------------------------------------------------------------------------
| Make sure a registration session exists
|--------------------------------------------------------------------------
*/

$patient = $_SESSION['patient_registration'] ?? [];

if (empty($patient)) {
    redirect_to(url_wrap('/modules/patients/basic_information.php'));
}


/*
|--------------------------------------------------------------------------
| Database transaction
|--------------------------------------------------------------------------
*/

mysqli_begin_transaction($db_1);

try {

    /*
    |--------------------------------------------------------------------------
    | Generate Patient ID
    |--------------------------------------------------------------------------
    |
    | The database ID is still the primary key.
    | patient_id is the human-readable hospital ID.
    |
    */

    $patientId = generate_patient_id();


    /*
    |--------------------------------------------------------------------------
    | Basic Information
    |--------------------------------------------------------------------------
    */

    $surname = trim($patient['surname'] ?? '');
    $firstName = trim($patient['first_name'] ?? '');
    $middleName = trim($patient['middle_name'] ?? '');

    $gender = $patient['gender'] ?? null;
    $nationality = $patient['nationality'] ?? null;
    $stateOfOrigin = $patient['state_of_origin'] ?? null;
    $lga = $patient['lga'] ?? null;
    $maritalStatus = $patient['marital_status'] ?? null;
    $dateOfBirth = $patient['date_of_birth'] ?? null;

    $patientCategory = $patient['patient_category'] ?? null;
    $profileImage =
    !empty($patient['profile_image'])
        ? trim($patient['profile_image'])
        : null;

    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */

    $phone = trim($patient['phone'] ?? '');
    $alternatePhone = trim($patient['alternate_phone'] ?? '');
    $email = trim($patient['email'] ?? '');
    $address = trim($patient['address'] ?? '');
    $city = trim($patient['city'] ?? '');
    $residentialState = trim($patient['residential_state'] ?? '');
    $postalCode = trim($patient['postal_code'] ?? '');
    $state = trim($patient['state'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | University / Employment Information
    |--------------------------------------------------------------------------
    */

    $matricNumber = trim($patient['matric_number'] ?? '');
    $staffNumber = trim($patient['staff_number'] ?? '');
    $faculty = trim($patient['faculty'] ?? '');
    $department = trim($patient['department'] ?? '');
    $level = trim($patient['level'] ?? '');
    $staffPosition = trim($patient['position'] ?? '');

    $occupation = trim($patient['occupation'] ?? '');
    $employer = trim($patient['employer'] ?? '');

    $relationshipToPrincipal =
        trim($patient['relationship_to_principal'] ?? '');

    $principalPatientId =
        !empty($patient['principal_patient_id'])
        ? (int)$patient['principal_patient_id']
        : null;


    /*
    |--------------------------------------------------------------------------
    | Medical Information
    |--------------------------------------------------------------------------
    */

    $bloodGroup = trim($patient['blood_group'] ?? '');
    $genotype = trim($patient['genotype'] ?? '');
    $allergies = trim($patient['allergies'] ?? '');
    $chronicConditions = trim($patient['chronic_conditions'] ?? '');
    $disabilities = trim($patient['disabilities'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Next of Kin
    |--------------------------------------------------------------------------
    */

    $nextOfKinName =
        trim($patient['next_of_kin_name'] ?? '');

    $nextOfKinPhone =
        trim($patient['next_of_kin_phone'] ?? '');

    $nextOfKinRelationship =
        trim($patient['next_of_kin_relationship'] ?? '');

     /*
    | Status must be defined before execute.
    */

    $status = 'Active';
    
    /*
    |--------------------------------------------------------------------------
    | Registration information
    |--------------------------------------------------------------------------
    */

    $registrationSource = 'Manual';

    /*
    | Change this to however the authentication system stores
    | the currently logged-in staff member's database ID.
    */

    $registeredBy = $_SESSION['staff_id'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | Insert Patient
    |--------------------------------------------------------------------------
    */

    $sql = "
        INSERT INTO patients (
            patient_id,
            surname,
            first_name,
            middle_name,
            gender,
            nationality,
            state_of_origin,
            lga,
            marital_status,
            date_of_birth,
            phone,
            alternate_phone,
            email,
            address,
            city,
            residential_state,
            postal_code,
            state,
            patient_category,
            matric_number,
            staff_number,
            faculty,
            department,
            level,
            staff_position,
            blood_group,
            genotype,
            allergies,
            chronic_conditions,
            disabilities,
            next_of_kin_name,
            next_of_kin_phone,
            next_of_kin_relationship,
            registration_source,
            registered_by,
            status,
            occupation,
            employer,
            relationship_to_principal,
            principal_patient_id,
            profile_image
        )
        VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";

    $stmt = mysqli_prepare($db_1, $sql);

    if (!$stmt) {
        throw new Exception(
            'Failed to prepare patient registration.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Bind Patient Values
    |--------------------------------------------------------------------------
    */

    mysqli_stmt_bind_param(
        $stmt,
        "ssssssssssssssssssssssssssssssssssissssis",
        $patientId,
        $surname,
        $firstName,
        $middleName,
        $gender,
        $nationality,
        $stateOfOrigin,
        $lga,
        $maritalStatus,
        $dateOfBirth,
        $phone,
        $alternatePhone,
        $email,
        $address,
        $city,
        $residentialState,
        $postalCode,
        $state,
        $patientCategory,
        $matricNumber,
        $staffNumber,
        $faculty,
        $department,
        $level,
        $staffPosition,
        $bloodGroup,
        $genotype,
        $allergies,
        $chronicConditions,
        $disabilities,
        $nextOfKinName,
        $nextOfKinPhone,
        $nextOfKinRelationship,
        $registrationSource,
        $registeredBy,
        $status,
        $occupation,
        $employer,
        $relationshipToPrincipal,
        $principalPatientId,
        $patient['profile_image']
    );

   

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------------------
    | Get the newly created patient's internal database ID
    |--------------------------------------------------------------------------
    */

    $patientDatabaseId = mysqli_insert_id($db_1);


    /*
|--------------------------------------------------------------------------
| Emergency Contact
|--------------------------------------------------------------------------
|
| The registration wizard currently collects one emergency contact.
| It is stored in the patient_emergency_contacts table so that
| multiple contacts can be supported later.
|
*/

$emergencyName =
    trim($patient['emergency_name'] ?? '');

$emergencyPhone =
    trim($patient['emergency_phone'] ?? '');

$emergencyRelationship =
    trim($patient['emergency_relationship'] ?? '');


if (
    $emergencyName !== '' ||
    $emergencyPhone !== '' ||
    $emergencyRelationship !== ''
) {

    $emergencySql = "
        INSERT INTO patient_emergency_contacts (
            patient_id,
            contact_name,
            relationship,
            phone,
            is_primary
        )
        VALUES (?, ?, ?, ?, ?)
    ";

    $emergencyStmt =
        mysqli_prepare($db_1, $emergencySql);

    if (!$emergencyStmt) {
        throw new Exception(
            'Failed to prepare emergency contact registration: '
            . mysqli_error($db_1)
        );
    }

    $isPrimary = 1;

    mysqli_stmt_bind_param(
        $emergencyStmt,
        "isssi",
        $patientDatabaseId,
        $emergencyName,
        $emergencyRelationship,
        $emergencyPhone,
        $isPrimary
    );

    if (!mysqli_stmt_execute($emergencyStmt)) {

        throw new Exception(
            'Failed to save emergency contact: '
            . mysqli_stmt_error($emergencyStmt)
        );

    }

    mysqli_stmt_close($emergencyStmt);
}

    /*
    |--------------------------------------------------------------------------
    | Commit everything
    |--------------------------------------------------------------------------
    */

    mysqli_commit($db_1);


    /*
    |--------------------------------------------------------------------------
    | Clear registration session
    |--------------------------------------------------------------------------
    */

    unset($_SESSION['patient_registration']);


    /*
    |--------------------------------------------------------------------------
    | Success message
    |--------------------------------------------------------------------------
    */

    $_SESSION['success'] =
        "Patient registered successfully. Patient ID: " . $patientId;


    redirect_to(
        url_wrap('/modules/patients/index.php')
    );

} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | Roll back everything
    |--------------------------------------------------------------------------
    */

    mysqli_rollback($db_1);


    /*
    |--------------------------------------------------------------------------
    | Log the actual error
    |--------------------------------------------------------------------------
    */

    error_log(
        'Patient registration failed: ' . $e->getMessage()
    );


    /*
    |--------------------------------------------------------------------------
    | Preserve the registration session
    |--------------------------------------------------------------------------
    |
    | This is important.
    | If something fails, the staff member doesn't lose everything
    | they entered into the wizard.
    |
    */

    $_SESSION['error'] =
        "Patient registration could not be completed. Please check the information and try again.";


    redirect_to(
        url_wrap('/modules/patients/review.php')
    );
}
?>
