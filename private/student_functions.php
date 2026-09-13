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
    $db_1->query("UPDATE appointments SET status = 'No Show' WHERE status IN ('Pending', 'Approved') AND DATE(appointment_date) < CURDATE()");
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
    
    $booked_by = NULL; // Self-booked by student
    
    $sql = "INSERT INTO appointments (appointment_number, patient_id, appointment_date, appointment_type, reason, status, booked_by, doctor_id) ";
    $sql .= "VALUES (?, ?, ?, ?, ?, 'Pending', ?, NULL)";
    
    $query = $db_1->prepare($sql);
    $query->bind_param("sisssi", $app_num, $patient_id, $datetime, $type, $reason, $booked_by);
    $query->execute();
    $insert_id = $db_1->insert_id;
    $query->close();
    return $insert_id;
}

function update_student_profile($patient_id, $data, $password = null, $profile_image = null) {
    global $db_1;
    $patient_id = (int)$patient_id;
    if ($patient_id <= 0) return false;

    if (!is_array($data)) {
        // Backwards compatibility with old signature ($patient_id, $phone, $email, $password = null)
        $phone = $data;
        $email = $password;
        $pwd = func_num_args() > 3 ? func_get_arg(3) : null;
        if ($pwd) {
            $hashed = password_hash($pwd, PASSWORD_BCRYPT);
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

    // $data is an associative array of student fields
    $fields = [];
    $types = '';
    $values = [];

    $allowed = [
        'first_name', 'surname', 'middle_name',
        'gender', 'date_of_birth', 'nationality', 'state_of_origin', 'lga', 'marital_status',
        'phone', 'alternate_phone', 'email', 'address', 'city', 'residential_state',
        'faculty', 'department', 'level',
        'blood_group', 'genotype', 'allergies', 'chronic_conditions', 'disabilities',
        'next_of_kin_name', 'next_of_kin_relationship', 'next_of_kin_phone'
    ];

    foreach ($allowed as $field) {
        if (isset($data[$field])) {
            $val = trim($data[$field]);
            $fields[] = "$field = ?";
            $types .= 's';
            if ($field === 'date_of_birth' && empty($val)) {
                $values[] = null;
            } else {
                $values[] = $val;
            }
        }
    }

    if (!empty($profile_image)) {
        $fields[] = "profile_image = ?";
        $types .= 's';
        $values[] = $profile_image;
    }

    if (!empty($password)) {
        $fields[] = "hashed_password = ?";
        $types .= 's';
        $values[] = password_hash($password, PASSWORD_BCRYPT);
    }

    $res = true;
    if (!empty($fields)) {
        $sql = "UPDATE patients SET " . implode(", ", $fields) . " WHERE id = ? LIMIT 1";
        $types .= 'i';
        $values[] = $patient_id;

        $stmt = $db_1->prepare($sql);
        if (!$stmt) {
            error_log("update_student_profile prepare failed: " . $db_1->error);
            return false;
        }

        $stmt->bind_param($types, ...$values);
        $res = $stmt->execute();
        if (!$res) {
            error_log("update_student_profile execute failed: " . $stmt->error);
        }
        $stmt->close();
    }

    // Keep patient_emergency_contacts in sync with Next of Kin details
    $emName = trim($data['emergency_contact_name'] ?? $data['next_of_kin_name'] ?? '');
    $emPhone = trim($data['emergency_contact_phone'] ?? $data['next_of_kin_phone'] ?? '');
    $emRel = trim($data['emergency_contact_relationship'] ?? $data['next_of_kin_relationship'] ?? '');

    if (!empty($emName) && !empty($emPhone)) {
        $chk = $db_1->prepare("SELECT id FROM patient_emergency_contacts WHERE patient_id = ? AND is_primary = 1 LIMIT 1");
        if ($chk) {
            $chk->bind_param("i", $patient_id);
            $chk->execute();
            $chkRes = $chk->get_result();
            if ($chkRow = $chkRes->fetch_assoc()) {
                $emId = (int)$chkRow['id'];
                $chk->close();
                $updEm = $db_1->prepare("UPDATE patient_emergency_contacts SET contact_name = ?, relationship = ?, phone = ? WHERE id = ?");
                if ($updEm) {
                    $updEm->bind_param("sssi", $emName, $emRel, $emPhone, $emId);
                    $updEm->execute();
                    $updEm->close();
                }
            } else {
                $chk->close();
                $insEm = $db_1->prepare("INSERT INTO patient_emergency_contacts (patient_id, contact_name, relationship, phone, is_primary) VALUES (?, ?, ?, ?, 1)");
                if ($insEm) {
                    $insEm->bind_param("isss", $patient_id, $emName, $emRel, $emPhone);
                    $insEm->execute();
                    $insEm->close();
                }
            }
        }
    }

    return $res;
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
