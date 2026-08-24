<?php
require_once('../../../private/config.php');
require_password_reset();

if (!isset($_SESSION['staff_role']) || ($_SESSION['staff_role'] !== 'receptionist' && $_SESSION['staff_role'] !== 'admin')) {
    $_SESSION['error'] = "Access Denied: Billing is restricted to Reception and Admin.";
    redirect_to(url_wrap('/staff/dashboard.php'));
}

if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    if ($action === 'mark_paid') {
        $bill_id = (int)$_POST['bill_id'];
        if (mark_bill_paid($bill_id)) {
            $_SESSION['message'] = "Bill marked as Paid successfully.";
        }
    }
    redirect_to('index.php');
}

$page_title = "Billing Dashboard";
$specificCss = "/assets/css/encounters.css"; 

$status_filter = $_GET['status'] ?? 'Pending';
$bills = get_all_bills($status_filter);

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    <main class="main-content">
        
        <div class="top">
            <p class="top-head">Billing Dashboard</p>
            <p class="top-description">Manage patient payments and outstanding bills.</p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div class="tabs" role="tablist">
            <a href="?status=Pending" class="tab-btn <?php echo $status_filter == 'Pending' ? 'active' : ''; ?>" style="text-decoration:none;">Pending Bills</a>
            <a href="?status=Paid" class="tab-btn <?php echo $status_filter == 'Paid' ? 'active' : ''; ?>" style="text-decoration:none;">Paid</a>
            <a href="?status=" class="tab-btn <?php echo $status_filter == '' ? 'active' : ''; ?>" style="text-decoration:none;">All Records</a>
        </div>

        <div class="encounter-list-container">
            <table class="staff-list">
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Category</th>
                    <th>Amount (₦)</th>
                    <th>Remarks</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
                <?php while($b = $bills->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo date('d M Y - h:i A', strtotime($b['created_at'])); ?></td>
                    <td><?php echo v_wrap($b['surname'] . ' ' . $b['first_name']); ?></td>
                    <td><span class="badge priority-normal"><?php echo v_wrap($b['billing_category']); ?></span></td>
                    <td><strong><?php echo number_format($b['amount'], 2); ?></strong></td>
                    <td><?php echo v_wrap($b['remarks']); ?></td>
                    <td>
                        <span class="badge status-<?php echo strtolower($b['payment_status']); ?>"><?php echo v_wrap($b['payment_status']); ?></span>
                    </td>
                    <td>
                        <?php if($b['payment_status'] == 'Pending') { ?>
                            <form action="index.php" method="post" style="display:inline;">
                                <input type="hidden" name="action" value="mark_paid">
                                <input type="hidden" name="bill_id" value="<?php echo $b['id']; ?>">
                                <button type="submit" class="btn btn-primary" style="background:#1bc03d; color:white; border:none; padding:5px 10px; border-radius:4px; cursor:pointer;" onclick="return confirm('Confirm payment received?');">
                                    Mark Paid
                                </button>
                            </form>
                        <?php } else { ?>
                            <span style="color:#888;"><i class="bi bi-check-circle-fill" style="color:#1bc03d;"></i> Cleared</span>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>
            </table>
        </div>
    </main>
</div>
<?php include(SHARED_PATH . '/footer.php'); ?>
