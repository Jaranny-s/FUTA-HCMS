<?php
require_once('../../../private/config.php');
require_password_reset();

if (!hasPermission('edit_patient')) {
    $_SESSION['error'] = "Access Denied: You do not have permission to edit patients.";
    redirect_to(url_wrap('/modules/patients/index.php'));
}

if (is_post_request()) {
    $patient = $_POST;
    $id = (int)$_POST['id'];
    
    // Fetch current image to know if we need to replace it
    $existing = find_patient_by_id($id);
    if (!$existing) {
        $_SESSION['error'] = "Patient not found.";
        redirect_to(url_wrap('/modules/patients/index.php'));
    }
    
    $patient['profile_image'] = $existing['profile_image'];

    // Handle Image Upload
    if (!empty($_FILES['profile_image']['name']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadResult = upload_patient_image($_FILES['profile_image']);
        if (!$uploadResult['success']) {
            $_SESSION['error'] = $uploadResult['error'];
            redirect_to(url_wrap('/modules/patients/index.php'));
        } else {
            $patient['profile_image'] = $uploadResult['filename'];
            
            // Delete old image
            if (!empty($existing['profile_image']) && $existing['profile_image'] !== 'default_profile_pic.png') {
                delete_patient_image($existing['profile_image']);
            }
        }
    }

    $result = update_patient($patient);
    if ($result === true) {
        update_patient_profile_image($id, $patient['profile_image']);
        $_SESSION['message'] = "Patient updated successfully!";
        redirect_to(url_wrap('/modules/patients/index.php'));
    } else {
        $_SESSION['error'] = "Failed to update patient.";
        redirect_to(url_wrap('/modules/patients/index.php'));
    }
} else {
    redirect_to(url_wrap('/modules/patients/index.php'));
}
?>
