<?php

// --- Inventory Functions ---

function get_all_inventory() {
    global $db_1;
    $sql = "SELECT * FROM pharmacy_inventory ORDER BY drug_name ASC";
    $result = $db_1->query($sql);
    return $result;
}

function get_inventory_item($id) {
    global $db_1;
    $sql = "SELECT * FROM pharmacy_inventory WHERE id = ?";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

function add_inventory_item($name, $category, $price, $stock) {
    global $db_1;
    $sql = "INSERT INTO pharmacy_inventory (drug_name, category, unit_price, stock_quantity) VALUES (?, ?, ?, ?)";
    $query = $db_1->prepare($sql);
    $query->bind_param("ssdi", $name, $category, $price, $stock);
    $query->execute();
    return $db_1->insert_id;
}

function update_inventory_stock($id, $stock_change) {
    global $db_1;
    $sql = "UPDATE pharmacy_inventory SET stock_quantity = stock_quantity + ? WHERE id = ?";
    $query = $db_1->prepare($sql);
    $query->bind_param("ii", $stock_change, $id);
    $query->execute();
    return $query->affected_rows > 0;
}

// --- Prescription & Dispensing ---

function get_all_prescriptions($status = null) {
    global $db_1;
    $sql = "SELECT pr.*, e.encounter_number, e.patient_id, p.first_name, p.surname, p.patient_category, d.full_name as doctor_name, inv.drug_name, inv.unit_price ";
    $sql .= "FROM prescriptions pr ";
    $sql .= "JOIN encounters e ON pr.encounter_id = e.id ";
    $sql .= "JOIN patients p ON e.patient_id = p.id ";
    $sql .= "JOIN staff d ON pr.doctor_id = d.id ";
    $sql .= "LEFT JOIN pharmacy_inventory inv ON pr.inventory_id = inv.id ";
    
    if ($status) {
        $sql .= "WHERE pr.status = ? ";
    }
    
    $sql .= "ORDER BY pr.created_at DESC";
    
    $query = $db_1->prepare($sql);
    if ($status) {
        $query->bind_param("s", $status);
    }
    $query->execute();
    return $query->get_result();
}

function get_prescription_details($id) {
    global $db_1;
    $sql = "SELECT pr.*, e.encounter_number, e.patient_id as e_patient_id, p.first_name, p.surname, p.patient_category, d.full_name as doctor_name, inv.drug_name, inv.unit_price, inv.stock_quantity ";
    $sql .= "FROM prescriptions pr ";
    $sql .= "JOIN encounters e ON pr.encounter_id = e.id ";
    $sql .= "JOIN patients p ON e.patient_id = p.id ";
    $sql .= "JOIN staff d ON pr.doctor_id = d.id ";
    $sql .= "LEFT JOIN pharmacy_inventory inv ON pr.inventory_id = inv.id ";
    $sql .= "WHERE pr.id = ?";
    
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

function dispense_prescription($prescription_id, $pharmacist_id, $quantity, $remarks) {
    global $db_1;
    
    // 1. Get the prescription details to know the inventory item and patient category
    $rx = get_prescription_details($prescription_id);
    if (!$rx) return false;
    
    // 2. Insert into dispensing_records
    $sql = "INSERT INTO dispensing_records (prescription_id, pharmacist_id, quantity_dispensed, remarks, status, dispensed_at) VALUES (?, ?, ?, ?, 'Dispensed', NOW())";
    $query = $db_1->prepare($sql);
    $query->bind_param("iiis", $prescription_id, $pharmacist_id, $quantity, $remarks);
    $query->execute();
    $dispense_id = $db_1->insert_id;
    
    // 3. Update prescription status
    $sql2 = "UPDATE prescriptions SET status = 'Dispensed' WHERE id = ?";
    $q2 = $db_1->prepare($sql2);
    $q2->bind_param("i", $prescription_id);
    $q2->execute();
    
    // 4. Deduct inventory
    if ($rx['inventory_id']) {
        update_inventory_stock($rx['inventory_id'], -$quantity);
    }
    
    // 5. Trigger Billing if patient is NOT a Student
    if ($rx['patient_category'] !== 'Student' && $rx['inventory_id']) {
        $bill_amount = $quantity * $rx['unit_price'];
        
        $bill_remarks = "Pharmacy Dispensing: " . $quantity . "x " . $rx['drug_name'];
        
        $sql3 = "INSERT INTO billing (encounter_id, patient_id, billing_category, amount, payment_status, remarks) VALUES (?, ?, ?, ?, 'Pending', ?)";
        $q3 = $db_1->prepare($sql3);
        $encounter_id = $rx['encounter_id'];
        $patient_id = $rx['e_patient_id'];
        $cat = $rx['patient_category'];
        
        $q3->bind_param("iisds", $encounter_id, $patient_id, $cat, $bill_amount, $bill_remarks);
        $q3->execute();
    }
    
    return true;
}

?>
