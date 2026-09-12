<?php
require_once('../../private/config.php');
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request.'
  ]);
  exit;
}

if (!hasPermission('delete_staff')) {
    echo json_encode([
      'success' => false,
      'message' => 'Unauthorized access.'
    ]);
    exit;
}

$targetStaff = find_staff_by_id($id);
if (!$targetStaff) {
    echo json_encode(['success' => false, 'message' => 'Staff not found.']);
    exit;
}

if (($targetStaff['role'] === 'super_admin' || $targetStaff['role_id'] == 6) && ($_SESSION['staff_role'] ?? '') !== 'super_admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Only Super Admin can delete Super Admin accounts.']);
    exit;
}

$result = delete_staff($id);

echo json_encode($result);
?>
