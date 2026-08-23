<?php
require_once('../../../private/config.php');
require_password_reset();

if (!isset($_SESSION['staff_role']) || $_SESSION['staff_role'] !== 'admin') {
    $_SESSION['error'] = "Unauthorized access.";
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Admin Dashboard";

// Fetch some basic stats
$total_patients = 0;
$total_staff = 0;
$total_encounters = 0;

$p_result = $db->query("SELECT COUNT(*) as count FROM patients");
if ($p_result) { $row = $p_result->fetch_assoc(); $total_patients = $row['count']; }

$s_result = $db->query("SELECT COUNT(*) as count FROM staff");
if ($s_result) { $row = $s_result->fetch_assoc(); $total_staff = $row['count']; }

$e_result = $db->query("SELECT COUNT(*) as count FROM encounters");
if ($e_result) { $row = $e_result->fetch_assoc(); $total_encounters = $row['count']; }

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    
    <main class="main-content">
        <div class="top" style="margin-bottom: 20px;">
            <p class="top-head">Admin Dashboard</p>
            <p class="top-description">System Overview & Patient Statistics</p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px;">
            
            <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
                <i class="bi bi-people" style="font-size: 2.5rem; color: #0F4E74;"></i>
                <h2 style="margin: 10px 0; color: #333; font-size: 2rem;"><?php echo $total_patients; ?></h2>
                <p style="color: #666; margin: 0; font-weight: 600;">Registered Patients</p>
            </div>

            <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
                <i class="bi bi-person-badge" style="font-size: 2.5rem; color: #1bc03d;"></i>
                <h2 style="margin: 10px 0; color: #333; font-size: 2rem;"><?php echo $total_staff; ?></h2>
                <p style="color: #666; margin: 0; font-weight: 600;">Total Staff Members</p>
            </div>

            <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); text-align: center;">
                <i class="bi bi-heart-pulse" style="font-size: 2.5rem; color: #ff6b6b;"></i>
                <h2 style="margin: 10px 0; color: #333; font-size: 2rem;"><?php echo $total_encounters; ?></h2>
                <p style="color: #666; margin: 0; font-weight: 600;">Total Encounters</p>
            </div>

        </div>

        <div style="background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
            <h3 style="margin-top: 0; color: #333;">Quick Actions</h3>
            <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">
            <a href="<?php echo url_wrap('/staff/index.php'); ?>" class="btn btn-primary" style="background: #0F4E74; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block;">
                <i class="bi bi-gear-fill"></i> Manage Staff Directory
            </a>
            <a href="<?php echo url_wrap('/staff/new.php'); ?>" class="btn" style="background: #e6e5ff; color: #0F4E74; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; margin-left: 10px; font-weight: 600;">
                <i class="bi bi-person-plus"></i> Add New Staff
            </a>
        </div>

    </main>
</div>

<?php include(SHARED_PATH . '/footer.php'); ?>
