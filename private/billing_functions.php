<?php

function get_all_bills($status = null) {
    global $db_1;
    $sql = "SELECT b.*, p.first_name, p.surname, p.patient_category, e.encounter_number 
            FROM billing b 
            JOIN patients p ON b.patient_id = p.id 
            JOIN encounters e ON b.encounter_id = e.id ";
    if ($status) {
        $sql .= "WHERE b.payment_status = ? ";
    }
    $sql .= "ORDER BY b.created_at DESC";
    $query = $db_1->prepare($sql);
    if ($status) {
        $query->bind_param("s", $status);
    }
    $query->execute();
    return $query->get_result();
}

function mark_bill_paid($bill_id) {
    global $db_1;
    $sql = "UPDATE billing SET payment_status = 'Paid' WHERE id = ?";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $bill_id);
    return $query->execute();
}

?>
