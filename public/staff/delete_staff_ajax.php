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

$result = delete_staff($id);

echo json_encode($result);
?>
