<?php 
$current_script = basename($_SERVER['SCRIPT_NAME']);
$dir_path = dirname($_SERVER['SCRIPT_NAME']);
$role = $_SESSION['staff_role'] ?? '';
?>
<aside class="navigation">
    <div class="nav-brand" style="padding: 20px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Staff Portal</h3>
    </div>

    <?php 
    if ($role === 'doctor' && isset($_SESSION['staff_id'])) {
        $doctorDutyStatus = 'Available';
        $docQ = $db_1->query("SELECT duty_status FROM staff WHERE id = " . (int)$_SESSION['staff_id'] . " LIMIT 1");
        if ($docQ && $rowD = $docQ->fetch_assoc()) {
            $doctorDutyStatus = $rowD['duty_status'] ?? 'Available';
        }
        $dutyDotColor = ($doctorDutyStatus === 'Available') ? '#22c55e' : (($doctorDutyStatus === 'In Consultation') ? '#38bdf8' : (($doctorDutyStatus === 'On Break') ? '#facc15' : '#94a3b8'));
    ?>
    <div style="padding: 10px 14px; margin: 12px 14px 0 14px; background: rgba(255,255,255,0.08); border-radius: 8px; border: 1px solid rgba(255,255,255,0.15);">
        <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.5px; color: #cbd5e1; margin-bottom: 5px; display: flex; align-items: center; gap: 5px;">
            <i id="navDutyDot" class="bi bi-circle-fill" style="color: <?php echo $dutyDotColor; ?>; font-size: 0.65rem;"></i> Clinical Duty Status
        </div>
        <select id="doctorDutyStatusNav" onchange="updateNavDutyStatus(this.value)" style="width: 100%; padding: 6px 8px; border-radius: 5px; font-size: 0.8rem; font-weight: 600; background: #0F4E74; color: white; border: 1px solid rgba(255,255,255,0.25); cursor: pointer;">
            <option value="Available" <?php if($doctorDutyStatus === 'Available') echo 'selected'; ?>>🟢 Available</option>
            <option value="In Consultation" <?php if($doctorDutyStatus === 'In Consultation') echo 'selected'; ?>>🔵 In Consultation</option>
            <option value="On Break" <?php if($doctorDutyStatus === 'On Break') echo 'selected'; ?>>🟡 On Break</option>
            <option value="Off Duty" <?php if($doctorDutyStatus === 'Off Duty') echo 'selected'; ?>>⚪ Off Duty</option>
        </select>
    </div>
    <script>
    function updateNavDutyStatus(newStatus) {
        const sel = document.getElementById('doctorDutyStatusNav');
        const dot = document.getElementById('navDutyDot');
        if (sel) sel.disabled = true;
        const formData = new FormData();
        formData.append('status', newStatus);
        fetch('<?php echo url_wrap("/modules/ajax/update_duty_status.php"); ?>', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(res => {
            if (sel) sel.disabled = false;
            if (dot) {
                if (newStatus === 'Available') dot.style.color = '#22c55e';
                else if (newStatus === 'In Consultation') dot.style.color = '#38bdf8';
                else if (newStatus === 'On Break') dot.style.color = '#facc15';
                else dot.style.color = '#94a3b8';
            }
        })
        .catch(() => { if (sel) sel.disabled = false; });
    }
    </script>
    <?php } ?>
    
    <nav class="nav-links" style="margin-top: 15px;">

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

            <?php if($role === 'super_admin'): ?>
            <div class="nav-section-label">Clinical Overview</div>
            <a href="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'reception') !== false) echo 'active'; ?>">
                <i class="bi bi-person-bounding-box"></i> Reception
            </a>
            <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'encounters') !== false) echo 'active'; ?>">
                <i class="bi bi-heart-pulse"></i> Encounters
            </a>
            <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'pharmacy') !== false) echo 'active'; ?>">
                <i class="bi bi-capsule"></i> Pharmacy Queue
            </a>
            <?php endif; ?>

            <div class="nav-section-label">Reports & Settings</div>
            <a href="<?php echo url_wrap('/staff/admin/activity_logs.php'); ?>" class="nav-item <?php if($current_script == 'activity_logs.php') echo 'active'; ?>">
                <i class="bi bi-journal-text"></i> Activity Logs
            </a>
            <a href="<?php echo url_wrap('/staff/admin/settings.php'); ?>" class="nav-item <?php if($current_script == 'settings.php') echo 'active'; ?>">
                <i class="bi bi-gear"></i> System Settings
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