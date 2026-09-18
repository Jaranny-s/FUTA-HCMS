<?php

  // Performs all actions necessary to log in an staff
  function log_in_staff($staff) {
  // Renerating the ID protects the staff from session fixation.
    session_regenerate_id();
    $_SESSION['staff_id'] = $staff['id'];
    $_SESSION['last_login'] = time();
    $_SESSION['email'] = $staff['email'];
    $_SESSION['role_id'] = $staff['role_id'];
    $_SESSION['staff_role'] = $staff['role'];
      
    $actorId = $_SESSION['staff_id'] ?? $staff['id']; // fallback if no login yet
    logAction($actorId, 'LOGIN', 'staff', $staff['id']);

    return true;
  }

// Performs all actions necessary to log out a staff member
  function log_out_staff($staff = null) {
    $actorId = $_SESSION['staff_id'] ?? ($staff['id'] ?? null);
    $entityId = ($staff && isset($staff['id'])) ? $staff['id'] : $actorId;
      
    if ($actorId) {
        logAction($actorId, 'LOGOUT', 'staff', $entityId);
    }

    unset($_SESSION['staff_id']);
    unset($_SESSION['last_login']);
    unset($_SESSION['email']);
    unset($_SESSION['role_id']);
    unset($_SESSION['staff_role']);
    session_destroy();
    return true;
  }

// is_logged_in() contains all the logic for determining if a
// request should be considered a "logged in" request or not.
// It is the core of require_login() but it can also be called
// on its own in other contexts (e.g. display one link if an staff
// is logged in and display another link if they are not)

function is_logged_in() {
  // Its presence indicates the staff is logged in.

  return isset($_SESSION['staff_id']);
}

// Call require_login() at the top of any page which needs to
// require a valid login before granting acccess to the page.
//function require_login() {
 // if(!is_logged_in()) {
 //   redirect_to(url_wrap('/staff/login.php'));
 // } else {
  // Do nothing, let the rest of the page proceed 
//  }
//}



// Call require_password_reset() at the top of any page which needs to
// confirm that the user is logged in and checks if that user needs a password 
// reset before granting acccess to the page.
function require_password_reset() {
    global $db_1;

    if (!is_logged_in()) {
        redirect_to(url_wrap('/staff/login.php'));
    }

    $sql = "SELECT password_reset_required, status FROM staff WHERE id = ?";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $_SESSION['staff_id']);
    $query->execute();
    $query->bind_result($reset_required, $status);
    $query->fetch();
    $query->close();

    // Check if account has been deactivated
    if (!empty($status) && strtolower($status) === 'inactive') {
        $actorId = $_SESSION['staff_id'];
        logAction($actorId, 'KICKED_INACTIVE', 'staff', $actorId);
        unset($_SESSION['staff_id']);
        unset($_SESSION['last_login']);
        unset($_SESSION['email']);
        unset($_SESSION['role_id']);
        unset($_SESSION['staff_role']);
        $_SESSION['error'] = "Your account has been deactivated. Please contact an administrator.";
        redirect_to(url_wrap('/staff/login.php'));
    }

    if ($reset_required == 1) {
        redirect_to(url_wrap('/staff/reset_password.php'));
    }
}




// This checks if the logged in user has 
// permission to be on the current page
//  or perform the activity it protects
function hasPermission($permissionName) {
    global $db_1;

    if (isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'super_admin') {
        return true;
    }

    $role_id = $_SESSION['role_id'];

    $sql = "SELECT p.name ";
    $sql .= "FROM role_permissions rp ";
    $sql .= "JOIN permissions p ON rp.permission_id = p.id ";
    $sql .= "WHERE rp.role_id = ? AND p.name = ?";

    $query = $db_1->prepare($sql);
    $query->bind_param("is", $role_id, $permissionName);
    $query->execute();


    $result = $query->get_result();

    return $result->num_rows > 0;
}

/**
 * Sets a 4-to-6 digit Supervisor Reverification PIN for break-glass actions.
 */
function set_staff_reverification_pin($staff_id, $pin) {
    global $db_1;
    $clean_pin = trim($pin);
    if (!preg_match('/^\d{4,6}$/', $clean_pin)) {
        return false;
    }
    $hashed = password_hash($clean_pin, PASSWORD_BCRYPT);
    $stmt = $db_1->prepare("UPDATE staff SET reverification_pin = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed, $staff_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && function_exists('logAction')) {
        logAction($staff_id, 'SET_REVERIFICATION_PIN', 'staff', $staff_id);
    }
    return $success;
}

/**
 * Verifies a 4-to-6 digit Supervisor Reverification PIN.
 * Falls back to account login password if PIN has not yet been configured.
 */
function verify_staff_reverification_pin($staff_id, $pin) {
    global $db_1;
    $stmt = $db_1->prepare("SELECT reverification_pin, password FROM staff WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();

    if (!$row) return false;

    if (!empty($row['reverification_pin'])) {
        return password_verify(trim($pin), $row['reverification_pin']);
    }

    // Fallback: If PIN not set, verify against login password
    return password_verify(trim($pin), $row['password']);
}

/**
 * Checks if a staff member has configured their Reverification PIN.
 */
function has_staff_reverification_pin($staff_id) {
    global $db_1;
    $stmt = $db_1->prepare("SELECT reverification_pin FROM staff WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $stmt->close();
    return !empty($row['reverification_pin']);
}
?>
