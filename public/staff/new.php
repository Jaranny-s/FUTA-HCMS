<?php 
require_once('../../private/config.php'); 

require_password_reset();

if (!hasPermission('create_staff')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

?>

<?php 
    // TO UPLOAD IMAGES
    $image_name = null;

if (!empty($_FILES['profile_image']['name'])) {

    $original_name = $_FILES['profile_image']['name'];
    $tmp_path = $_FILES['profile_image']['tmp_name'];

    // Get file extension
    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

    // Allow only images
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];

    if (in_array($ext, $allowed)) {

        // Create unique filename
        $image_name = 'staff_' . time() . '.' . $ext;

        $destination = __DIR__ . '/images/staff_pictures/' . $image_name;

        move_uploaded_file($tmp_path, $destination);
    }
}
 ?>
<?php 
if(is_post_request()) {
    
    $staff = [];
    $staff['full_name'] = $_POST['full_name'] ?? '';
    $staff['email'] = $_POST['email'] ?? '';
    $staff['hashed_password'] = $_POST['password'] ?? '';
    $staff['confirm_password'] = $_POST['confirm_password'] ?? '';
    $staff['role'] = $_POST['role'] ?? '';
    $staff['department'] = $_POST['department'] ?? '';
    $staff['profile_image'] = $image_name ?? '';
    $staff['password_reset_required'] = 1 ?? '';
    
    
    
    $result = insert_staff($staff);
    if (is_array($result) && isset($result['success']) && $result['success']) {

    $new_id = $result['id'];

    $_SESSION['message'] = "Staff created successfully!";
    $_SESSION['temp_password'] = $result['temp_password'];

    redirect_to(url_wrap('/staff/view.php?id=' . $new_id));

    } else {
        // INSERT failed
        $_SESSION['error'] = implode("<br>", $errors);
        redirect_to($_SERVER['HTTP_REFERER'] ?? url_wrap('/staff/index.php'));
    }
} else {
    // If it's a GET request, just redirect to the index
    redirect_to(url_wrap('/staff/index.php'));
}
?>
