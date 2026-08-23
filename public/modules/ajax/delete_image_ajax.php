<?php

require_once('../../../private/config.php');

header('Content-Type: application/json');


$id =
    $_POST['id'] ?? null;

$deleteImage =
    $_POST['delete_image'] ?? null;


if (
    !$id ||
    !$deleteImage
) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Get actual patient from database.
|--------------------------------------------------------------------------
*/

$patient =
    find_patient_by_id(
        (int)$id
    );


if (!$patient) {

    echo json_encode([
        'success' => false,
        'message' => 'Patient not found.'
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Security check:
| the requested image must actually belong to this patient.
|--------------------------------------------------------------------------
*/

if (
    empty($patient['profile_image']) ||
    $patient['profile_image'] !== $deleteImage
) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid patient image.'
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Delete physical file.
|--------------------------------------------------------------------------
*/

delete_patient_image(
    $deleteImage
);


/*
|--------------------------------------------------------------------------
| Replace with generic image.
|--------------------------------------------------------------------------
*/

update_patient_profile_image(
    (int)$id,
    'default_profile_pic.png'
);


echo json_encode([
    'success' => true,
    'message' => 'Profile image deleted successfully.'
]);