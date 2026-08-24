<?php

function create_encounter($patient_id, $doctor_id, $appointment_id, $priority, $created_by) {
    global $db_1;
    
    // Generate unique encounter number (e.g. ENC-2026-0001)
    $year = date('Y');
    $like = "ENC-$year-%";
    
    $sql = "SELECT encounter_number FROM encounters WHERE encounter_number LIKE ? ORDER BY id DESC LIMIT 1";
    $query = $db_1->prepare($sql);
    $query->bind_param("s", $like);
    $query->execute();
    $result = $query->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $last_id = $row['encounter_number'];
        $last_number = (int) substr($last_id, -4);
        $next_number = $last_number + 1;
    } else {
        $next_number = 1;
    }
    $query->close();
    
    $encounter_number = "ENC-$year-" . str_pad($next_number, 4, '0', STR_PAD_LEFT);
    $status = 'Waiting';
    
    $sql_insert = "INSERT INTO encounters (encounter_number, appointment_id, patient_id, doctor_id, created_by, priority, status) ";
    $sql_insert .= "VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $query_insert = $db_1->prepare($sql_insert);
    $query_insert->bind_param("siiiiss", $encounter_number, $appointment_id, $patient_id, $doctor_id, $created_by, $priority, $status);
    $query_insert->execute();
    
    $new_id = $db_1->insert_id;
    $query_insert->close();
    
    // If created from an appointment, update appointment status to Checked In
    if ($appointment_id) {
        $sql_app = "UPDATE appointments SET status = 'Checked In', check_in_time = NOW() WHERE id = ?";
        $query_app = $db_1->prepare($sql_app);
        $query_app->bind_param("i", $appointment_id);
        $query_app->execute();
        $query_app->close();
    }
    
    return $new_id;
}

function find_all_encounters($status = null) {
    global $db_1;
    
    $sql = "SELECT e.*, p.first_name as patient_first, p.surname as patient_last, p.patient_id as p_id, d.full_name as doctor_name ";
    $sql .= "FROM encounters e ";
    $sql .= "JOIN patients p ON e.patient_id = p.id ";
    $sql .= "JOIN staff d ON e.doctor_id = d.id ";
    
    if ($status) {
        $sql .= "WHERE e.status = ? ";
    }
    
    $sql .= "ORDER BY e.created_at DESC";
    
    $query = $db_1->prepare($sql);
    
    if ($status) {
        $query->bind_param("s", $status);
    }
    
    $query->execute();
    return $query->get_result();
}

function find_encounter_by_id($id) {
    global $db_1;
    
    $sql = "SELECT e.*, p.first_name as patient_first, p.surname as patient_last, p.patient_category, p.gender, p.date_of_birth, p.blood_group, p.profile_image, p.patient_id as p_id, d.full_name as doctor_name ";
    $sql .= "FROM encounters e ";
    $sql .= "JOIN patients p ON e.patient_id = p.id ";
    $sql .= "JOIN staff d ON e.doctor_id = d.id ";
    $sql .= "WHERE e.id = ?";
    
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $id);
    $query->execute();
    
    $result = $query->get_result();
    return $result->fetch_assoc();
}

function update_encounter_status($id, $status) {
    global $db_1;
    $sql = "UPDATE encounters SET status = ?";
    
    if ($status == 'In Progress') {
        $sql .= ", started_at = NOW() ";
    } elseif ($status == 'Completed') {
        $sql .= ", completed_at = NOW() ";
    }
    
    $sql .= "WHERE id = ?";
    
    $query = $db_1->prepare($sql);
    $query->bind_param("si", $status, $id);
    $query->execute();
    return $query->affected_rows > 0;
}

// --- Vitals ---
function get_vitals($encounter_id) {
    global $db_1;
    $sql = "SELECT * FROM vitals WHERE encounter_id = ? ORDER BY created_at DESC LIMIT 1";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $encounter_id);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

function save_vitals($encounter_id, $nurse_id, $temp, $weight, $height, $bmi, $pulse, $respiration, $oxygen, $sys_bp, $dia_bp) {
    global $db_1;
    $sql = "INSERT INTO vitals (encounter_id, nurse_id, temperature, weight, height, bmi, pulse, respiration, oxygen_saturation, systolic_bp, diastolic_bp) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $db_1->prepare($sql);
    $query->bind_param("iidddiiiiii", $encounter_id, $nurse_id, $temp, $weight, $height, $bmi, $pulse, $respiration, $oxygen, $sys_bp, $dia_bp);
    $query->execute();
    return $db_1->insert_id;
}

function get_nursing_notes($encounter_id) {
    global $db_1;
    $sql = "SELECT * FROM nursing_notes WHERE encounter_id = ? ORDER BY created_at DESC";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $encounter_id);
    $query->execute();
    return $query->get_result();
}

function save_nursing_note($encounter_id, $nurse_id, $notes) {
    global $db_1;
    $sql = "INSERT INTO nursing_notes (encounter_id, nurse_id, notes) VALUES (?, ?, ?)";
    $query = $db_1->prepare($sql);
    $query->bind_param("iis", $encounter_id, $nurse_id, $notes);
    $query->execute();
    return $db_1->insert_id;
}

// --- Consultation ---
function get_consultation($encounter_id) {
    global $db_1;
    $sql = "SELECT * FROM consultations WHERE encounter_id = ? LIMIT 1";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $encounter_id);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

function save_consultation($encounter_id, $doctor_id, $hpi, $exam, $assessment, $plan, $follow_up) {
    global $db_1;
    // Check if exists
    $existing = get_consultation($encounter_id);
    if ($existing) {
        $sql = "UPDATE consultations SET history_of_present_illness=?, physical_examination=?, assessment=?, management_plan=?, follow_up_instructions=? WHERE encounter_id=?";
        $query = $db_1->prepare($sql);
        $query->bind_param("sssssi", $hpi, $exam, $assessment, $plan, $follow_up, $encounter_id);
    } else {
        $sql = "INSERT INTO consultations (encounter_id, doctor_id, history_of_present_illness, physical_examination, assessment, management_plan, follow_up_instructions) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query = $db_1->prepare($sql);
        $query->bind_param("iisssss", $encounter_id, $doctor_id, $hpi, $exam, $assessment, $plan, $follow_up);
    }
    $query->execute();
    return true;
}

// --- Diagnosis ---
function get_diagnoses($encounter_id) {
    global $db_1;
    $sql = "SELECT * FROM diagnoses WHERE encounter_id = ? ORDER BY created_at ASC";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $encounter_id);
    $query->execute();
    return $query->get_result();
}

function save_diagnosis($encounter_id, $doctor_id, $type, $diagnosis, $icd, $notes) {
    global $db_1;
    $sql = "INSERT INTO diagnoses (encounter_id, doctor_id, diagnosis_type, diagnosis, icd_code, notes) VALUES (?, ?, ?, ?, ?, ?)";
    $query = $db_1->prepare($sql);
    $query->bind_param("iissss", $encounter_id, $doctor_id, $type, $diagnosis, $icd, $notes);
    $query->execute();
    return $db_1->insert_id;
}

// --- Prescriptions ---
function get_prescriptions($encounter_id) {
    global $db_1;
    $sql = "SELECT p.*, i.drug_name as medication_name FROM prescriptions p LEFT JOIN pharmacy_inventory i ON p.inventory_id = i.id WHERE p.encounter_id = ? ORDER BY p.created_at ASC";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $encounter_id);
    $query->execute();
    return $query->get_result();
}

function save_prescription($encounter_id, $doctor_id, $inventory_id, $dosage, $freq, $duration, $instructions) {
    global $db_1;
    $sql = "INSERT INTO prescriptions (encounter_id, doctor_id, inventory_id, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $query = $db_1->prepare($sql);
    $query->bind_param("iiissss", $encounter_id, $doctor_id, $inventory_id, $dosage, $freq, $duration, $instructions);
    $query->execute();
    return $db_1->insert_id;
}

function get_patient_medical_history($patient_id) {
    global $db_1;
    $sql = "SELECT e.*, d.full_name as doctor_name 
            FROM encounters e 
            LEFT JOIN staff d ON e.doctor_id = d.id 
            WHERE e.patient_id = ? AND e.status = 'Completed' 
            ORDER BY e.created_at DESC";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $patient_id);
    $query->execute();
    return $query->get_result();
}

?>
