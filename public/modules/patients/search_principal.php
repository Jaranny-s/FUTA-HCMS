<?php

require_once('../../../private/config.php');

header('Content-Type: application/json; charset=utf-8');


/*
|--------------------------------------------------------------------------
| Request Method
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Search Term
|--------------------------------------------------------------------------
*/

$search =
    trim($_GET['q'] ?? '');


/*
|--------------------------------------------------------------------------
| Current Patient ID
|--------------------------------------------------------------------------
|
| This is the INTERNAL database id of the patient currently being edited.
|
*/

$currentPatientId =
    filter_input(
        INPUT_GET,
        'current_id',
        FILTER_VALIDATE_INT
    );


/*
|--------------------------------------------------------------------------
| Validate Search
|--------------------------------------------------------------------------
*/

if (
    $search === '' ||
    mb_strlen($search) < 2
) {

    echo json_encode([
        'success' => true,
        'patients' => []
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Validate Current Patient
|--------------------------------------------------------------------------
*/

if (
    !$currentPatientId ||
    $currentPatientId <= 0
) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid current patient.'
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

$patients =
    search_patients_for_principal(
        $search,
        $currentPatientId
    );


/*
|--------------------------------------------------------------------------
| Response
|--------------------------------------------------------------------------
*/

echo json_encode([
    'success' => true,
    'patients' => $patients
]);

exit;
