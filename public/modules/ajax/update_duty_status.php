<?php
require_once('../../../private/config.php');

header('Content-Type: application/json');

if (!isset($_SESSION['staff_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$allowedStatuses = ['Available', 'In Consultation', 'On Break', 'Off Duty'];
$newStatus = trim($_POST['status'] ?? $_GET['status'] ?? '');

if (!in_array($newStatus, $allowedStatuses)) {
    echo json_encode(['success' => false, 'error' => 'Invalid duty status value.']);
    exit;
}

$staffId = (int)$_SESSION['staff_id'];
$userRole = $_SESSION['staff_role'] ?? '';

// Allow admin/super_admin to update other doctors if specified
if (in_array($userRole, ['admin', 'super_admin']) && !empty($_POST['target_staff_id'])) {
    $staffId = (int)$_POST['target_staff_id'];
}

$stmt = $db_1->prepare("UPDATE staff SET duty_status = ? WHERE id = ?");
$stmt->bind_param("si", $newStatus, $staffId);

if ($stmt->execute()) {
    $stmt->close();
    // Also record last activity
    $_SESSION['LAST_ACTIVITY'] = time();
    if ($staffId === (int)$_SESSION['staff_id']) {
        $_SESSION['duty_status'] = $newStatus;
    }
    echo json_encode([
        'success' => true,
        'duty_status' => $newStatus,
        'staff_id' => $staffId,
        'message' => "Duty status changed to {$newStatus}"
    ]);
} else {
    $err = $db_1->error;
    $stmt->close();
    echo json_encode(['success' => false, 'error' => $err]);
}
