<?php 
require_once('../../../private/config.php'); 
require_password_reset();

if (function_exists('sweep_expired_appointments')) {
    sweep_expired_appointments();
}

$page_title = 'Encounters Dashboard';
$specificCss = '/assets/css/encounters.css';

// Filter setup
$current_role = $_SESSION['staff_role'] ?? '';
$is_doctor = ($current_role === 'doctor');
$current_staff_id = $_SESSION['staff_id'] ?? null;

$my_active_count = 0;
$my_total_count = 0;
if ($is_doctor && $current_staff_id) {
    $c_q = $db_1->prepare("SELECT 
        COUNT(CASE WHEN status IN ('Waiting', 'In Progress') THEN 1 END) as active_c,
        COUNT(*) as total_c 
        FROM encounters WHERE doctor_id = ?");
    $c_q->bind_param("i", $current_staff_id);
    $c_q->execute();
    $c_q->bind_result($my_active_count, $my_total_count);
    $c_q->fetch();
    $c_q->close();
}

// Scope: if doctor, default to their own queue ('mine') unless 'all' is explicitly requested
$scope = $_GET['scope'] ?? ($is_doctor ? 'mine' : 'all');
$mine = ($is_doctor && $scope === 'mine');

// Status filter: for doctor in 'mine' scope, default to 'Active' (Waiting + In Progress) unless overridden
$status_filter = $_GET['status'] ?? ($mine ? 'Active' : null);
if ($status_filter === 'all' || $status_filter === '') {
    $status_filter = null;
}

$search = trim($_GET['search'] ?? '');
$doctor_filter = $mine ? $current_staff_id : null;
$encounters = find_all_encounters($status_filter, $doctor_filter, $search);

include(SHARED_PATH . '/header.php'); 
?>

<div id="content">
  <?php include(SHARED_PATH . '/navigation.php'); ?>
  <main class="main-content">
    
    <div class="top">
        <p class="top-head">Encounters & Clinical Queue</p> 
        <p class="top-description">Manage active patient visits, doctor assignments, and clinical workflows.</p>
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

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
        <div class="tabs" role="tablist" style="margin-bottom: 0;">
            <?php if ($mine) { ?>
                <a href="?scope=mine&status=Active<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter === 'Active' ? 'active' : ''; ?>" style="text-decoration:none;">
                    <i class="bi bi-lightning-charge-fill"></i> Active Queue (<?php echo $my_active_count; ?>)
                </a>
                <a href="?scope=mine&status=Waiting<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter === 'Waiting' ? 'active' : ''; ?>" style="text-decoration:none;">
                    Waiting (Nurse)
                </a>
                <a href="?scope=mine&status=In+Progress<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter === 'In Progress' ? 'active' : ''; ?>" style="text-decoration:none;">
                    In Consultation
                </a>
                <a href="?scope=mine&status=Completed<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter === 'Completed' ? 'active' : ''; ?>" style="text-decoration:none;">
                    Completed
                </a>
                <a href="?scope=mine&status=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter === null ? 'active' : ''; ?>" style="text-decoration:none;">
                    All My History (<?php echo $my_total_count; ?>)
                </a>
            <?php } else { ?>
                <a href="?scope=all<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo !$status_filter ? 'active' : ''; ?>" style="text-decoration:none;">
                    All Clinic Encounters
                </a>
                <a href="?scope=all&status=Waiting<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter == 'Waiting' ? 'active' : ''; ?>" style="text-decoration:none;">
                    Waiting (Nurse Queue)
                </a>
                <a href="?scope=all&status=In+Progress<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter == 'In Progress' ? 'active' : ''; ?>" style="text-decoration:none;">
                    In Progress (Doctor)
                </a>
                <a href="?scope=all&status=Completed<?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>" class="tab-btn <?php echo $status_filter == 'Completed' ? 'active' : ''; ?>" style="text-decoration:none;">
                    Completed
                </a>
            <?php } ?>
        </div>
        
        <?php if ($is_doctor) { ?>
        <div style="display: flex; gap: 6px; background: #e9ecef; padding: 4px; border-radius: 6px;">
            <a href="?scope=mine&status=Active" style="text-decoration: none; padding: 6px 14px; font-size: 0.85rem; border-radius: 4px; font-weight: 600; <?php echo $mine ? 'background: #0F4E74; color: white;' : 'color: #555;'; ?>">
                <i class="bi bi-person-badge"></i> My Active Queue (<?php echo $my_active_count; ?>)
            </a>
            <a href="?scope=all" style="text-decoration: none; padding: 6px 14px; font-size: 0.85rem; border-radius: 4px; font-weight: 600; <?php echo !$mine ? 'background: #0F4E74; color: white;' : 'color: #555;'; ?>">
                <i class="bi bi-people"></i> All Clinic Doctors
            </a>
        </div>
        <?php } ?>
    </div>

    <!-- Uniform Search Bar (Styled identically to Patients & Staff Index) -->
    <form method="GET" style="display: flex; align-items: center; justify-content: flex-start; margin-bottom: 18px; flex-wrap: wrap; gap: 10px;">
        <input type="hidden" name="scope" value="<?php echo htmlspecialchars($scope); ?>">
        <?php if ($status_filter) { ?>
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($status_filter); ?>">
        <?php } ?>
        
        <span style="color: #666666; border: 1px solid #666666; border-left: 3px solid #0F4E74; padding: 4px 6px; border-radius: 4px; display: inline-flex; align-items: center; background: #fff;">
            <i class="bi bi-search" style="margin-right: 6px;"></i>
            <input type="text" name="search" placeholder="Search patient name, ID, or encounter #..." 
                   style="color: #333; outline: none; border: none; width: 280px; font-size: 0.9rem;" 
                   value="<?php echo htmlspecialchars($search); ?>">
        </span>
        
        <button type="submit" id="filterBtn" style="background: #0F4E74; color: white; border: none; padding: 7px 16px; border-radius: 4px; font-size: 0.88rem; cursor: pointer; font-weight: 600;">
            Search
        </button>

        <?php if (!empty($search)) { ?>
            <a href="?scope=<?php echo urlencode($scope); ?><?php echo $status_filter ? '&status=' . urlencode($status_filter) : ''; ?>" style="font-size: 0.85rem; color: #dc3545; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                <i class="bi bi-x-circle"></i> Clear Filter
            </a>
        <?php } ?>
    </form>

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
