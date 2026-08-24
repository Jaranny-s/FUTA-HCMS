<?php
require_once('../../private/config.php');
require_student_login();

$student = find_student_by_matric($_SESSION['student_matric']);

if (is_post_request()) {
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $type = $_POST['type'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if (is_blank($date) || is_blank($time) || is_blank($reason)) {
        $_SESSION['message'] = "Error: Please fill in all required fields.";
        redirect_to($_SERVER['HTTP_REFERER'] ?? url_wrap('/student/dashboard.php'));
    } else {
        // Basic validation: date shouldn't be in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $_SESSION['message'] = "Error: Appointment date cannot be in the past.";
            redirect_to($_SERVER['HTTP_REFERER'] ?? url_wrap('/student/dashboard.php'));
        } else {
            $result = book_student_appointment($student['id'], $date, $time, $type, $reason);
            if ($result) {
                $_SESSION['message'] = "Your appointment has been successfully requested. Please wait for approval or proceed to the clinic on the chosen date.";
                redirect_to(url_wrap('/student/dashboard.php'));
            } else {
                $_SESSION['message'] = "Error: Failed to book appointment. Please try again.";
                redirect_to($_SERVER['HTTP_REFERER'] ?? url_wrap('/student/dashboard.php'));
            }
        }
    }
} else {
    // If it's a GET request, just redirect to the dashboard
    // The user should only use the modal to book appointments
    redirect_to(url_wrap('/student/dashboard.php'));
}
?>
