<?php 
$current_script = basename($_SERVER['SCRIPT_NAME']);
$dir_path = dirname($_SERVER['SCRIPT_NAME']);
$role = $_SESSION['staff_role'] ?? '';
?>
<aside class="navigation">
    <div class="nav-brand" style="padding: 20px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Staff Portal</h3>
    </div>
    
    <nav class="nav-links" style="margin-top: 20px;">

        <!-- Dashboard: shown to all -->
        <a href="<?php echo url_wrap('/staff/dashboard.php'); ?>" class="nav-item <?php if($current_script == 'dashboard.php' && strpos($dir_path, 'staff') !== false) echo 'active'; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <?php if($role === 'receptionist'): ?>
            <!-- RECEPTIONIST NAV -->
            <div class="nav-section-label">Patients</div>
            <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'patients') !== false) echo 'active'; ?>">
                <i class="bi bi-people"></i> All Patients
            </a>

            <div class="nav-section-label">Check-In</div>
            <a href="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'reception') !== false) echo 'active'; ?>">
                <i class="bi bi-person-bounding-box"></i> Check In Patient
            </a>

        <?php elseif($role === 'nurse'): ?>
            <!-- NURSE NAV -->
            <div class="nav-section-label">Patients</div>
            <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'patients') !== false) echo 'active'; ?>">
                <i class="bi bi-people"></i> All Patients
            </a>

            <div class="nav-section-label">Clinical</div>
            <a href="<?php echo url_wrap('/modules/nursing/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'nursing') !== false) echo 'active'; ?>">
                <i class="bi bi-clipboard2-pulse"></i> Nursing Station
            </a>
            <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'encounters') !== false) echo 'active'; ?>">
                <i class="bi bi-heart-pulse"></i> Active Encounters
            </a>

        <?php elseif($role === 'doctor'): ?>
            <!-- DOCTOR NAV -->
            <div class="nav-section-label">Patients</div>
            <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'patients') !== false) echo 'active'; ?>">
                <i class="bi bi-people"></i> All Patients
            </a>

            <div class="nav-section-label">Clinical</div>
            <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'encounters') !== false) echo 'active'; ?>">
                <i class="bi bi-heart-pulse"></i> Encounters
            </a>
            <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'pharmacy') !== false) echo 'active'; ?>">
                <i class="bi bi-capsule"></i> Prescriptions
            </a>

        <?php elseif($role === 'pharmacist'): ?>
            <!-- PHARMACIST NAV -->
            <div class="nav-section-label">Pharmacy</div>
            <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" class="nav-item <?php if($current_script == 'index.php' && strpos($dir_path, 'pharmacy') !== false) echo 'active'; ?>">
                <i class="bi bi-capsule"></i> Prescriptions Queue
            </a>
            <a href="<?php echo url_wrap('/modules/pharmacy/inventory.php'); ?>" class="nav-item <?php if($current_script == 'inventory.php') echo 'active'; ?>">
                <i class="bi bi-box-seam"></i> Drug Inventory
            </a>
            <a href="<?php echo url_wrap('/modules/pharmacy/dispense.php'); ?>" class="nav-item <?php if($current_script == 'dispense.php') echo 'active'; ?>">
                <i class="bi bi-bag-check"></i> Dispense Medication
            </a>

        <?php elseif(in_array($role, ['admin', 'super_admin'])): ?>
            <!-- ADMIN / SUPER ADMIN NAV -->
            <div class="nav-section-label">Staff</div>
            <a href="<?php echo url_wrap('/staff/index.php'); ?>" class="nav-item <?php if($current_script == 'index.php' && strpos($dir_path, 'staff') !== false) echo 'active'; ?>">
                <i class="bi bi-person-badge"></i> Manage Staff
            </a>

            <div class="nav-section-label">Patients</div>
            <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'patients') !== false) echo 'active'; ?>">
                <i class="bi bi-people"></i> Patients Directory
            </a>

            <div class="nav-section-label">Reports</div>
            <a href="<?php echo url_wrap('/staff/admin/activity_logs.php'); ?>" class="nav-item <?php if($current_script == 'activity_logs.php') echo 'active'; ?>">
                <i class="bi bi-journal-text"></i> Activity Logs
            </a>

        <?php else: ?>
            <!-- FALLBACK: show all for unknown roles -->
            <a href="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'reception') !== false) echo 'active'; ?>">
                <i class="bi bi-person-bounding-box"></i> Reception
            </a>
            <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'encounters') !== false) echo 'active'; ?>">
                <i class="bi bi-heart-pulse"></i> Encounters
            </a>
            <a href="<?php echo url_wrap('/modules/nursing/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'nursing') !== false) echo 'active'; ?>">
                <i class="bi bi-clipboard2-pulse"></i> Nursing Station
            </a>
            <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'patients') !== false) echo 'active'; ?>">
                <i class="bi bi-people"></i> Patients Directory
            </a>
            <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'pharmacy') !== false) echo 'active'; ?>">
                <i class="bi bi-capsule"></i> Pharmacy
            </a>
        <?php endif; ?>

        <!-- Logout: shown to all -->
        <a href="#" class="nav-item" data-modal-target="logoutModal" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); color: #ff6b6b;">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</aside>

<!-- Global Logout Modal -->
<div id="logoutModal" class="modal-overlay">
    <div class="modal-content" style="max-width: 400px;">
        <button class="modal-close" data-modal-close>&times;</button>
        <h3 class="modal-title"><i class="bi bi-box-arrow-right"></i> Confirm Logout</h3>
        <p style="color: #666; margin-bottom: 25px;">Are you sure you want to log out of your account?</p>
        <div style="display: flex; justify-content: space-between; gap: 15px;">
            <button data-modal-close class="btn" style="background:#e0e0e0; color:#333; flex: 1;">Cancel</button>
            <a href="<?php echo url_wrap('/staff/logout_confirm.php'); ?>" class="btn" style="background:#ff6b6b; color:white; flex: 1; text-align: center;">Yes, Log Out</a>
        </div>
    </div>
</div>