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
    $patient['emergency_contact_name'] = $emergency['contact_name'] ?? $emergency['name'] ?? '';
    $patient['emergency_contact_phone'] = $emergency['phone'] ?? '';
    $patient['emergency_contact_relationship'] = $emergency['relationship'] ?? '';
} else {
    $patient['emergency_contact_name'] = $patient['next_of_kin_name'] ?? '';
    $patient['emergency_contact_phone'] = $patient['next_of_kin_phone'] ?? '';
    $patient['emergency_contact_relationship'] = $patient['next_of_kin_relationship'] ?? '';
}

// Check and attach principal patient details if Dependant has principal_patient_id
if (!empty($patient['principal_patient_id'])) {
    $principal = find_patient_by_id((int)$patient['principal_patient_id']);
    if ($principal) {
        $patient['principal_display'] = [
            'id' => (int)$principal['id'],
            'patient_id' => $principal['patient_id'] ?? '',
            'surname' => $principal['surname'] ?? '',
            'first_name' => $principal['first_name'] ?? '',
            'middle_name' => $principal['middle_name'] ?? '',
            'full_name' => trim(($principal['surname'] ?? '') . ' ' . ($principal['first_name'] ?? '') . ' ' . ($principal['middle_name'] ?? '')),
            'staff_number' => $principal['staff_number'] ?? '',
            'department' => $principal['department'] ?? '',
            'profile_image' => $principal['profile_image'] ?? ''
        ];
    }
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
