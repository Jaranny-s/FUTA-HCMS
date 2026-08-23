<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<div class="sidebar">
    <div class="card">
        <h3><i class="bi bi-grid-1x2"></i> Navigation</h3>
        <a href="<?php echo url_wrap('/student/dashboard.php'); ?>" class="action-btn <?php if($current_page == 'dashboard.php') echo 'active'; ?>">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <a href="<?php echo url_wrap('/student/book.php'); ?>" class="action-btn <?php if($current_page == 'book.php') echo 'active'; ?> primary">
            <i class="bi bi-calendar-plus"></i> Book Appointment
        </a>
        <a href="<?php echo url_wrap('/student/appointments.php'); ?>" class="action-btn <?php if($current_page == 'appointments.php') echo 'active'; ?>">
            <i class="bi bi-calendar2-check"></i> My Appointments
        </a>
        <a href="<?php echo url_wrap('/student/medical_records.php'); ?>" class="action-btn <?php if($current_page == 'medical_records.php') echo 'active'; ?>">
            <i class="bi bi-journal-medical"></i> Medical Records
        </a>
        <a href="<?php echo url_wrap('/student/prescriptions.php'); ?>" class="action-btn <?php if($current_page == 'prescriptions.php') echo 'active'; ?>">
            <i class="bi bi-capsule"></i> My Prescriptions
        </a>
        <a href="<?php echo url_wrap('/student/profile.php'); ?>" class="action-btn <?php if($current_page == 'profile.php') echo 'active'; ?>">
            <i class="bi bi-person-lines-fill"></i> Update Profile
        </a>
    </div>
</div>
