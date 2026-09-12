<?php 
require_once('../../../private/config.php'); 
require_password_reset();

$page_title = 'Encounters Dashboard';
$specificCss = '/assets/css/encounters.css';

// We can filter by status and doctor assignment if passed in URL
$status_filter = $_GET['status'] ?? null;
$mine = isset($_GET['mine']) && $_GET['mine'] == '1';
$current_role = $_SESSION['staff_role'] ?? '';
$is_doctor = ($current_role === 'doctor');

$my_encounters_count = 0;
if ($is_doctor && isset($_SESSION['staff_id'])) {
    $chk_q = $db_1->prepare("SELECT COUNT(*) FROM encounters WHERE doctor_id = ?");
    $chk_q->bind_param("i", $_SESSION['staff_id']);
    $chk_q->execute();
    $chk_q->bind_result($my_encounters_count);
    $chk_q->fetch();
    $chk_q->close();
}

// If doctor tried to access ?mine=1 but has no encounters assigned, redirect with message
if ($mine && $is_doctor && $my_encounters_count == 0) {
    set_session_message("You currently have no patient encounters assigned to you.", "info");
    redirect_to(url_wrap('/modules/encounters/index.php'));
}

$doctor_filter = ($mine && $is_doctor && $my_encounters_count > 0) ? $_SESSION['staff_id'] : null;
$encounters = find_all_encounters($status_filter, $doctor_filter);

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
      <?php if (hasPermission('create_encounter')) { 
          $patients = find_all_patients();
          $doctors = find_staff_by_role('doctor');
      ?>
      <button data-modal-target="checkInModal" class="add-staff" style="background:#0F4E74; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:600;">
          + Check-in Patient
      </button>

      <!-- Check-in Modal -->
      <div id="checkInModal" class="modal-overlay">
          <div class="modal-content">
              <button class="modal-close" data-modal-close>&times;</button>
              <h3 class="modal-title"><i class="bi bi-person-plus"></i> Check-in Patient</h3>
              
              <form action="<?php echo url_wrap('/modules/reception/check_in.php'); ?>" method="post">
                  <div class="form-group" style="margin-bottom: 15px;">
                      <label>Select Patient</label>
                      <select name="patient_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                          <option value="">-- Choose Patient --</option>
                          <?php while($p = $patients->fetch_assoc()) { ?>
                              <option value="<?php echo $p['id']; ?>">
                                  <?php echo v_wrap($p['patient_id'] . ' - ' . $p['surname'] . ' ' . $p['first_name']); ?>
                              </option>
                          <?php } ?>
                      </select>
                  </div>

                  <div class="form-group" style="margin-bottom: 15px;">
                      <label>Assign Doctor</label>
                      <select name="doctor_id" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                          <option value="">-- Choose Doctor --</option>
                          <?php while($d = $doctors->fetch_assoc()) { ?>
                              <option value="<?php echo $d['id']; ?>">
                                  <?php echo v_wrap($d['full_name']); ?>
                              </option>
                          <?php } ?>
                      </select>
                  </div>

                  <div class="form-group" style="margin-bottom: 15px;">
                      <label>Priority</label>
                      <select name="priority" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                          <option value="Normal">Normal</option>
                          <option value="Routine">Routine</option>
                          <option value="Urgent">Urgent</option>
                          <option value="Emergency">Emergency</option>
                      </select>
                  </div>

                  <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width: 100%;">
                      Check In & Start Encounter
                  </button>
              </form>
          </div>
      </div>
      <?php } ?>
    </div>

    <?php 
    $mine_param = ($mine && $is_doctor && $my_encounters_count > 0) ? '&mine=1' : '';
    ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <div class="tabs" role="tablist" style="margin-bottom: 0;">
            <a href="?status=<?php echo $mine_param; ?>" class="tab-btn <?php echo !$status_filter ? 'active' : ''; ?>" style="text-decoration:none;">All</a>
            <a href="?status=Waiting<?php echo $mine_param; ?>" class="tab-btn <?php echo $status_filter == 'Waiting' ? 'active' : ''; ?>" style="text-decoration:none;">Waiting (Nurse Queue)</a>
            <a href="?status=In Progress<?php echo $mine_param; ?>" class="tab-btn <?php echo $status_filter == 'In Progress' ? 'active' : ''; ?>" style="text-decoration:none;">In Progress (Doctor)</a>
            <a href="?status=Completed<?php echo $mine_param; ?>" class="tab-btn <?php echo $status_filter == 'Completed' ? 'active' : ''; ?>" style="text-decoration:none;">Completed</a>
        </div>
        
        <?php if ($is_doctor && $my_encounters_count > 0) { ?>
        <div style="display: flex; gap: 6px; background: #e9ecef; padding: 4px; border-radius: 6px;">
            <a href="?status=<?php echo urlencode($status_filter ?? ''); ?>" style="text-decoration: none; padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; font-weight: 600; <?php echo !$mine ? 'background: #0F4E74; color: white;' : 'color: #555;'; ?>">
                <i class="bi bi-people"></i> All Clinic Encounters
            </a>
            <a href="?status=<?php echo urlencode($status_filter ?? ''); ?>&mine=1" style="text-decoration: none; padding: 6px 12px; font-size: 0.85rem; border-radius: 4px; font-weight: 600; <?php echo $mine ? 'background: #0F4E74; color: white;' : 'color: #555;'; ?>">
                <i class="bi bi-person-badge"></i> My Encounters (<?php echo $my_encounters_count; ?>)
            </a>
        </div>
        <?php } ?>
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
