<?php
require_once('../../../private/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['staff_id']) && !isset($_SESSION['staff_role'])) {
    echo json_encode(['found' => false, 'error' => 'Unauthorized']);
    exit;
}

$matric = trim($_GET['matric_number'] ?? $_POST['matric_number'] ?? '');
$staffNumber = trim($_GET['staff_number'] ?? $_POST['staff_number'] ?? '');
$phone = trim($_GET['phone'] ?? $_POST['phone'] ?? '');
$email = trim($_GET['email'] ?? $_POST['email'] ?? '');
$surname = trim($_GET['surname'] ?? $_POST['surname'] ?? '');
$firstName = trim($_GET['first_name'] ?? $_POST['first_name'] ?? '');
$dob = trim($_GET['date_of_birth'] ?? $_POST['date_of_birth'] ?? '');
$excludeId = (int)($_GET['exclude_id'] ?? $_POST['exclude_id'] ?? 0);

$matchFound = null;
$matchReason = '';

// 1. Check Matric Number
if (!empty($matric) && strlen($matric) >= 4) {
    $stmt = $db_1->prepare("SELECT id, patient_id, surname, first_name, middle_name, phone, email, patient_category, status, matric_number, staff_number, department, created_at FROM patients WHERE UPPER(matric_number) = UPPER(?) AND id != ? LIMIT 1");
    $stmt->bind_param("si", $matric, $excludeId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $matchFound = $row;
        $matchReason = 'Matriculation Number (' . $matric . ')';
    }
    $stmt->close();
}

// 2. Check Staff Number
if (!$matchFound && !empty($staffNumber) && strlen($staffNumber) >= 3) {
    $stmt = $db_1->prepare("SELECT id, patient_id, surname, first_name, middle_name, phone, email, patient_category, status, matric_number, staff_number, department, created_at FROM patients WHERE UPPER(staff_number) = UPPER(?) AND id != ? LIMIT 1");
    $stmt->bind_param("si", $staffNumber, $excludeId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $matchFound = $row;
        $matchReason = 'Staff ID / Number (' . $staffNumber . ')';
    }
    $stmt->close();
}

// 2. Check Email
if (!$matchFound && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $stmt = $db_1->prepare("SELECT id, patient_id, surname, first_name, middle_name, phone, email, patient_category, status, matric_number, department, created_at FROM patients WHERE LOWER(email) = LOWER(?) AND id != ? LIMIT 1");
    $stmt->bind_param("si", $email, $excludeId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $matchFound = $row;
        $matchReason = 'Email Address (' . $email . ')';
    }
    $stmt->close();
}

// 3. Check Phone (clean digits)
if (!$matchFound && !empty($phone) && strlen(preg_replace('/[^0-9]/', '', $phone)) >= 9) {
    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    $stmt = $db_1->prepare("SELECT id, patient_id, surname, first_name, middle_name, phone, email, patient_category, status, matric_number, department, created_at FROM patients WHERE (REPLACE(phone, ' ', '') LIKE ? OR REPLACE(alternate_phone, ' ', '') LIKE ?) AND id != ? LIMIT 1");
    $phoneParam = '%' . substr($cleanPhone, -9) . '%';
    $stmt->bind_param("ssi", $phoneParam, $phoneParam, $excludeId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $matchFound = $row;
        $matchReason = 'Phone Number (' . $phone . ')';
    }
    $stmt->close();
}

// 4. Check Surname + First Name + DOB
if (!$matchFound && !empty($surname) && !empty($firstName) && !empty($dob) && $dob !== '2000-01-01') {
    $stmt = $db_1->prepare("SELECT id, patient_id, surname, first_name, middle_name, phone, email, patient_category, status, matric_number, department, created_at FROM patients WHERE LOWER(surname) = LOWER(?) AND LOWER(first_name) = LOWER(?) AND date_of_birth = ? AND id != ? LIMIT 1");
    $stmt->bind_param("sssi", $surname, $firstName, $dob, $excludeId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $matchFound = $row;
        $matchReason = 'Full Name & Date of Birth (' . $surname . ' ' . $firstName . ', DOB: ' . $dob . ')';
    }
    $stmt->close();
}

if ($matchFound) {
    echo json_encode([
        'found' => true,
        'reason' => $matchReason,
        'patient' => [
            'id' => (int)$matchFound['id'],
            'patient_id' => $matchFound['patient_id'],
            'surname' => $matchFound['surname'],
            'first_name' => $matchFound['first_name'],
            'middle_name' => $matchFound['middle_name'] ?? '',
            'full_name' => trim($matchFound['first_name'] . ' ' . ($matchFound['middle_name'] ? $matchFound['middle_name'] . ' ' : '') . $matchFound['surname']),
            'patient_category' => $matchFound['patient_category'],
            'status' => $matchFound['status'],
            'matric_number' => $matchFound['matric_number'] ?? '',
            'staff_number' => $matchFound['staff_number'] ?? '',
            'department' => $matchFound['department'] ?? '',
            'phone' => $matchFound['phone'],
            'email' => $matchFound['email'],
            'registered_date' => date('d M Y', strtotime($matchFound['created_at']))
        ]
    ]);
} else {
    echo json_encode(['found' => false]);
}
