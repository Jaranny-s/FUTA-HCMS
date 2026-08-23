<?php

require_once('../../../private/config.php');

header('Content-Type: application/json');


$imageName =
    $_POST['delete_image'] ?? null;


if (
    empty($imageName)
) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid image request.'
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Make sure the requested image is actually the image currently stored
| in this registration session.
|--------------------------------------------------------------------------
*/

$patientRegistration =
    $_SESSION['patient_registration'] ?? [];


$currentImage =
    $patientRegistration['profile_image']
    ?? null;


if (
    empty($currentImage) ||
    $currentImage !== $imageName
) {

    echo json_encode([
        'success' => false,
        'message' => 'Image does not belong to this registration.'
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Delete physical file.
|--------------------------------------------------------------------------
*/

delete_patient_image(
    $imageName
);


/*
|--------------------------------------------------------------------------
| Replace it with generic image.
|--------------------------------------------------------------------------
*/

$_SESSION['patient_registration']['profile_image'] =
    'default_profile_pic.png';


echo json_encode([
    'success' => true,
    'message' => 'Profile image deleted.'
]);

?>