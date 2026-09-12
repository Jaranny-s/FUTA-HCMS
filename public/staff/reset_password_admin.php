<?php
require_once('../../private/config.php');

require_password_reset(); // user must be logged in

// Only admin or super_admin should do this
$canAdminReset = (isset($_SESSION['role_id']) && (int)$_SESSION['role_id'] === 1) || ($_SESSION['staff_role'] ?? '') === 'super_admin';
if (!$canAdminReset) {
    redirect_to(url_wrap('/staff/dashboard.php'));
    exit;
}

$staff_id = $_POST['staff_id'] ?? null;

if (!$staff_id) {
    redirect_to(url_wrap('/staff/index.php'));
    exit;
}

$targetStaff = find_staff_by_id($staff_id);
if (!$targetStaff) {
    $_SESSION['error'] = "Staff account not found.";
    redirect_to(url_wrap('/staff/index.php'));
    exit;
}

// Protect super_admin account: only super_admin can reset super_admin password
if (($targetStaff['role'] === 'super_admin' || $targetStaff['role_id'] == 6) && ($_SESSION['staff_role'] ?? '') !== 'super_admin') {
    $_SESSION['error'] = "Unauthorized: Only Super Admin can reset password for Super Admin accounts.";
    redirect_to(url_wrap('/staff/view.php?id=' . $staff_id));
    exit;
}

// Generate temp password
$temp_password = generate_temp_password();
$hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);

// Update DB
$sql = "UPDATE staff SET password = ?, password_reset_required = 1 WHERE id = ?";
$query = $db_1->prepare($sql);
$query->bind_param("si", $hashed_password, $staff_id);
$query->execute();

// Log it
$actorId = $_SESSION['staff_id'];
logAction($actorId, 'ADMIN_RESET_PASSWORD', 'staff', $staff_id);

// Store temp password to show admin
$_SESSION['temp_password'] = $temp_password;
$_SESSION['message'] = "Password reset successfully.";

// Redirect back to view page
redirect_to(url_wrap('/staff/view.php?id=' . $staff_id));
