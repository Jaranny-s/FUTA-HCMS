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

function find_all_encounters($status = null, $doctor_id = null) {
    global $db_1;
    
    $sql = "SELECT e.*, p.first_name as patient_first, p.surname as patient_last, p.patient_id as p_id, COALESCE(d.full_name, 'Unassigned') as doctor_name ";
    $sql .= "FROM encounters e ";
    $sql .= "JOIN patients p ON e.patient_id = p.id ";
    $sql .= "LEFT JOIN staff d ON e.doctor_id = d.id ";
    
    $conditions = [];
    $types = "";
    $params = [];

    if ($status) {
        $conditions[] = "e.status = ?";
        $types .= "s";
        $params[] = $status;
    }

    if ($doctor_id) {
        $conditions[] = "e.doctor_id = ?";
        $types .= "i";
        $params[] = $doctor_id;
    }

    if (!empty($conditions)) {
        $sql .= "WHERE " . implode(" AND ", $conditions) . " ";
    }
    
    $sql .= "ORDER BY e.created_at DESC";
    
    $query = $db_1->prepare($sql);
    
    if (!empty($params)) {
        $query->bind_param($types, ...$params);
    }
    
    $query->execute();
    return $query->get_result();
}

function find_encounter_by_id($id) {
    global $db_1;
    
    $sql = "SELECT e.*, p.first_name as patient_first, p.surname as patient_last, p.patient_category, p.gender, p.date_of_birth, p.blood_group, p.profile_image, p.patient_id as p_id, COALESCE(d.full_name, 'Unassigned') as doctor_name ";
    $sql .= "FROM encounters e ";
    $sql .= "JOIN patients p ON e.patient_id = p.id ";
    $sql .= "LEFT JOIN staff d ON e.doctor_id = d.id ";
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
    $affected = $query->affected_rows > 0;
    $query->close();

    // If completed and linked to an appointment, synchronize appointment status
    if ($status === 'Completed') {
        $app_q = $db_1->prepare("SELECT appointment_id FROM encounters WHERE id = ?");
        $app_q->bind_param("i", $id);
        $app_q->execute();
        $res = $app_q->get_result();
        if ($row = $res->fetch_assoc()) {
            if (!empty($row['appointment_id'])) {
                $up_app = $db_1->prepare("UPDATE appointments SET status = 'Completed' WHERE id = ?");
                $up_app->bind_param("i", $row['appointment_id']);
                $up_app->execute();
                $up_app->close();
            }
        }
        $app_q->close();
    }

    return $affected;
}

function cancel_encounter($id, $reason, $staff_id = null) {
    global $db_1;
    $sql = "UPDATE encounters SET status = 'Cancelled', cancelled_at = NOW(), cancel_reason = ? WHERE id = ?";
    $query = $db_1->prepare($sql);
    $query->bind_param("si", $reason, $id);
    $query->execute();
    $affected = $query->affected_rows > 0;
    $query->close();

    // If linked to an appointment, synchronize appointment status to Cancelled
    $app_q = $db_1->prepare("SELECT appointment_id FROM encounters WHERE id = ?");
    $app_q->bind_param("i", $id);
    $app_q->execute();
    $res = $app_q->get_result();
    if ($row = $res->fetch_assoc()) {
        if (!empty($row['appointment_id'])) {
            $up_app = $db_1->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ?");
            $up_app->bind_param("i", $row['appointment_id']);
            $up_app->execute();
            $up_app->close();
        }
    }
    $app_q->close();

    if (function_exists('log_action') && $staff_id) {
        log_action($staff_id, 'Cancel Encounter', "Encounter #{$id} was cancelled. Reason: {$reason}");
    }

    return $affected;
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
    $sql = "SELECT p.*, COALESCE(NULLIF(p.medication_name, ''), i.drug_name, 'Prescribed Medication') as medication_name FROM prescriptions p LEFT JOIN pharmacy_inventory i ON p.inventory_id = i.id WHERE p.encounter_id = ? ORDER BY p.created_at ASC";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $encounter_id);
    $query->execute();
    return $query->get_result();
}

function save_prescription($encounter_id, $doctor_id, $inventory_id, $dosage, $freq, $duration, $instructions, $custom_name = '') {
    global $db_1;
    
    $medication_name = !empty(trim($custom_name)) ? trim($custom_name) : 'Prescribed Medication';
    $inv_id = !empty($inventory_id) ? (int)$inventory_id : null;
    if ($inv_id) {
        $inv_q = $db_1->prepare("SELECT drug_name FROM pharmacy_inventory WHERE id = ?");
        $inv_q->bind_param("i", $inv_id);
        $inv_q->execute();
        $res = $inv_q->get_result();
        if ($row = $res->fetch_assoc()) {
            $medication_name = $row['drug_name'];
        }
        $inv_q->close();
    }

    $sql = "INSERT INTO prescriptions (encounter_id, doctor_id, inventory_id, medication_name, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $query = $db_1->prepare($sql);
    $query->bind_param("iiisssss", $encounter_id, $doctor_id, $inv_id, $medication_name, $dosage, $freq, $duration, $instructions);
    $query->execute();
    $new_id = $db_1->insert_id;
    $query->close();
    return $new_id;
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

/**
 * Retrieves all doctors with their duty_status and live waiting/active queue counts.
 */
function get_doctors_workload_summary() {
    global $db_1;
    $sql = "SELECT s.id, s.full_name, s.email, s.department, s.duty_status, s.status as account_status,
                   COUNT(CASE WHEN e.status = 'Waiting' THEN 1 END) as waiting_count,
                   COUNT(CASE WHEN e.status = 'In Progress' THEN 1 END) as in_progress_count,
                   COUNT(CASE WHEN e.status IN ('Waiting', 'In Progress') THEN 1 END) as total_active_queue
            FROM staff s
            LEFT JOIN encounters e ON s.id = e.doctor_id AND DATE(e.created_at) = CURDATE()
            WHERE s.role = 'doctor' AND s.status = 'active'
            GROUP BY s.id
            ORDER BY 
                CASE s.duty_status 
                    WHEN 'Available' THEN 1 
                    WHEN 'In Consultation' THEN 2 
                    WHEN 'On Break' THEN 3 
                    ELSE 4 
                END,
                total_active_queue ASC,
                s.full_name ASC";
    $result = $db_1->query($sql);
    $doctors = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
    }
    return $doctors;
}

/**
 * Automatically selects the best available doctor (least loaded, Available or In Consultation).
 */
function get_best_available_doctor() {
    $doctors = get_doctors_workload_summary();
    // Prefer 'Available' with lowest queue
    foreach ($doctors as $d) {
        if ($d['duty_status'] === 'Available') {
            return $d;
        }
    }
    // Next prefer 'In Consultation' with lowest queue
    foreach ($doctors as $d) {
        if ($d['duty_status'] === 'In Consultation') {
            return $d;
        }
    }
    // Fallback to any on-duty doctor if all are on break or not specified
    return !empty($doctors) ? $doctors[0] : null;
}

/**
 * Reassigns an existing encounter to another doctor with clinical rationale.
 */
function reassign_encounter($encounter_id, $new_doctor_id, $reason, $reassigned_by_staff_id = null) {
    global $db_1;
    $enc_id = (int)$encounter_id;
    $new_doc = (int)$new_doctor_id;
    
    // Fetch old doctor
    $check_q = $db_1->query("SELECT doctor_id, encounter_number FROM encounters WHERE id = {$enc_id} LIMIT 1");
    $enc = $check_q ? $check_q->fetch_assoc() : null;
    if (!$enc) return false;
    $old_doc = (int)($enc['doctor_id'] ?? 0);

    $stmt = $db_1->prepare("UPDATE encounters SET doctor_id = ?, transfer_reason = ?, transferred_from = ? WHERE id = ?");
    $stmt->bind_param("isii", $new_doc, $reason, $old_doc, $enc_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && function_exists('logAction')) {
        $staff_id = $reassigned_by_staff_id ?? ($_SESSION['staff_id'] ?? 1);
        logAction($staff_id, "Reassigned Encounter {$enc['encounter_number']} from Doctor #{$old_doc} to Doctor #{$new_doc}. Reason: {$reason}", 'encounters', $enc_id);
    }
    return $success;
}

?>
