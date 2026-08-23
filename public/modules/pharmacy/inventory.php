<?php
require_once('../../../private/config.php');
require_password_reset();

$page_title = "Pharmacy Inventory";
$specificCss = "/assets/css/encounters.css"; // Reuse card layout

if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_drug') {
        add_inventory_item($_POST['drug_name'], $_POST['category'], $_POST['unit_price'], $_POST['stock_quantity']);
        $_SESSION['message'] = "New drug added to inventory.";
    } elseif ($action === 'add_stock') {
        update_inventory_stock($_POST['inventory_id'], $_POST['stock_change']);
        $_SESSION['message'] = "Stock updated.";
    }
    
    redirect_to(url_wrap('/modules/pharmacy/inventory.php'));
}

$inventory = get_all_inventory();

include(SHARED_PATH . '/header.php');
?>

<div id="content">
    <?php include(SHARED_PATH . '/navigation.php'); ?>
    <main class="main-content">
        <a href="<?php echo url_wrap('/modules/pharmacy/index.php'); ?>" class="btn btn-primary" id="link_layout"><i class="bi bi-arrow-left"></i> Back to Pharmacy Queue</a>
        
        <div class="top">
            <p class="top-head">Pharmacy Inventory</p>
            <p class="top-description">Manage drug stock levels and pricing.</p>
        </div>

        <div><?php echo display_session_message(); ?></div>

        <div class="clinical-grid">
            <div class="card">
                <h3>Add New Drug</h3>
                <form action="" method="post">
                    <input type="hidden" name="action" value="add_drug">
                    <div class="form-group">
                        <label>Drug Name *</label>
                        <input type="text" name="drug_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="Tablet">Tablet</option>
                            <option value="Capsule">Capsule</option>
                            <option value="Syrup">Syrup</option>
                            <option value="Injection">Injection</option>
                            <option value="Ointment">Ointment</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Unit Price (₦) *</label>
                        <input type="number" step="0.01" name="unit_price" required>
                    </div>
                    <div class="form-group">
                        <label>Initial Stock *</label>
                        <input type="number" name="stock_quantity" required>
                    </div>
                    <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:8px 15px; border-radius:5px;">Add Drug</button>
                </form>
            </div>
            
            <div class="card" style="grid-column: span 2;">
                <h3>Current Inventory</h3>
                <table class="staff-list">
                    <tr>
                        <th>ID</th>
                        <th>Drug Name</th>
                        <th>Category</th>
                        <th>Unit Price</th>
                        <th>Stock Available</th>
                        <th>Update Stock</th>
                    </tr>
                    <?php while($item = $inventory->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo v_wrap($item['id']); ?></td>
                        <td><strong><?php echo v_wrap($item['drug_name']); ?></strong></td>
                        <td><span class="badge status-<?php echo strtolower($item['category']); ?>"><?php echo v_wrap($item['category']); ?></span></td>
                        <td>₦<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td>
                            <span style="font-weight:bold; color: <?php echo $item['stock_quantity'] < 20 ? 'red' : 'green'; ?>">
                                <?php echo v_wrap($item['stock_quantity']); ?>
                            </span>
                        </td>
                        <td>
                            <form action="" method="post" style="display:flex; gap:10px;">
                                <input type="hidden" name="action" value="add_stock">
                                <input type="hidden" name="inventory_id" value="<?php echo $item['id']; ?>">
                                <input type="number" name="stock_change" placeholder="e.g. 50 or -10" required style="width:100px; padding:5px;">
                                <button type="submit" class="btn btn-primary" style="background:#0F4E74; color:white; border:none; border-radius:4px;">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </main>
</div>
<?php include(SHARED_PATH . '/footer.php'); ?>
