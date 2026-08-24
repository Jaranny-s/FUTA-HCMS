<?php
require_once('../../../private/config.php');
require_password_reset();

if (!isset($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['pharmacist', 'doctor', 'admin', 'super_admin'])) {
    $_SESSION['error'] = "Access Denied: You do not have permission to access Pharmacy.";
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Pharmacy Dispensing Queue";
$specificCss = "/assets/css/encounters.css"; 

$status_filter = $_GET['status'] ?? 'Pending';
$prescriptions = get_all_prescriptions($status_filter);

if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'dispense') {
        $rx_id = (int)$_POST['prescription_id'];
        $quantity = (int)$_POST['quantity'];
        $remarks = $_POST['remarks'] ?? '';
        $staff_id = $_SESSION['staff_id'];
        
        $rx_details = get_prescription_details($rx_id);
        
        if (dispense_prescription($rx_id, $staff_id, $quantity, $remarks)) {
            $_SESSION['message'] = "Prescription dispensed successfully!";
            if ($rx_details && $rx_details['patient_category'] !== 'Student' && $rx_details['inventory_id']) {
                $_SESSION['message'] .= " A bill was automatically generated for the patient.";
            }
        } else {
            $_SESSION['error'] = "Failed to dispense prescription.";
        }
        redirect_to(url_wrap('/modules/pharmacy/index.php'));
    }
}

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
                        <?php if($rx['status'] == 'Pending') { 
                            $rx_details = get_prescription_details((int)$rx['id']);
                        ?>
                            <button data-modal-target="dispenseModal_<?php echo $rx['id']; ?>" class="btn btn-primary" style="background:#1bc03d; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;">
                                Process <i class="bi bi-arrow-right"></i>
                            </button>
                            
                            <!-- Dispense Modal -->
                            <div id="dispenseModal_<?php echo $rx['id']; ?>" class="modal-overlay">
                                <div class="modal-content" style="text-align:left;">
                                    <button class="modal-close" data-modal-close>&times;</button>
                                    <h3 class="modal-title" style="margin-bottom:5px;"><i class="bi bi-prescription2"></i> Dispense Medication</h3>
                                    
                                    <?php if ($rx_details) { ?>
                                        <div style="background:#f9f9f9; padding:15px; border-radius:8px; border:1px solid #eee; margin-bottom:15px;">
                                            <h4 style="margin-top:0; color:#0F4E74; font-size:1.1rem;">
                                                <?php echo v_wrap($rx_details['drug_name'] ?? $rx_details['medication_name']); ?>
                                            </h4>
                                            <p style="margin-bottom:5px;"><strong>Dosage:</strong> <?php echo v_wrap($rx_details['dosage'] . ' - ' . $rx_details['frequency']); ?></p>
                                            <p style="margin-bottom:5px;"><strong>Duration:</strong> <?php echo v_wrap($rx_details['duration']); ?></p>
                                            <p style="margin-bottom:5px;"><strong>Instructions:</strong> <span style="background:#fff3cd; color:#856404; padding:2px 5px; border-radius:4px; font-size:0.9rem;"><?php echo v_wrap($rx_details['instructions']); ?></span></p>
                                        </div>

                                        <?php if ($rx_details['inventory_id']) { ?>
                                            <form action="index.php" method="post">
                                                <input type="hidden" name="action" value="dispense">
                                                <input type="hidden" name="prescription_id" value="<?php echo $rx['id']; ?>">
                                                
                                                <div style="margin-bottom:10px; font-size:0.9rem;">
                                                    <strong>Current Stock:</strong> <span style="color:<?php echo $rx_details['stock_quantity'] > 10 ? 'green' : 'red'; ?>"><?php echo v_wrap($rx_details['stock_quantity']); ?> units</span><br>
                                                    <?php if($rx_details['patient_category'] !== 'Student') { ?>
                                                        <span style="color:#721c24;">⚠️ Since patient is a <b><?php echo v_wrap($rx_details['patient_category']); ?></b>, dispensing will auto-generate a bill.</span>
                                                    <?php } else { ?>
                                                        <span style="color:#155724;">✔️ Patient is a <b>Student</b>. No bill will be generated.</span>
                                                    <?php } ?>
                                                </div>

                                                <div class="form-group" style="margin-bottom:15px;">
                                                    <label>Quantity to Dispense *</label>
                                                    <input type="number" name="quantity" required min="1" max="<?php echo $rx_details['stock_quantity']; ?>" style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;">
                                                </div>
                                                <div class="form-group" style="margin-bottom:15px;">
                                                    <label>Pharmacist Remarks</label>
                                                    <textarea name="remarks" rows="2" style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;"></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary" style="background:#1bc03d; color:white; border:none; padding:10px; border-radius:5px; width:100%;">
                                                    <i class="bi bi-check-circle"></i> Dispense Medication
                                                </button>
                                            </form>
                                        <?php } else { ?>
                                            <div style="background:#f8d7da; color:#721c24; padding:15px; border-radius:8px;">
                                                <strong>Error:</strong> This prescription was not selected from the official pharmacy inventory. You cannot auto-dispense it.
                                            </div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </div>
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
