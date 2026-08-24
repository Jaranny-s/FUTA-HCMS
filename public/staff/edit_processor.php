<?php
require_once "../../private/config.php";

require_password_reset();

if (!isset($_SESSION["staff_id"])) {
  redirect_to(url_wrap("/staff/login.php"));
}

if (is_post_request()) {
  $staff_id = $_SESSION["staff_id"];
  $current_staff = find_staff_by_id($staff_id);
  
  $staff = [];
  $staff["id"] = $staff_id;
  $staff["full_name"] = $_POST["full_name"] ?? $current_staff['full_name'];
  $staff["email"] = $_POST["email"] ?? $current_staff['email'];
  // Dashboard doesn't edit passwords, role, or status directly
  $staff["role"] = $current_staff['role'];
  $staff["department"] = $_POST["department"] ?? $current_staff['department'];
  $staff["status"] = $current_staff['status'];
  
  $new_image_name = $current_staff['profile_image'];

  if (!empty($_FILES["profile_image"]["name"])) {
    $tmp = $_FILES["profile_image"]["tmp_name"];
    $ext = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
    $allowed = ["jpg", "jpeg", "png", "webp"];

    if (in_array($ext, $allowed)) {
      $new_image_name = "staff_" . time() . "." . $ext;
      $destination = __DIR__ . "/images/staff_pictures/" . $new_image_name;
      move_uploaded_file($tmp, $destination);

      if (!empty($current_staff['profile_image']) && file_exists(__DIR__ . "/images/staff_pictures/" . $current_staff['profile_image'])) {
        unlink(__DIR__ . "/images/staff_pictures/" . $current_staff['profile_image']);
      }
    }
  }
  
  $staff["profile_image"] = $new_image_name;

  $result = update_staff($staff);
  if ($result === true) {
    $_SESSION["message"] = "Profile updated successfully!";
  } else {
    $_SESSION["error"] = "Failed to update profile: " . implode(" ", $result);
  }
  
  redirect_to(url_wrap("/staff/dashboard.php"));
} else {
  redirect_to(url_wrap("/staff/dashboard.php"));
}
?>
