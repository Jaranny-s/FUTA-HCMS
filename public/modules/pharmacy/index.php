<?php
require_once('../../../private/config.php');
require_password_reset();

$page_title = "Pharmacy Dispensing Queue";
$specificCss = "/assets/css/encounters.css"; 

$status_filter = $_GET['status'] ?? 'Pending';
$prescriptions = get_all_prescriptions($status_filter);

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    <main class="main-content">
        
        <div class="top">
            <p class="top-head">Pharmacy Queue</p>
            <p class="top-description">Process prescriptions and manage dispensing.</p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div class="above-tabs">
            <a id="link_layout" class="add-staff" href="<?php echo url_wrap('/modules/pharmacy/inventory.php'); ?>"> <i class="bi bi-box"></i> Manage Inventory</a>
        </div>

        <div class="tabs" role="tablist">
            <a href="?status=Pending" class="tab-btn <?php echo $status_filter == 'Pending' ? 'active' : ''; ?>" style="text-decoration:none;">Pending Queue</a>
            <a href="?status=Dispensed" class="tab-btn <?php echo $status_filter == 'Dispensed' ? 'active' : ''; ?>" style="text-decoration:none;">Dispensed</a>
            <a href="?status=" class="tab-btn <?php echo $status_filter == '' ? 'active' : ''; ?>" style="text-decoration:none;">All Records</a>
        </div>

        <div class="encounter-list-container">
            <table class="staff-list">
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Medication</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while($rx = $prescriptions->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo date('d M Y - h:i A', strtotime($rx['created_at'])); ?></td>
                    <td><?php echo v_wrap($rx['patient_category'] . ' - ' . $rx['surname'] . ' ' . $rx['first_name']); ?></td>
                    <td><?php echo v_wrap($rx['doctor_name']); ?></td>
                    <td><strong><?php echo v_wrap($rx['drug_name'] ?? $rx['medication_name']); ?></strong></td>
                    <td><span class="badge status-<?php echo strtolower($rx['status']); ?>"><?php echo v_wrap($rx['status']); ?></span></td>
                    <td>
                        <?php if($rx['status'] == 'Pending') { ?>
                            <a class="btn btn-primary" style="background:#1bc03d; color:white; padding:5px 10px; border-radius:4px; text-decoration:none;" href="<?php echo url_wrap('/modules/pharmacy/dispense.php?id=' . u_wrap($rx['id'])); ?>">
                                Process <i class="bi bi-arrow-right"></i>
                            </a>
                        <?php } else { ?>
                            <span style="color:#888;">Processed</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </main>
</div>
<?php include(SHARED_PATH . '/footer.php'); ?>
