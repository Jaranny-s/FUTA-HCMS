<?php
require_once('../../private/config.php');
require_student_login();

$student = find_student_by_matric($_SESSION['student_matric']);
$errors = [];

if (is_post_request()) {
    $date = $_POST['date'] ?? '';
    $time = $_POST['time'] ?? '';
    $type = $_POST['type'] ?? '';
    $reason = $_POST['reason'] ?? '';
    
    if (is_blank($date) || is_blank($time) || is_blank($reason)) {
        $errors[] = "Please fill in all required fields.";
    } else {
        // Basic validation: date shouldn't be in the past
        if (strtotime($date) < strtotime(date('Y-m-d'))) {
            $errors[] = "Appointment date cannot be in the past.";
        } else {
            $result = book_student_appointment($student['id'], $date, $time, $type, $reason);
            if ($result) {
                $_SESSION['message'] = "Your appointment has been successfully requested. Please wait for approval or proceed to the clinic on the chosen date.";
                redirect_to(url_wrap('/student/dashboard.php'));
            } else {
                $errors[] = "Failed to book appointment. Please try again.";
            }
        }
    }
}

$page_title = 'Book Appointment';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo v_wrap($page_title); ?> - FUTA HCMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/student_portal.css">
</head>
<body>

    <header class="portal-header">
        <div class="brand">
            <img src="../assets/images/futa_logo.png" width="40" height="40" alt="FUTA Logo">
            FUTA HCMS Student Portal
        </div>
        <div class="user-menu">
            <a href="dashboard.php" style="color:#555; text-decoration:none; margin-right:15px;">Dashboard</a>
            <span><i class="bi bi-person-circle"></i> <?php echo v_wrap($student['first_name']); ?></span>
        </div>
    </header>

    <div class="portal-container">
        
        <div class="dashboard-grid">
            <div class="main-column">
                <div class="card">
                    <h3><i class="bi bi-calendar-plus"></i> Book a New Appointment</h3>
                    
                    <?php if (!empty($errors)) { ?>
                        <div style="color: #d93025; background: #fce8e6; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                            <?php foreach($errors as $err) echo v_wrap($err) . "<br>"; ?>
                        </div>
                    <?php } ?>

                    <form action="book.php" method="post">
                        <div class="form-group">
                            <label>Date of Visit *</label>
                            <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Preferred Time *</label>
                            <input type="time" name="time" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Type of Visit *</label>
                            <select name="type" required>
                                <option value="Consultation">General Consultation</option>
                                <option value="Medical Examination">Medical Examination (Clearance)</option>
                                <option value="Follow-up">Follow-up</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Reason for Visit *</label>
                            <textarea name="reason" rows="4" placeholder="Briefly describe why you are visiting the health centre..." required></textarea>
                        </div>

                        <button type="submit" class="action-btn primary" style="font-size:1.1rem;">Submit Appointment Request</button>
                    </form>
                </div>
            </div>

            <?php include(SHARED_PATH . '/student_sidebar.php'); ?>

        </div>

    </div>

</body>
</html>
