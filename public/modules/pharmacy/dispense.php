<?php
require_once('../../../private/config.php');
require_password_reset();

if (!isset($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['pharmacist', 'admin', 'super_admin'])) {
    $_SESSION['error'] = "Access Denied: You do not have permission to dispense medication.";
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    $_SESSION['error'] = "Invalid prescription record.";
    redirect_to(url_wrap('/modules/pharmacy/index.php'));
}

$rx = get_prescription_details((int)$id);
if (!$rx || $rx['status'] != 'Pending') {
    $_SESSION['error'] = "Prescription not found or already dispensed.";
    redirect_to(url_wrap('/modules/pharmacy/index.php'));
}

if (is_post_request()) {
    $quantity = (int)$_POST['quantity'];
    $remarks = $_POST['remarks'] ?? '';
    
    // Dispense & Bill logic
    $staff_id = $_SESSION['staff_id'];
    if (dispense_prescription($rx['id'], $staff_id, $quantity, $remarks)) {
        $_SESSION['message'] = "Prescription dispensed successfully!";
        if ($rx['patient_category'] !== 'Student' && $rx['inventory_id']) {
            $_SESSION['message'] .= " A bill was automatically generated for the patient.";
        }
        redirect_to(url_wrap('/modules/pharmacy/index.php'));
    } else {
        $_SESSION['error'] = "Failed to dispense prescription.";
    }
}

$page_title = "Dispense Prescription";
$specificCss = "/assets/css/encounters.css"; 

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    <main class="main-content">
        <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" class="btn btn-primary" id="link_layout"><i class="bi bi-arrow-left"></i> Back to Queue</a>
        
        <div class="top">
            <p class="top-head">Dispensing Workspace</p>
            <p class="top-description">Review doctor's order and dispense medication.</p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div class="clinical-grid">
            
            <div class="card">
                <h3>Prescription Details</h3>
                <div style="background:#f9f9f9; padding:20px; border-radius:8px; border:1px solid #eee;">
                    <h2 style="margin-top:0; color:#0F4E74;">
                        <?php echo v_wrap($rx['drug_name'] ?? $rx['medication_name']); ?>
                    </h2>
                    
                    <p><strong>Dosage:</strong> <?php echo v_wrap($rx['dosage']); ?></p>
                    <p><strong>Frequency:</strong> <?php echo v_wrap($rx['frequency']); ?></p>
                    <p><strong>Duration:</strong> <?php echo v_wrap($rx['duration']); ?></p>
                    <p><strong>Doctor's Instructions:</strong> <br> 
                        <span style="background:#fff3cd; color:#856404; padding:5px 10px; border-radius:4px; display:inline-block; margin-top:5px;">
                            <?php echo v_wrap($rx['instructions']); ?>
                        </span>
                    </p>
                    <hr style="border:0; border-top:1px solid #ddd; margin:15px 0;">
                    <p><strong>Prescribed By:</strong> Dr. <?php echo v_wrap($rx['doctor_name']); ?></p>
                    <p><strong>Patient:</strong> <?php echo v_wrap($rx['surname'] . ' ' . $rx['first_name']); ?> <span class="badge status-pending"><?php echo v_wrap($rx['patient_category']); ?></span></p>
                </div>
            </div>

            <div class="card">
                <h3>Dispense Action</h3>
                <?php if ($rx['inventory_id']) { ?>
                    <p style="margin-bottom:20px;">
                        <strong>Current Stock Level:</strong> 
                        <span style="font-size:1.2rem; color:<?php echo $rx['stock_quantity'] > 10 ? 'green' : 'red'; ?>;">
                            <?php echo v_wrap($rx['stock_quantity']); ?> units
                        </span>
                        <br>
                        <strong>Unit Price:</strong> ₦<?php echo number_format($rx['unit_price'], 2); ?>
                        <br>
                        <?php if($rx['patient_category'] !== 'Student') { ?>
                            <span style="color:#721c24; font-size:0.9rem;">⚠️ Since patient is a <b><?php echo v_wrap($rx['patient_category']); ?></b>, dispensing will auto-generate a bill.</span>
                        <?php } else { ?>
                            <span style="color:#155724; font-size:0.9rem;">✔️ Patient is a <b>Student</b>. No bill will be generated.</span>
                        <?php } ?>
                    </p>

                    <form action="" method="post">
                        <div class="form-group">
                            <label>Quantity to Dispense *</label>
                            <input type="number" name="quantity" required min="1" max="<?php echo $rx['stock_quantity']; ?>" style="font-size:1.2rem;">
                        </div>
                        <div class="form-group">
                            <label>Pharmacist Remarks / Directions Given</label>
                            <textarea name="remarks" rows="3" placeholder="Notes for the patient..." style="width:100%; border-radius:5px; padding:10px; border:1px solid #ddd;"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#1bc03d; color:white; border:none; padding:12px 20px; border-radius:5px; font-size:1.1rem; width:100%;">
                            <i class="bi bi-check-circle"></i> Dispense Medication
                        </button>
                    </form>
                <?php } else { ?>
                    <div style="background:#f8d7da; color:#721c24; padding:20px; border-radius:8px;">
                        <strong>Error:</strong> This prescription was not selected from the official pharmacy inventory (likely a free-text entry from an older version). You cannot auto-dispense or auto-bill this item. Please consult the doctor to rewrite it using the inventory dropdown.
                    </div>
                <?php } ?>
            </div>

        </div>
    </main>
</div>
<?php include(SHARED_PATH . '/footer.php'); ?>
