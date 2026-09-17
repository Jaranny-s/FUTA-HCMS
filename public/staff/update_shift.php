<?php
require_once('../../private/config.php');
require_password_reset();

$current_role = $_SESSION['staff_role'] ?? '';
if (!in_array($current_role, ['admin', 'super_admin'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }
    set_session_message("Access denied. Only administrators can assign shifts.", "error");
    redirect_to(url_wrap('/staff/index.php'));
}

if (is_post_request()) {
    $doctor_id = (int)($_POST['doctor_id'] ?? 0);
    $shift = trim($_POST['shift'] ?? '');

    if ($doctor_id > 0 && !empty($shift)) {
        $success = assign_doctor_shift($doctor_id, $shift, $_SESSION['staff_id']);
        if ($success) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Shift updated successfully!']);
                exit;
            }
            set_session_message("Doctor shift updated successfully to {$shift}!", "success");
        } else {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to update shift.']);
                exit;
            }
            set_session_message("Failed to update doctor shift.", "error");
        }
    }
}

redirect_to(url_wrap('/staff/index.php?role=doctor'));
?>
