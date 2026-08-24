<?php
require_once('../../../private/config.php');
require_password_reset();

if (!hasPermission('create_patient')) {
    $_SESSION['error'] = "Access Denied: You do not have permission to register patients.";
    redirect_to(url_wrap('/modules/patients/index.php'));
}

if (is_post_request()) {
    $patient = $_POST;
    $patient['status'] = 'Active';
    $patient['profile_image'] = null;

    // Handle Image Upload
    if (!empty($_FILES['profile_image']['name'])) {
        $original_name = $_FILES['profile_image']['name'];
        $tmp_path = $_FILES['profile_image']['tmp_name'];
        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($ext, $allowed)) {
            $image_name = 'patient_' . time() . '.' . $ext;
            $destination = __DIR__ . '/images/patient_pictures/' . $image_name;
            move_uploaded_file($tmp_path, $destination);
            $patient['profile_image'] = $image_name;
        }
    }

    $result = insert_patient($patient);
    if ($result['success'] === true) {
        $_SESSION['message'] = "Patient registered successfully!";
        redirect_to(url_wrap('/modules/patients/index.php'));
    } else {
        $_SESSION['error'] = "Failed to register patient: " . implode(" ", $result);
        redirect_to(url_wrap('/modules/patients/index.php'));
    }
} else {
    redirect_to(url_wrap('/modules/patients/index.php'));
}
?>
