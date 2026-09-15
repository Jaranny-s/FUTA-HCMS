<?php
require_once('../../../private/config.php');

header('Content-Type: application/json');

if (isset($_SESSION['staff_id']) || isset($_SESSION['student_id'])) {
    $_SESSION['LAST_ACTIVITY'] = time();
    echo json_encode([
        'status' => 'alive',
        'role' => $_SESSION['staff_role'] ?? 'student',
        'timestamp' => time()
    ]);
} else {
    http_response_code(401);
    echo json_encode(['status' => 'unauthenticated']);
}
