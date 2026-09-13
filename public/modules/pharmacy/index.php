<?php
require_once('../../../private/config.php');
require_password_reset();

if (!isset($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['pharmacist', 'doctor', 'admin', 'super_admin'])) {
    $_SESSION['error'] = "Access Denied: You do not have permission to access Pharmacy.";
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Pharmacy Dispensing Queue";
$specificCss = "/assets/css/encounters.css"; 

$can_dispense = hasPermission('dispense_medication') || in_array($_SESSION['staff_role'] ?? '', ['pharmacist', 'admin', 'super_admin']);

$status_filter = $_GET['status'] ?? 'Pending';
$prescriptions = get_all_prescriptions($status_filter);

if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'dispense') {
        if (!$can_dispense) {
            $_SESSION['error'] = "Access Denied: Only pharmacists and authorized staff are permitted to dispense medications.";
            redirect_to(url_wrap('/modules/pharmacy/index.php'));
        }
        $rx_id = (int)$_POST['prescription_id'];
        $quantity = (int)($_POST['quantity'] ?? 1);
        $remarks = $_POST['remarks'] ?? '';
        $manual_inv_id = !empty($_POST['selected_inventory_id']) ? (int)$_POST['selected_inventory_id'] : null;
        $staff_id = $_SESSION['staff_id'];
        
        $rx_details = get_prescription_details($rx_id);
        
        if (dispense_prescription($rx_id, $staff_id, $quantity, $remarks, $manual_inv_id)) {
            $_SESSION['message'] = "Prescription dispensed successfully!";
            if ($rx_details && $rx_details['patient_category'] !== 'Student' && ($rx_details['inventory_id'] || $manual_inv_id)) {
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
                            <?php if ($can_dispense) { ?>
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

                                            <form action="index.php" method="post">
                                                <input type="hidden" name="action" value="dispense">
                                                <input type="hidden" name="prescription_id" value="<?php echo $rx['id']; ?>">

                                                <?php if (!empty($rx_details['inventory_id'])) { ?>
                                                    <div style="margin-bottom:10px; font-size:0.9rem;">
                                                        <strong>Matched Stock:</strong> <span style="color:#0F4E74; font-weight:600;"><?php echo v_wrap($rx_details['drug_name']); ?></span><br>
                                                        <strong>Current Stock:</strong> <span style="color:<?php echo $rx_details['stock_quantity'] > 10 ? 'green' : 'red'; ?>"><?php echo v_wrap($rx_details['stock_quantity']); ?> units available</span><br>
                                                        <?php if($rx_details['patient_category'] !== 'Student') { ?>
                                                            <span style="color:#721c24;">⚠️ Since patient is a <b><?php echo v_wrap($rx_details['patient_category']); ?></b>, dispensing will auto-generate a bill.</span>
                                                        <?php } else { ?>
                                                            <span style="color:#155724;">✔️ Patient is a <b>Student</b>. No bill will be generated.</span>
                                                        <?php } ?>
                                                    </div>

                                                    <div class="form-group" style="margin-bottom:15px;">
                                                        <label>Quantity to Dispense *</label>
                                                        <input type="number" name="quantity" required min="1" max="<?php echo max(1, (int)$rx_details['stock_quantity']); ?>" value="1" style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;">
                                                    </div>
                                                <?php } else { ?>
                                                    <div style="background:#fff3cd; color:#856404; padding:12px; border-radius:6px; margin-bottom:15px; font-size:0.9rem; border:1px solid #ffeeba;">
                                                        <i class="bi bi-info-circle-fill" style="margin-right:5px;"></i>
                                                        This medication was entered as a custom name (<strong><?php echo v_wrap($rx_details['medication_name']); ?></strong>). You can link it to an existing inventory item below, or dispense it directly.
                                                    </div>

                                                    <div class="form-group" style="margin-bottom:15px;">
                                                        <label style="font-weight:600; color:#333;">Link to Pharmacy Inventory (Optional):</label>
                                                        <select name="selected_inventory_id" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ddd; font-size:0.95rem;">
                                                            <option value="">-- Manual Dispensing / External (No Stock Deduction) --</option>
                                                            <?php 
                                                            $all_inv_items = get_all_inventory();
                                                            if ($all_inv_items) {
                                                                while($inv_row = $all_inv_items->fetch_assoc()) { 
                                                            ?>
                                                                <option value="<?php echo $inv_row['id']; ?>">
                                                                    <?php echo v_wrap($inv_row['drug_name'] . ' (' . $inv_row['stock_quantity'] . ' in stock)'); ?>
                                                                </option>
                                                            <?php 
                                                                } 
                                                            } 
                                                            ?>
                                                        </select>
                                                    </div>

                                                    <div class="form-group" style="margin-bottom:15px;">
                                                        <label>Quantity to Dispense *</label>
                                                        <input type="number" name="quantity" required min="1" value="1" style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;">
                                                    </div>
                                                <?php } ?>

                                                <div class="form-group" style="margin-bottom:15px;">
                                                    <label>Pharmacist Remarks</label>
                                                    <textarea name="remarks" rows="2" placeholder="e.g. Dispensed with counselling, dosage explained..." style="width:100%; padding:8px; border-radius:5px; border:1px solid #ddd;"></textarea>
                                                </div>

                                                <button type="submit" class="btn btn-primary" style="background:#1bc03d; color:white; border:none; padding:10px; border-radius:5px; width:100%;">
                                                    <i class="bi bi-check-circle"></i> Dispense Medication
                                                </button>
                                            </form>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <button data-modal-target="viewRxModal_<?php echo $rx['id']; ?>" class="btn" style="background:#e9ecef; color:#495057; border:1px solid #ced4da; padding:5px 10px; border-radius:4px; cursor:pointer; font-size:0.85rem;">
                                    <i class="bi bi-eye"></i> View Details
                                </button>

                                <!-- View Details Modal (Read-Only for Doctors / Staff) -->
                                <div id="viewRxModal_<?php echo $rx['id']; ?>" class="modal-overlay">
                                    <div class="modal-content" style="text-align:left;">
                                        <button class="modal-close" data-modal-close>&times;</button>
                                        <h3 class="modal-title" style="margin-bottom:5px;"><i class="bi bi-prescription2"></i> Prescription Details</h3>
                                        
                                        <?php if ($rx_details) { ?>
                                            <div style="background:#f9f9f9; padding:15px; border-radius:8px; border:1px solid #eee; margin-bottom:15px;">
                                                <h4 style="margin-top:0; color:#0F4E74; font-size:1.1rem;">
                                                    <?php echo v_wrap($rx_details['drug_name'] ?? $rx_details['medication_name']); ?>
                                                </h4>
                                                <p style="margin-bottom:5px;"><strong>Patient:</strong> <?php echo v_wrap($rx_details['patient_category'] . ' - ' . $rx_details['surname'] . ' ' . $rx_details['first_name']); ?></p>
                                                <p style="margin-bottom:5px;"><strong>Prescribing Doctor:</strong> <?php echo v_wrap($rx_details['doctor_name']); ?></p>
                                                <p style="margin-bottom:5px;"><strong>Dosage:</strong> <?php echo v_wrap($rx_details['dosage'] . ' - ' . $rx_details['frequency']); ?></p>
                                                <p style="margin-bottom:5px;"><strong>Duration:</strong> <?php echo v_wrap($rx_details['duration']); ?></p>
                                                <p style="margin-bottom:5px;"><strong>Instructions:</strong> <span style="background:#fff3cd; color:#856404; padding:2px 5px; border-radius:4px; font-size:0.9rem;"><?php echo v_wrap($rx_details['instructions']); ?></span></p>
                                                <p style="margin-bottom:0;"><strong>Status:</strong> <span class="badge status-pending" style="font-size:0.8rem;">Pending Pharmacy Dispensation</span></p>
                                            </div>

                                            <div style="background:#e8f4fd; color:#0c5460; padding:12px; border-radius:6px; font-size:0.85rem; border:1px solid #bee5eb;">
                                                <i class="bi bi-info-circle-fill" style="margin-right:5px;"></i> Only licensed pharmacy staff can dispense medication items and manage stock reductions.
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
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
