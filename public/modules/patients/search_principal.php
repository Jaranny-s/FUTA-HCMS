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

$currentPatientId = isset($_GET['current_id']) ? (int)$_GET['current_id'] : 0;

if ($search === '' || mb_strlen($search) < 2) {
    echo json_encode([
        'success' => true,
        'patients' => []
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
