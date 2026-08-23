<?php 
// Assumes $current_page is set by the including page, or we can use $_SERVER['SCRIPT_NAME']
$current_script = basename($_SERVER['SCRIPT_NAME']);
$dir_path = dirname($_SERVER['SCRIPT_NAME']);
?>
<aside class="navigation">
    <div class="nav-brand" style="padding: 20px; color: white; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1);">
        <h3 style="margin: 0; font-size: 1.1rem; font-weight: 600;">Staff Portal</h3>
    </div>
    
    <nav class="nav-links" style="margin-top: 20px;">
        <a href="<?php echo url_wrap('/staff/dashboard.php'); ?>" class="nav-item <?php if($current_script == 'dashboard.php' && strpos($dir_path, 'staff') !== false) echo 'active'; ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>
        
        <a href="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'reception') !== false) echo 'active'; ?>">
            <i class="bi bi-person-bounding-box"></i> Reception
        </a>
        
        <a href="<?php echo url_wrap('/modules/encounters/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'encounters') !== false) echo 'active'; ?>">
            <i class="bi bi-heart-pulse"></i> Doctor's Clinic
        </a>

        <a href="<?php echo url_wrap('/modules/patients/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'patients') !== false) echo 'active'; ?>">
            <i class="bi bi-people"></i> Patients Directory
        </a>
        
        <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'pharmacy') !== false) echo 'active'; ?>">
            <i class="bi bi-capsule"></i> Pharmacy
        </a>
        
        <?php if(isset($_SESSION['staff_role']) && $_SESSION['staff_role'] === 'admin') { ?>
        <div style="margin: 15px 20px 5px 20px; font-size: 0.8rem; color: #8ba8b8; text-transform: uppercase; letter-spacing: 1px;">Admin</div>
        
        <a href="<?php echo url_wrap('/modules/admin/index.php'); ?>" class="nav-item <?php if(strpos($dir_path, 'admin') !== false) echo 'active'; ?>">
            <i class="bi bi-bar-chart-steps"></i> Admin Dashboard
        </a>
        
        <a href="<?php echo url_wrap('/staff/index.php'); ?>" class="nav-item <?php if($current_script == 'index.php' && strpos($dir_path, 'staff') !== false) echo 'active'; ?>">
            <i class="bi bi-person-badge"></i> Manage Staff
        </a>
        <?php } ?>
        
        <a href="<?php echo url_wrap('/staff/logout.php'); ?>" class="nav-item" style="margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); color: #ff6b6b;">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </nav>
</aside>