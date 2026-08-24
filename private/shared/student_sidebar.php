<?php
$current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<div class="sidebar">
    <div class="card">
        <h3><i class="bi bi-grid-1x2"></i> Navigation</h3>
        <a href="<?php echo url_wrap('/student/dashboard.php'); ?>" class="action-btn <?php if($current_page == 'dashboard.php') echo 'active'; ?>">
            <i class="bi bi-house"></i> Dashboard
        </a>
        <a href="#" data-modal-target="bookAppointmentModal" class="action-btn <?php if($current_page == 'book.php') echo 'active'; ?> primary">
            <i class="bi bi-calendar-plus"></i> Book Appointment
        </a>

<!-- Book Appointment Modal -->
<div id="bookAppointmentModal" class="modal-overlay">
    <div class="modal-content">
        <button class="modal-close" data-modal-close>&times;</button>
        <h3 class="modal-title"><i class="bi bi-calendar-plus"></i> Book a New Appointment</h3>
        
        <form action="<?php echo url_wrap('/student/book.php'); ?>" method="post">
            <div class="form-group">
                <label>Date of Visit *</label>
                <input type="date" name="date" required min="<?php echo date('Y-m-d'); ?>" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label>Preferred Time *</label>
                <input type="time" name="time" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
            </div>
            
            <div class="form-group" style="margin-top: 15px;">
                <label>Type of Visit *</label>
                <select name="type" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                    <option value="Consultation">General Consultation</option>
                    <option value="Medical Examination">Medical Examination (Clearance)</option>
                    <option value="Follow-up">Follow-up</option>
                </select>
            </div>

            <div class="form-group" style="margin-top: 15px;">
                <label>Reason for Visit *</label>
                <textarea name="reason" rows="3" placeholder="Briefly describe why you are visiting..." required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;"></textarea>
            </div>

            <button type="submit" class="action-btn primary" style="margin-top: 20px; text-align: center; justify-content: center; width: 100%;">Submit Request</button>
        </form>
    </div>
</div>
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
