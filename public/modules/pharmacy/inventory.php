<?php
require_once('../../../private/config.php');
require_password_reset();

if (!isset($_SESSION['staff_role']) || !in_array($_SESSION['staff_role'], ['pharmacist', 'admin', 'super_admin'])) {
    $_SESSION['error'] = "Access Denied: You do not have permission to access drug inventory.";
    redirect_to(url_wrap('/staff/dashboard.php'));
}

$page_title = "Pharmacy Inventory";
$specificCss = "/assets/css/encounters.css"; // Reuse card layout

if (isset($_GET['download_template']) && $_GET['download_template'] == '1') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="pharmacy_inventory_template.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['drug_name', 'category', 'unit_price', 'stock_quantity']);
    fputcsv($output, ['Paracetamol 500mg', 'Tablet', '10.00', '200']);
    fputcsv($output, ['Amoxicillin 500mg', 'Capsule', '50.00', '100']);
    fputcsv($output, ['Cough Syrup 100ml', 'Syrup', '450.00', '50']);
    fputcsv($output, ['Hydrocortisone 1%', 'Ointment', '300.00', '30']);
    fputcsv($output, ['Vitamin C 100mg', 'Tablet', '15.00', '500']);
    fclose($output);
    exit;
}

if (isset($_GET['export_inventory']) && $_GET['export_inventory'] == '1') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="futa_pharmacy_current_inventory_' . date('Y_m_d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['id', 'drug_name', 'category', 'unit_price', 'stock_quantity']);
    $all_inv = get_all_inventory();
    while ($row = $all_inv->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['drug_name'],
            $row['category'],
            number_format((float)$row['unit_price'], 2, '.', ''),
            $row['stock_quantity']
        ]);
    }
    fclose($output);
    exit;
}

if (is_post_request()) {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_drug') {
        add_inventory_item($_POST['drug_name'], $_POST['category'], $_POST['unit_price'], $_POST['stock_quantity']);
        $_SESSION['message'] = "New drug added to inventory.";
    } elseif ($action === 'add_stock') {
        update_inventory_stock($_POST['inventory_id'], $_POST['stock_change']);
        $_SESSION['message'] = "Stock updated.";
    } elseif ($action === 'edit_drug') {
        $edit_id = (int)($_POST['inventory_id'] ?? 0);
        $edit_name = trim($_POST['drug_name'] ?? '');
        $edit_cat = trim($_POST['category'] ?? 'Tablet');
        $edit_price = (float)($_POST['unit_price'] ?? 0);
        if ($edit_id > 0 && !empty($edit_name)) {
            $up_stmt = $db_1->prepare("UPDATE pharmacy_inventory SET drug_name = ?, category = ?, unit_price = ? WHERE id = ?");
            $up_stmt->bind_param("ssdi", $edit_name, $edit_cat, $edit_price, $edit_id);
            $up_stmt->execute();
            $up_stmt->close();
            $_SESSION['message'] = "Medication '{$edit_name}' pricing and details updated successfully.";
        } else {
            $_SESSION['error'] = "Invalid drug data provided.";
        }
    } elseif ($action === 'import_csv') {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "File upload failed. Please choose a valid CSV file.";
        } else {
            $file = $_FILES['csv_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if ($ext !== 'csv') {
                $_SESSION['error'] = "Invalid file type. Please upload a .csv file.";
            } else {
                $handle = fopen($file['tmp_name'], 'r');
                if ($handle === false) {
                    $_SESSION['error'] = "Could not open uploaded file.";
                } else {
                    $raw_headers = fgetcsv($handle);
                    if (!$raw_headers) {
                        $_SESSION['error'] = "The uploaded CSV file is empty.";
                    } else {
                        $headers = array_map(function($h) {
                            return strtolower(trim(str_replace([' ', '_', '-'], '', $h)));
                        }, $raw_headers);

                        $mode = $_POST['import_mode'] ?? 'restock';
                        $added = 0;
                        $updated = 0;
                        $skipped = 0;

                        while (($row = fgetcsv($handle)) !== false) {
                            if (empty(array_filter($row))) continue;
                            $data = array_combine($headers, $row);
                            if (!$data) continue;

                            $name = trim($data['drugname'] ?? $data['name'] ?? $data['medication'] ?? '');
                            if (empty($name)) {
                                $skipped++;
                                continue;
                            }

                            $cat = trim($data['category'] ?? 'Tablet');
                            if (!in_array($cat, ['Tablet', 'Capsule', 'Syrup', 'Injection', 'Ointment', 'Other'])) {
                                $cat = 'Other';
                            }

                            $price = isset($data['unitprice']) ? (float)$data['unitprice'] : (isset($data['price']) ? (float)$data['price'] : 0.0);
                            $qty = isset($data['stockquantity']) ? (int)$data['stockquantity'] : (isset($data['stock']) ? (int)$data['stock'] : (isset($data['quantity']) ? (int)$data['quantity'] : 0));

                            // Check if drug already exists by ID (if exported) or by name
                            $row_id = isset($data['id']) ? (int)$data['id'] : 0;
                            $existing = null;
                            if ($row_id > 0) {
                                $check_stmt = $db_1->prepare("SELECT id, stock_quantity FROM pharmacy_inventory WHERE id = ? LIMIT 1");
                                $check_stmt->bind_param("i", $row_id);
                                $check_stmt->execute();
                                $existing = $check_stmt->get_result()->fetch_assoc();
                                $check_stmt->close();
                            }
                            if (!$existing) {
                                $check_stmt = $db_1->prepare("SELECT id, stock_quantity FROM pharmacy_inventory WHERE LOWER(drug_name) = LOWER(?) LIMIT 1");
                                $check_stmt->bind_param("s", $name);
                                $check_stmt->execute();
                                $existing = $check_stmt->get_result()->fetch_assoc();
                                $check_stmt->close();
                            }

                            if ($existing) {
                                $item_id = (int)$existing['id'];
                                if ($mode === 'price_only') {
                                    $up_stmt = $db_1->prepare("UPDATE pharmacy_inventory SET unit_price = IF(? > 0, ?, unit_price), category = IF(? != '', ?, category) WHERE id = ?");
                                    $up_stmt->bind_param("dsssi", $price, $price, $cat, $cat, $item_id);
                                } elseif ($mode === 'overwrite') {
                                    $up_stmt = $db_1->prepare("UPDATE pharmacy_inventory SET stock_quantity = ?, unit_price = IF(? > 0, ?, unit_price), category = IF(? != '', ?, category) WHERE id = ?");
                                    $up_stmt->bind_param("idsssi", $qty, $price, $price, $cat, $cat, $item_id);
                                } else {
                                    $up_stmt = $db_1->prepare("UPDATE pharmacy_inventory SET stock_quantity = stock_quantity + ?, unit_price = IF(? > 0, ?, unit_price), category = IF(? != '', ?, category) WHERE id = ?");
                                    $up_stmt->bind_param("idsssi", $qty, $price, $price, $cat, $cat, $item_id);
                                }
                                $up_stmt->execute();
                                $up_stmt->close();
                                $updated++;
                            } else {
                                $ins_stmt = $db_1->prepare("INSERT INTO pharmacy_inventory (drug_name, category, unit_price, stock_quantity) VALUES (?, ?, ?, ?)");
                                $ins_stmt->bind_param("ssdi", $name, $cat, $price, $qty);
                                $ins_stmt->execute();
                                $ins_stmt->close();
                                $added++;
                            }
                        }
                        fclose($handle);
                        $_SESSION['message'] = "Bulk inventory processing completed: {$added} new drug(s) added, {$updated} existing drug(s) restocked/updated.";
                        if ($skipped > 0) {
                            $_SESSION['message'] .= " ({$skipped} invalid rows skipped).";
                        }
                    }
                }
            }
        }
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
            <div class="card" style="grid-column: span 2; display: flex; justify-content: space-between; align-items: center; background: none; box-shadow: none; padding: 0; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0;">Current Inventory</h3>
                <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                    <a href="?export_inventory=1" class="btn" style="background:#17a2b8; color:white; border:none; padding:10px 15px; border-radius:5px; text-decoration:none; font-size:0.88rem; display:inline-flex; align-items:center; gap:6px;">
                        <i class="bi bi-file-earmark-arrow-down-fill"></i> Export Current Inventory (CSV)
                    </a>
                    <a href="?download_template=1" class="btn" style="background:#6c757d; color:white; border:none; padding:10px 15px; border-radius:5px; text-decoration:none; font-size:0.88rem; display:inline-flex; align-items:center; gap:6px;">
                        <i class="bi bi-file-earmark-arrow-down"></i> CSV Template
                    </a>
                    <button data-modal-target="bulkImportModal" class="btn" style="background:#28a745; color:white; border:none; padding:10px 16px; border-radius:5px; font-size:0.88rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i class="bi bi-file-earmark-spreadsheet"></i> Bulk Import / Restock (CSV)
                    </button>
                    <button data-modal-target="addDrugModal" class="btn btn-primary" style="background:#0F4E74; color:white; border:none; padding:10px 18px; border-radius:5px; font-size:0.88rem; cursor:pointer; display:inline-flex; align-items:center; gap:6px;">
                        <i class="bi bi-plus-lg"></i> Add New Drug
                    </button>
                </div>
            </div>

            <!-- Bulk Import / Restock Modal -->
            <div id="bulkImportModal" class="modal-overlay">
                <div class="modal-content" style="max-width: 540px; text-align: left;">
                    <button class="modal-close" data-modal-close>&times;</button>
                    <h3 class="modal-title" style="color: #0F4E74; margin-bottom: 12px;"><i class="bi bi-file-earmark-spreadsheet"></i> Bulk Import & Restock</h3>
                    
                    <div style="background: #e7f3ff; border: 1px solid #b6d4fe; color: #084298; padding: 12px 15px; border-radius: 6px; font-size: 0.88rem; margin-bottom: 18px;">
                        <strong>Expected CSV Columns:</strong> <code>drug_name, category, unit_price, stock_quantity</code><br>
                        <span style="font-size:0.82rem; color:#444;">Tip: Use <strong>"Export Current Inventory (CSV)"</strong> to download live data, update prices/stock in Excel, and upload back here!</span>
                    </div>

                    <form action="" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="import_csv">
                        
                        <div class="form-group" style="margin-bottom: 16px;">
                            <label style="display:block; margin-bottom: 6px; font-weight: 500;">Select CSV File *</label>
                            <input type="file" name="csv_file" accept=".csv" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                        </div>

                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="display:block; margin-bottom: 8px; font-weight: 500;">Import Mode for Existing Drugs:</label>
                            <label style="display:block; margin-bottom: 8px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="import_mode" value="restock" checked style="margin-right: 6px;">
                                <strong>Add to current stock (Physical Restocking)</strong>
                                <span style="display:block; color:#666; font-size:0.82rem; margin-left: 20px;">Increases existing stock counts by the number in your CSV.</span>
                            </label>
                            <label style="display:block; margin-bottom: 8px; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="import_mode" value="overwrite" style="margin-right: 6px;">
                                <strong>Set exact stock count (Audit / Physical Count)</strong>
                                <span style="display:block; color:#666; font-size:0.82rem; margin-left: 20px;">Replaces existing stock counts with the exact number in your CSV.</span>
                            </label>
                            <label style="display:block; cursor: pointer; font-size: 0.9rem;">
                                <input type="radio" name="import_mode" value="price_only" style="margin-right: 6px;">
                                <strong>Update Unit Prices only (Preserve stock counts)</strong>
                                <span style="display:block; color:#666; font-size:0.82rem; margin-left: 20px;">Updates only unit prices of existing drugs from the CSV without changing stock.</span>
                            </label>
                            <small style="color: #0F4E74; display: block; margin-top: 10px; font-style: italic;">Note: Any medication not already in inventory will be automatically created as a new drug record.</small>
                        </div>

                        <button type="submit" class="btn btn-primary" style="background: #28a745; color: white; border: none; padding: 12px; border-radius: 6px; width: 100%; font-weight: 600; cursor: pointer; font-size: 0.95rem;">
                            <i class="bi bi-upload"></i> Upload & Process Inventory
                        </button>
                    </form>
                </div>
            </div>

            <!-- Add Drug Modal -->
            <div id="addDrugModal" class="modal-overlay">
                <div class="modal-content">
                    <button class="modal-close" data-modal-close>&times;</button>
                    <h3 class="modal-title"><i class="bi bi-capsule"></i> Add New Drug</h3>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="add_drug">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Drug Name *</label>
                            <input type="text" name="drug_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Category *</label>
                            <select name="category" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Ointment">Ointment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Unit Price (₦) *</label>
                            <input type="number" step="0.01" name="unit_price" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Initial Stock *</label>
                            <input type="number" name="stock_quantity" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                        <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width: 100%;">Add Drug</button>
                    </form>
                </div>
            </div>

            <!-- Edit Drug Modal -->
            <div id="editDrugModal" class="modal-overlay">
                <div class="modal-content">
                    <button class="modal-close" data-modal-close>&times;</button>
                    <h3 class="modal-title"><i class="bi bi-pencil-square"></i> Edit Medication & Pricing</h3>
                    <form action="" method="post">
                        <input type="hidden" name="action" value="edit_drug">
                        <input type="hidden" name="inventory_id" id="edit_inv_id">
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Drug Name *</label>
                            <input type="text" name="drug_name" id="edit_inv_name" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Category *</label>
                            <select name="category" id="edit_inv_category" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                                <option value="Tablet">Tablet</option>
                                <option value="Capsule">Capsule</option>
                                <option value="Syrup">Syrup</option>
                                <option value="Injection">Injection</option>
                                <option value="Ointment">Ointment</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 15px;">
                            <label>Unit Price (₦) *</label>
                            <input type="number" step="0.01" name="unit_price" id="edit_inv_price" required style="width:100%; padding:10px; border:1px solid #ddd; border-radius:5px;">
                        </div>
                        <p style="color:#666; font-size:0.85rem; margin-top:5px;">Note: To adjust stock, use the stock update field on the inventory table.</p>
                        <button type="submit" class="btn btn-primary" style="margin-top:15px; background:#0F4E74; color:white; border:none; padding:10px; border-radius:5px; width: 100%;">Save Changes</button>
                    </form>
                </div>
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
                        <th>Actions</th>
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
                        <td>
                            <button type="button" class="btn btn-edit-drug" 
                                    data-modal-target="editDrugModal"
                                    data-id="<?php echo $item['id']; ?>"
                                    data-name="<?php echo htmlspecialchars($item['drug_name']); ?>"
                                    data-category="<?php echo htmlspecialchars($item['category']); ?>"
                                    data-price="<?php echo htmlspecialchars($item['unit_price']); ?>"
                                    style="background:#0F4E74; color:white; border:none; padding:6px 12px; border-radius:4px; font-size:0.85rem; cursor:pointer; display:inline-flex; align-items:center; gap:4px;">
                                <i class="bi bi-pencil-square"></i> Edit Price
                            </button>
                        </td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-edit-drug').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('edit_inv_id').value = this.dataset.id;
            document.getElementById('edit_inv_name').value = this.dataset.name;
            document.getElementById('edit_inv_category').value = this.dataset.category;
            document.getElementById('edit_inv_price').value = this.dataset.price;
        });
    });
});
</script>

<?php include(SHARED_PATH . '/footer.php'); ?>
