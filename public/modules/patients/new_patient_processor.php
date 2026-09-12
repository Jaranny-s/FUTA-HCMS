<?php
require_once('../../../private/config.php');
require_password_reset();

$allowedRoles = ['receptionist', 'admin', 'super_admin'];
if (!in_array($_SESSION['staff_role'] ?? '', $allowedRoles) && !hasPermission('create_patient')) {
    $_SESSION['error'] = "Access Denied: You do not have permission to register patients.";
    redirect_to(url_wrap('/modules/patients/index.php'));
}

if (is_post_request()) {
    $patient = $_POST;
    $patient['status'] = 'Active';
    $patient['profile_image'] = null;

    // Handle Image Upload
    if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = upload_patient_image($_FILES['profile_image']);
        if (!$uploadResult['success']) {
            $_SESSION['error'] = $uploadResult['error'];
            redirect_to(url_wrap('/modules/patients/index.php'));
        } else {
            $patient['profile_image'] = $uploadResult['filename'];
        }
    }

    $result = insert_patient($patient);
    if (isset($result['success']) && $result['success'] === true) {
        $_SESSION['message'] = "Patient registered successfully!";
        redirect_to(url_wrap('/modules/patients/index.php'));
    } else {
        $msg = is_array($result) ? implode(" ", $result) : "An unexpected error occurred.";
        $_SESSION['error'] = "Failed to register patient: " . $msg;
        redirect_to(url_wrap('/modules/patients/index.php'));
    }
} else {
    redirect_to(url_wrap('/modules/patients/index.php'));
}
?>
