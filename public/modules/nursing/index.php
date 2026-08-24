<?php 
require_once('../../../private/config.php'); 
require_password_reset();

$_allowed_nursing = ['nurse', 'doctor', 'admin', 'super_admin'];
if (!isset($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], $_allowed_nursing)) {
    $_SESSION['error'] = "Access Denied: You do not have permission to access the Nursing Station.";
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = 'Nursing Station Queue';
$specificCss = '/assets/css/encounters.css';

$encounters = find_all_encounters('Waiting'); // Nurses only need to see waiting patients

include(SHARED_PATH . '/header.php'); 
?>

<div id="content">
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  <main class="main-content">
    
    <div class="top">
        <p class="top-head">Nursing Station</p> 
        <p class="top-description">Manage patients waiting for vitals and initial assessment.</p>
    </div>

    <div><?php echo display_session_message(); ?></div>

    <div class="tabs" role="tablist">
        <a href="#" class="tab-btn active" style="text-decoration:none;">Waiting (Nurse Queue)</a>
    </div>

    <div class="encounter-list-container">
        <table class="staff-list">
            <tr>
                <th>Encounter No.</th>
                <th>Patient</th>
                <th>Assigned Doctor</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Time Arrived</th>
                <th>Action</th>
            </tr>
            <?php while($e = $encounters->fetch_assoc()) { ?>
            <tr>
                <td><?php echo v_wrap($e['encounter_number']); ?></td>
                <td><?php echo v_wrap($e['p_id'] . ' - ' . $e['patient_last'] . ' ' . $e['patient_first']); ?></td>
                <td><?php echo v_wrap($e['doctor_name']); ?></td>
                <td>
                    <span class="badge priority-<?php echo strtolower($e['priority']); ?>">
                        <?php echo v_wrap($e['priority']); ?>
                    </span>
                </td>
                <td>
                    <span class="badge status-<?php echo str_replace(' ', '-', strtolower($e['status'])); ?>">
                        <?php echo v_wrap($e['status']); ?>
                    </span>
                </td>
                <td><?php echo date('h:i A', strtotime($e['created_at'])); ?></td>
                <td>
                    <a class="view-staff" style="background:#0F4E74; color:white; padding:5px 10px; border-radius:4px; text-decoration:none;" href="<?php echo url_wrap('/modules/encounters/view.php?id=' . u_wrap($e['id'])); ?>">
                        Open Workspace <i class="bi bi-box-arrow-in-right"></i>
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </div>

  </main>
</div>
<?php include(SHARED_PATH . '/footer.php'); ?>
