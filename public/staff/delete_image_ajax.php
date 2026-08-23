<?php
require_once('../../private/config.php');

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;
$delete_image = $_POST['delete_image'] ?? null;

if (!$id || !$delete_image) {
  echo json_encode([
    'success' => false,
    'message' => 'Invalid request'
  ]);
  exit;
}

delete_picture($id, $delete_image);

echo json_encode([
  'success' => true,
  'message' => 'Profile image deleted'
]);

?>
