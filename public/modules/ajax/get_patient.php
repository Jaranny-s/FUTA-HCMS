<?php
require_once('../../../private/config.php');

if (!isset($_SESSION['staff_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing ID']);
    exit;
}

$id = (int)$_GET['id'];
$patient = find_patient_by_id($id);

if (!$patient) {
    http_response_code(404);
    echo json_encode(['error' => 'Patient not found']);
    exit;
}

$emergency = find_primary_emergency_contact($id);
if ($emergency) {
    $patient['emergency_contact_name'] = $emergency['name'];
    $patient['emergency_contact_phone'] = $emergency['phone'];
    $patient['emergency_contact_relationship'] = $emergency['relationship'];
} else {
    $patient['emergency_contact_name'] = '';
    $patient['emergency_contact_phone'] = '';
    $patient['emergency_contact_relationship'] = '';
}

// Ensure non-null values for JSON safely
foreach ($patient as $key => $value) {
    if (is_null($value)) {
        $patient[$key] = '';
    }
}

header('Content-Type: application/json');
echo json_encode($patient);
?>
