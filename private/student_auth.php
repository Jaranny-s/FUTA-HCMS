<?php

function log_in_student($patient) {
    session_regenerate_id();
    $_SESSION['student_id'] = $patient['id'];
    $_SESSION['last_login'] = time();
    $_SESSION['student_matric'] = $patient['matric_number'];
    return true;
}

function log_out_student() {
    unset($_SESSION['student_id']);
    unset($_SESSION['last_login']);
    unset($_SESSION['student_matric']);
    // session_destroy(); // Optional: destroy entire session
    return true;
}

function is_student_logged_in() {
    return isset($_SESSION['student_id']);
}

function require_student_login() {
    if (!is_student_logged_in()) {
        redirect_to(url_wrap('/index.php'));
    }
}
?>
