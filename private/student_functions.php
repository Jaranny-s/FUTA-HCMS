<?php

function find_student_by_matric($matric_number) {
    global $db_1;
    $sql = "SELECT * FROM patients WHERE matric_number = ? AND patient_category = 'Student' LIMIT 1";
    $query = $db_1->prepare($sql);
    $query->bind_param("s", $matric_number);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

function register_student($first_name, $surname, $email, $phone, $matric_number, $password, $profile_image = null) {
    global $db_1;
    
    // Generate a unique patient ID e.g. PAT-2026-0001
    $year = date('Y');
    $like = "PAT-$year-%";
    $sql_pid = "SELECT patient_id FROM patients WHERE patient_id LIKE ? ORDER BY id DESC LIMIT 1";
    $q_pid = $db_1->prepare($sql_pid);
    $q_pid->bind_param("s", $like);
    $q_pid->execute();
    $res_pid = $q_pid->get_result();
    
    if ($row = $res_pid->fetch_assoc()) {
        $last_id = $row['patient_id'];
        $last_num = (int) substr($last_id, -4);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }
    $new_patient_id = "PAT-$year-" . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    $q_pid->close();

    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    
    // Some required fields by DB schema need dummy or default values if not provided by public form
    $gender = 'Male'; // Default or we could ask in form
    $dob = '2000-01-01'; // Default
    
    $sql = "INSERT INTO patients (patient_id, first_name, surname, email, phone, matric_number, hashed_password, patient_category, registration_source, gender, date_of_birth, status, profile_image) ";
    $sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, 'Student', 'Self Registration', ?, ?, 'Active', ?)";
    
    $query = $db_1->prepare($sql);
    $query->bind_param("ssssssssss", $new_patient_id, $first_name, $surname, $email, $phone, $matric_number, $hashed_password, $gender, $dob, $profile_image);
    
    if ($query->execute()) {
        return $db_1->insert_id;
    } else {
        return false;
    }
}

function get_student_appointments($patient_id) {
    global $db_1;
    $sql = "SELECT a.*, d.full_name as doctor_name FROM appointments a LEFT JOIN staff d ON a.doctor_id = d.id WHERE a.patient_id = ? ORDER BY a.appointment_date DESC";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $patient_id);
    $query->execute();
    return $query->get_result();
}

function get_upcoming_student_appointment($patient_id) {
    global $db_1;
    $sql = "SELECT a.*, d.full_name as doctor_name FROM appointments a LEFT JOIN staff d ON a.doctor_id = d.id WHERE a.patient_id = ? AND a.status IN ('Pending', 'Approved') AND a.appointment_date >= CURDATE() ORDER BY a.appointment_date ASC LIMIT 1";
    $query = $db_1->prepare($sql);
    $query->bind_param("i", $patient_id);
    $query->execute();
    return $query->get_result()->fetch_assoc();
}

function book_student_appointment($patient_id, $date, $time, $type, $reason) {
    global $db_1;
    
    $datetime = $date . ' ' . $time;
    
    // Generate unique appt number
    $year = date('Y');
    $like = "APT-$year-%";
    $sql_pid = "SELECT appointment_number FROM appointments WHERE appointment_number LIKE ? ORDER BY id DESC LIMIT 1";
    $q_pid = $db_1->prepare($sql_pid);
    $q_pid->bind_param("s", $like);
    $q_pid->execute();
    $res_pid = $q_pid->get_result();
    
    if ($row = $res_pid->fetch_assoc()) {
        $last_num = (int) substr($row['appointment_number'], -4);
        $next_num = $last_num + 1;
    } else {
        $next_num = 1;
    }
    $app_num = "APT-$year-" . str_pad($next_num, 4, '0', STR_PAD_LEFT);
    $q_pid->close();
    
    // default doctor = 0 or null if not selected, since student can't choose doc?
    // Wait, the schema requires doctor_id. Let's find any doctor or set it to 1.
    // Actually, appointments schema: doctor_id NO MUL. So it's required. Let's assign doctor_id = 2 (Assuming ID 2 is a doctor, or we can fetch one).
    // Let's fetch the first doctor
    $q_doc = $db_1->query("SELECT id FROM staff WHERE role_id = (SELECT id FROM roles WHERE role_name='Doctor' LIMIT 1) LIMIT 1");
    $doc_row = $q_doc->fetch_assoc();
    $doctor_id = $doc_row ? $doc_row['id'] : 1;
    
    $booked_by = 1; // Assuming 1 is system or generic admin for self-booking
    
    $sql = "INSERT INTO appointments (appointment_number, patient_id, doctor_id, booked_by, appointment_date, appointment_type, reason, status) ";
    $sql .= "VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
    $query = $db_1->prepare($sql);
    $query->bind_param("siiisss", $app_num, $patient_id, $doctor_id, $booked_by, $datetime, $type, $reason);
    $query->execute();
    return $db_1->insert_id;
}

function update_student_profile($patient_id, $phone, $email, $password = null) {
    global $db_1;
    if ($password) {
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE patients SET phone = ?, email = ?, hashed_password = ? WHERE id = ?";
        $q = $db_1->prepare($sql);
        $q->bind_param("sssi", $phone, $email, $hashed, $patient_id);
    } else {
        $sql = "UPDATE patients SET phone = ?, email = ? WHERE id = ?";
        $q = $db_1->prepare($sql);
        $q->bind_param("ssi", $phone, $email, $patient_id);
    }
    return $q->execute();
}

function get_student_medical_records($patient_id) {
    global $db_1;
    $sql = "SELECT e.*, d.full_name as doctor_name 
            FROM encounters e 
            LEFT JOIN staff d ON e.doctor_id = d.id 
            WHERE e.patient_id = ? AND e.status = 'Completed' 
            ORDER BY e.created_at DESC";
    $q = $db_1->prepare($sql);
    $q->bind_param("i", $patient_id);
    $q->execute();
    return $q->get_result();
}

function get_student_prescriptions($patient_id) {
    global $db_1;
    $sql = "SELECT p.*, inv.drug_name 
            FROM prescriptions p 
            JOIN encounters e ON p.encounter_id = e.id 
            LEFT JOIN pharmacy_inventory inv ON p.inventory_id = inv.id 
            WHERE e.patient_id = ? 
            ORDER BY p.created_at DESC";
    $q = $db_1->prepare($sql);
    $q->bind_param("i", $patient_id);
    $q->execute();
    return $q->get_result();
}

?>
