<?php 
require_once('../../../private/config.php'); 
require_password_reset();

$page_title = 'Encounters Dashboard';
$specificCss = '/assets/css/encounters.css';

// We can filter by status if passed in URL
$status_filter = $_GET['status'] ?? null;
$encounters = find_all_encounters($status_filter);

include(SHARED_PATH . '/header.php'); 
?>

<div id="content">
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  <main class="main-content">
    
    
    <div class="top">
        <p class="top-head">Encounters & Clinical Queue</p> 
        <p class="top-description">Manage active patient visits and clinical workflows.</p>
    </div>

    <div><?php echo display_session_message(); ?></div>

    <div class="above-tabs">
      <a id="link_layout" class="add-staff" href="<?php echo url_wrap('/modules/reception/check_in.php'); ?>"> + Check-in Patient</a>
    </div>

    <div class="tabs" role="tablist">
        <a href="?status=" class="tab-btn <?php echo !$status_filter ? 'active' : ''; ?>" style="text-decoration:none;">All</a>
        <a href="?status=Waiting" class="tab-btn <?php echo $status_filter == 'Waiting' ? 'active' : ''; ?>" style="text-decoration:none;">Waiting (Nurse Queue)</a>
        <a href="?status=In Progress" class="tab-btn <?php echo $status_filter == 'In Progress' ? 'active' : ''; ?>" style="text-decoration:none;">In Progress (Doctor)</a>
        <a href="?status=Completed" class="tab-btn <?php echo $status_filter == 'Completed' ? 'active' : ''; ?>" style="text-decoration:none;">Completed</a>
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
