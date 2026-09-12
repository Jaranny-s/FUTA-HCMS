<?php 

//function find_all_staff() {
//    global $db_1;
//        
//    $sql = "SELECT * FROM staff ";
//    $sql .= "ORDER BY created_at DESC";
//    
//    $query = $db_1->prepare($sql);
//    confirm_query($query);
//    
//    $query->execute();
//    
//    
//    $result = $query->get_result();
//    confirm_result_set($result);
//    $query->close();
//    
//    return $result;
//}


function display_logs($search = null, $startDate = null, $endDate = null, $onlyAdmin = false, $limit = 10, $offset = 0) {
    global $db_1;

    $sql = "SELECT audit_logs.*, staff.full_name, staff.role ";
    $sql .= "FROM audit_logs ";
    $sql .= "LEFT JOIN staff ON audit_logs.staff_id = staff.id ";

    $conditions = [];

    if ($onlyAdmin) {
        $conditions[] = "staff.role = 'admin'";
    }

    if ($startDate && $endDate) {
        $conditions[] = "DATE(audit_logs.created_at) BETWEEN '$startDate' AND '$endDate'";
    }

    if ($search) {
        $conditions[] = "(staff.full_name LIKE '%$search%' 
                         OR audit_logs.action LIKE '%$search%' 
                         OR audit_logs.entity_type LIKE '%$search%')";
    }

    if (!empty($conditions)) {
        $sql .= "WHERE " . implode(" AND ", $conditions) . " ";
    }

    $sql .= "ORDER BY audit_logs.created_at DESC ";

    //  PAGINATION HERE
    $sql .= "LIMIT $limit OFFSET $offset";

    return $db_1->query($sql);
}

function total_page_count($limit) {
	global $db_1;
	
	$totalQuery = "SELECT COUNT(*) as total ";
	$totalQuery .= "FROM audit_logs";
	

	$totalResult = $db_1->query($totalQuery);
	
	$totalRows = $totalResult->fetch_assoc()['total'];

	return ceil($totalRows / $limit);
}

function find_staff_by_role($role, $search = null, $startDate = null, $endDate = null, $onlyAdmin = false, $onlyDoctor = false, $onlyNurse = false, $onlyReceptionist = false, $onlyPharmacist = false, $limit = 10, $offset = 0) {
    global $db_1;
        
    
    $sql = "SELECT * FROM staff ";

    //$sql .= "ORDER BY created_at ASC";
    
    $conditions = [];

    if ($onlyAdmin) {
        $conditions[] = "role = 'admin'";
    }
    if ($onlyDoctor) {
        $conditions[] = "role = 'doctor'";
    }
    if ($onlyNurse) {
        $conditions[] = "role = 'nurse'";
    }
    if ($onlyReceptionist) {
        $conditions[] = "role = 'receptionist'";
    }
    if ($onlyPharmacist) {
        $conditions[] = "role = 'pharmacist'";
    }

    if ($startDate && $endDate) {
        $conditions[] = "DATE(created_at) BETWEEN '$startDate' AND '$endDate'";
    }

    if ($search) {
        $conditions[] = "(full_name LIKE '%$search%' 
                         OR email LIKE '%$search%'  
                         OR department LIKE '%$search%')";
    }

    if (!empty($conditions)) {
        $sql .= "WHERE " . implode(" AND ", $conditions) . " ";
    } else {
    $sql .= "WHERE role = ? ";
    }
    $sql .= "ORDER BY created_at DESC ";

    //  PAGINATION HERE
    $sql .= "LIMIT $limit OFFSET $offset";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    $query->bind_param("s", $role);
    $query->execute();
    
    
    $result = $query->get_result();
    confirm_result_set($result);
    $query->close();
    
    return $result;
}

function find_admin_and_super_admin() {
    global $db_1;
    
    $sql = "SELECT * FROM staff ";
    $sql .= "WHERE role IN ('admin', 'super_admin') ";
    $sql .= "ORDER BY created_at DESC ";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
    $query->execute();
    
    $result = $query->get_result();
    confirm_result_set($result);
    $query->close();
    
    return $result;
}

function total_page_count_for_staff($limit) {
	global $db_1;
	
	$totalQuery = "SELECT COUNT(*) as total ";
	$totalQuery .= "FROM staff";
	

	$totalResult = $db_1->query($totalQuery);
	
	$totalRows = $totalResult->fetch_assoc()['total'];

	return ceil($totalRows / $limit);
}

function find_staff_by_id($id) {
    global $db_1;
        
    $sql = "SELECT * FROM staff ";
    $sql .= "WHERE id = ?";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    $query->bind_param("i", $id);
    $query->execute();
    
    
    $result = $query->get_result();
    confirm_result_set($result);
    
    $staff = $result->fetch_assoc();
    
    $query->close();
    
    return $staff;
}

function find_staff_by_email($email) {
    global $db_1;
        
    $sql = "SELECT * FROM staff ";
    $sql .= "WHERE email = ?";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    $query->bind_param("s", $email);
    $query->execute();
    
    
    $result = $query->get_result();
    confirm_result_set($result);
    
    $staff = $result->fetch_assoc();
    
    $query->close();
    
    return $staff;
}

function generate_temp_password($length = 12) {
    $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // no O, 0, I, 1
    $pass = '';

    for ($i = 0; $i < $length; $i++) {
        $pass .= $chars[random_int(0, strlen($chars) - 1)];
    }

    return 'FHC-' . $pass;
}

function generate_staff_id() {
    global $db_1;

    $year = date('Y');

    $sql = "SELECT system_staff_id FROM staff ";
    $sql .= "WHERE system_staff_id LIKE ? ";
    $sql .= "ORDER BY id DESC ";
    $sql .= "LIMIT 1";

    $like = "FUTA-HC-$year-%";

    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    $query->bind_param("s", $like);
    
    $query->execute();
    
    
    $result = $query->get_result();
    confirm_result_set($result);
    
    if ($row = $result->fetch_assoc()) {
        // Extract last number
        $last_id = $row['system_staff_id'];
        $last_number = (int) substr($last_id, -4);
        $next_number = $last_number + 1;
    } else {
        // First staff of the year
        $next_number = 1;
    }

    $query->close();

    return "FUTA-HC-$year-" . str_pad($next_number, 4, '0', STR_PAD_LEFT);
}

function validate_staff($staff, $options = []) {
    $errors = [];
    
    $password_required = $options['password_required'] ?? true;
    
    // full_name
    if(is_blank($staff['full_name'])) {
        $errors[] = "Name cannot be blank."; 
    }elseif(!has_length($staff['full_name'], ['min' => 2, 'max' => 255])) {
        $errors[] = "Name must be between 2 and 255 characters.";
    }
    
    
    // email
    if(is_blank($staff['email'])) {
        $errors[] = "Email cannot be blank."; 
    }
    if(!has_length($staff['email'], ['max' => 255])) {
        $errors[] = "Email must be at most 255 characters.";
    }
    if(!has_valid_email_format($staff['email'])) {
        $errors[] = "Email has invalid character(s).";
    }
    
if($password_required) {
        // password
    if(is_blank($staff['hashed_password'])) {
        $errors[] = "Password cannot be blank."; 
    }elseif(!has_length($staff['hashed_password'], ['min' => 12, 'max' => 255])) {
        $errors[] = "Password must be between 12 and 255 characters.";
    } 
    if(!has_valid_password_format($staff['hashed_password'])) {
        $errors[] = "Password must include 1 uppercase letter, 1 lowercase letter, 1 number and 1 symbol.";
    }
    
    // confirm_password
    if(is_blank($staff['confirm_password'])) {
        $errors[] = "Confirm Password field cannot be blank."; 
    }elseif($staff['confirm_password'] !== $staff['hashed_password']) {
        $errors[] = "Password and Confirm Password fields do not match.";
    }  
}

    
    // role
    if(is_blank($staff['role'])) {
        $errors[] = "Staff role hasn't been selected."; 
    }
    // department
    if(is_blank($staff['department'])) {
        $errors[] = "Department cannot be blank."; 
    }elseif(!has_length($staff['department'], ['min' => 2, 'max' => 50])) {
        $errors[] = "Department must be between 2 and 50 characters.";
    }

        
    // profile_image
    if(is_blank($staff['profile_image'])) {
        $errors[] = "Select a picture from your device.";
    }
    return $errors;
    
}

function get_role_id($staff) {
    global $db_1;
    
    // Get role_id from role name
    $sql = "SELECT id FROM roles ";
    $sql .= "WHERE name = ?";
    
    $query = $db_1->prepare($sql);
    
    $query->bind_param("s", $staff['role']);
    
    $query->execute();
    
    $result = $query->get_result();
    $roleRow = $result->fetch_assoc();
    $role_id = $roleRow['id'];
    
    $query->close();
    
    return $role_id;
}

function insert_staff($staff) {
    global $db_1;
    
    $errors = validate_staff($staff, ['password_required' => false]);
    if(!empty($errors)) {
        return $errors;
    }
    
    $system_staff_id = generate_staff_id();
    $temp_password = generate_temp_password();
    $hashed_password = password_hash($temp_password, PASSWORD_DEFAULT);
    $role_id = get_role_id($staff); 
    $role_name = $staff['role']; // the enum value (e.g. 'doctor', 'nurse')
    $status = !empty($staff['status']) ? strtolower($staff['status']) : 'active';
    
    $sql = "INSERT INTO staff ";
    $sql .= "(system_staff_id, full_name, email, password, role, role_id, department, profile_image, password_reset_required, status) ";
    $sql .= "VALUES ";
    $sql .= "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $query = $db_1->prepare($sql);
    
    $query->bind_param("sssssissis", $system_staff_id, $staff['full_name'], $staff['email'], $hashed_password, $role_name, $role_id, $staff['department'], $staff['profile_image'], $staff['password_reset_required'], $status);
    $query->execute();
    
    $newId = $db_1->insert_id; // THIS is the real ID

if ($query->affected_rows > 0) {
    $actorId = $_SESSION['staff_id'] ?? $newId; // fallback
    logAction($actorId, 'CREATED', 'staff', $newId);
}
    
    $query->close();
    
    return [
    'success' => true,
    'temp_password' => $temp_password,
    'id' => $newId
];
}

function update_staff($staff) {
    global $db_1;
    
    $password_sent = !is_blank($staff['hashed_password'] ?? '');
    
    $errors = validate_staff($staff, ['password_required' => $password_sent]);
    if(!empty($errors)) {
        return $errors;
    }

    $status = !empty($staff['status']) ? strtolower($staff['status']) : 'active';
    
    $sql = "UPDATE staff SET ";
    $sql .= "full_name = ?, ";
    $sql .= "email = ?, ";
    if($password_sent) {
        $sql .= "password = ?, ";
    }
    $sql .= "role = ?, ";
    $sql .= "department = ?, ";
    $sql .= "profile_image = ?, ";
    $sql .= "status = ? ";
    $sql .= "WHERE id = ? ";
    $sql .= "LIMIT 1";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    if($password_sent) {
        $hashed_password = password_hash($staff['hashed_password'], PASSWORD_BCRYPT);
        $query->bind_param("sssssssi", $staff['full_name'], $staff['email'], $hashed_password, $staff['role'], $staff['department'], $staff['profile_image'], $status, $staff['id']);
    } else {
        $query->bind_param("ssssssi", $staff['full_name'], $staff['email'], $staff['role'], $staff['department'], $staff['profile_image'], $status, $staff['id']);
    }
    $query->execute();
    
if ($query->affected_rows > 0) {
    $actorId = $_SESSION['staff_id'] ?? $staff['id']; // fallback if no login yet
    logAction($actorId, 'UPDATE', 'staff', $staff['id']);
}
    $query->close();
    return true;
}

function delete_picture($id, $delete_image) {
    global $db_1;
    
    $path = __DIR__ . '/../public/staff/images/staff_pictures/' . $delete_image;

    if ($delete_image && file_exists($path)) {
    unlink($path);
}
    
    $sql = "UPDATE staff ";
    $sql .= "SET profile_image = NULL ";
    $sql .= "WHERE id = ?";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
	
	
    
    $query->bind_param("i", $id);
    $query->execute();
	
     if ($query->affected_rows > 0) {
    $actorId = $_SESSION['staff_id'] ?? $id; // fallback if no login yet
    logAction($actorId, 'DELETE_IMAGE', 'staff', $id);
}
    $query->close();
    
    return true;
}

function delete_staff($id) {
    global $db_1;
    
    $staff = find_staff_by_id($id);
    
    if (!$staff) {
        return [
            'success' => false,
            'message' => 'Staff not found.'
        ];
    }


  if (!empty($staff['profile_image'])) {
    $path = __DIR__ . '/images/staff_pictures/' . $staff['profile_image'];
    if (file_exists($path)) {
      unlink($path);
	  $actorId = $_SESSION['staff_id'] ?? $id; // fallback if no login yet
      logAction($actorId, 'DELETE_IMAGE', 'staff', $id);
    }
  }

   
    
    $sql = "DELETE FROM staff ";
    $sql .= "WHERE id = ? ";
    $sql .= "LIMIT 1";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
	
   
    $query->bind_param("i", $id);
    $query->execute();
    
    if ($query->affected_rows === 1) {
        $actorId = $_SESSION['staff_id'] ?? $id;
        logAction($actorId, 'DELETE', 'staff', $id);
   
   $query->close();

    return [
    'success' => true,
    'message' => 'Staff account deleted successfully!'
  ];
    } else {

        $query->close();

        return [
            'success' => false,
            'message' => 'Delete failed or staff not found.'
        ];
    }
  
}


function find_all_patients($search = null, $startDate = null, $endDate = null, $onlyStudent = false, $onlyStaff= false, $limit = 20, $offset = 0) {
    global $db_1;
        
    $sql = "SELECT id, patient_id, surname, first_name, middle_name, gender, phone, email, patient_category, profile_image, status FROM patients ";
    //$sql .= "ORDER BY created_at DESC ";
    
   $conditions = [];

    if ($onlyStudent) {
        $conditions[] = "patient_category = 'Student'";
    }
    if ($onlyStaff) {
        $conditions[] = "patient_category = 'Staff'";
    }

    if ($startDate && $endDate) {
        $conditions[] = "DATE(created_at) BETWEEN '$startDate' AND '$endDate'";
    }

    if ($search) {
        $conditions[] = "(surname LIKE '%$search%' 
                         OR first_name LIKE '%$search%'  
                         OR middle_name LIKE '%$search%')";
    }

    if (!empty($conditions)) {
        $sql .= "WHERE " . implode(" AND ", $conditions) . " ";
    }

    $sql .= "ORDER BY created_at DESC ";

    //  PAGINATION HERE
    $sql .= "LIMIT $limit OFFSET $offset";
    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    $query->execute();
    
    
    $result = $query->get_result();
    confirm_result_set($result);
    $query->close();
    
    return $result;
}

function total_page_count_for_patients($limit) {
	global $db_1;
	
	$totalQuery = "SELECT COUNT(*) as total ";
	$totalQuery .= "FROM patients";
	

	$totalResult = $db_1->query($totalQuery);
	
	$totalRows = $totalResult->fetch_assoc()['total'];

	return ceil($totalRows / $limit);
}


function find_patient_by_id($id) {
    global $db_1;
        
    $sql = "SELECT * FROM patients ";
    $sql .= "WHERE id = ?";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    $query->bind_param("i", $id);
    $query->execute();
    
    
    $result = $query->get_result();
    confirm_result_set($result);
    
    $patient = $result->fetch_assoc();
    
    $query->close();
    
    return $patient;
    
}

function find_primary_emergency_contact($patientId) {
    global $db_1;

    $sql = "SELECT id, contact_name, relationship, phone, is_primary ";
    $sql .= "FROM patient_emergency_contacts ";
    $sql .= "WHERE patient_id = ? ";
    $sql .= "AND is_primary = 1 ";
    $sql .= "LIMIT 1";

    $query = $db_1->prepare($sql);

    confirm_query($query);

    $query->bind_param("i", $patientId);

    $query->execute();

    $result = $query->get_result();

    confirm_result_set($result);

    $contact = $result->fetch_assoc();

    $query->close();

    return $contact;
}

function generate_patient_id() {
    global $db_1;

    $year = date('Y');

    $sql = "SELECT patient_id FROM patients ";
    $sql .= "WHERE patient_id LIKE ? ";
    $sql .= "ORDER BY id DESC ";
    $sql .= "LIMIT 1";

    $like = "PAT-$year-%";

    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    $query->bind_param("s", $like);
    
    $query->execute();
    
    
    $result = $query->get_result();
    confirm_result_set($result);
    
    if ($row = $result->fetch_assoc()) {
        // Extract last number
        $last_id = $row['patient_id'];
        $last_number = (int) substr($last_id, -4);
        $next_number = $last_number + 1;
    } else {
        // First patient of the year
        $next_number = 1;
    }

    $query->close();

    return "PAT-$year-" . str_pad($next_number, 4, '0', STR_PAD_LEFT);
}

function validate_patient($patient) {
    $errors = [];

    // Basic Information
    if (is_blank($patient['surname'] ?? '')) {
        $errors[] = "Surname cannot be blank.";
    } elseif (!has_length($patient['surname'], ['min' => 2, 'max' => 100])) {
        $errors[] = "Surname must be between 2 and 100 characters.";
    }

    if (is_blank($patient['first_name'] ?? '')) {
        $errors[] = "First name cannot be blank.";
    } elseif (!has_length($patient['first_name'], ['min' => 2, 'max' => 100])) {
        $errors[] = "First name must be between 2 and 100 characters.";
    }

    $genderCheck = $patient['gender'] ?? $patient['sex'] ?? '';
    if (is_blank($genderCheck)) {
        $errors[] = "Gender cannot be blank.";
    }

    if (is_blank($patient['date_of_birth'] ?? '')) {
        $errors[] = "Please select the patient's date of birth.";
    }

    // Contact Information
    if (is_blank($patient['phone'] ?? '')) {
        $errors[] = "Please add a primary phone number.";
    } elseif (!has_length($patient['phone'], ['max' => 25])) {
        $errors[] = "Phone number must have at most 25 characters.";
    }

    if (!empty($patient['alternate_phone']) && !has_length($patient['alternate_phone'], ['max' => 25])) {
        $errors[] = "Alternate phone number must have at most 25 characters.";
    }

    // Email is optional, validate format only if provided
    if (!empty($patient['email'])) {
        if (!has_length($patient['email'], ['max' => 255])) {
            $errors[] = "Email address must be at most 255 characters.";
        }
        if (!has_valid_email_format($patient['email'])) {
            $errors[] = "Email format is invalid.";
        }
    }

    // Address is optional, but if provided validate max length
    if (!empty($patient['address']) && !has_length($patient['address'], ['max' => 255])) {
        $errors[] = "Address cannot exceed 255 characters.";
    }

    // Category
    if (is_blank($patient['patient_category'] ?? '')) {
        $errors[] = "Select the appropriate patient category.";
    }

    $patientCategory = trim($patient['patient_category'] ?? '');

    if ($patientCategory === 'Student') {
        if (is_blank($patient['matric_number'] ?? '')) {
            $errors[] = "Please fill in the student's matriculation number.";
        }
    } elseif ($patientCategory === 'Staff') {
        if (is_blank($patient['staff_number'] ?? '')) {
            $errors[] = "Please fill in the staff number/ID.";
        }
    } elseif ($patientCategory === 'Dependant') {
        if (is_blank($patient['relationship_to_principal'] ?? '')) {
            $errors[] = "Please fill in the dependant's relationship to the principal staff member.";
        }
    }

    // Principal Patient check if provided
    $principalPatientId = !empty($patient['principal_patient_id']) ? (int)$patient['principal_patient_id'] : null;
    if ($principalPatientId !== null && $principalPatientId > 0) {
        if ($principalPatientId === (int)($patient['id'] ?? 0)) {
            $errors[] = "A patient cannot be their own principal patient.";
        }
    }

    // Emergency Contact
    $emergencyName = trim($patient['emergency_contact_name'] ?? $patient['emergency_name'] ?? '');
    $emergencyPhone = trim($patient['emergency_contact_phone'] ?? $patient['emergency_phone'] ?? '');
    if (!empty($emergencyName) && !has_length($emergencyName, ['min' => 2, 'max' => 150])) {
        $errors[] = "Emergency contact name must be between 2 and 150 characters.";
    }
    if (!empty($emergencyPhone) && !has_length($emergencyPhone, ['max' => 25])) {
        $errors[] = "Emergency contact phone must have at most 25 characters.";
    }

    // Next of Kin
    $nextOfKinName = trim($patient['next_of_kin_name'] ?? '');
    $nextOfKinPhone = trim($patient['next_of_kin_phone'] ?? '');
    if (!empty($nextOfKinName) && !has_length($nextOfKinName, ['min' => 2, 'max' => 150])) {
        $errors[] = "Next of kin name must be between 2 and 150 characters.";
    }
    if (!empty($nextOfKinPhone) && !has_length($nextOfKinPhone, ['max' => 25])) {
        $errors[] = "Next of kin phone must have at most 25 characters.";
    }

    return $errors;
}

function search_patients_for_principal($search, $currentPatientId = 0) {
    global $db_1;

    $search = trim($search);
    $currentPatientId = (int)$currentPatientId;

    if ($search === '') {
        return [];
    }

    $searchTerm = '%' . $search . '%';

    if ($currentPatientId > 0) {
        $sql = "
            SELECT
                id,
                patient_id,
                surname,
                first_name,
                middle_name,
                staff_number,
                department,
                profile_image,
                patient_category
            FROM patients
            WHERE patient_category = 'Staff'
            AND id != ?
            AND (
                surname LIKE ?
                OR first_name LIKE ?
                OR middle_name LIKE ?
                OR staff_number LIKE ?
                OR patient_id LIKE ?
            )
            ORDER BY surname ASC, first_name ASC
            LIMIT 10
        ";
        $stmt = $db_1->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('isssss', $currentPatientId, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    } else {
        $sql = "
            SELECT
                id,
                patient_id,
                surname,
                first_name,
                middle_name,
                staff_number,
                department,
                profile_image,
                patient_category
            FROM patients
            WHERE patient_category = 'Staff'
            AND (
                surname LIKE ?
                OR first_name LIKE ?
                OR middle_name LIKE ?
                OR staff_number LIKE ?
                OR patient_id LIKE ?
            )
            ORDER BY surname ASC, first_name ASC
            LIMIT 10
        ";
        $stmt = $db_1->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param('sssss', $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return [];
    }

    $result = $stmt->get_result();
    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = [
            'id' => (int)$row['id'],
            'patient_id' => $row['patient_id'],
            'surname' => $row['surname'],
            'first_name' => $row['first_name'],
            'middle_name' => $row['middle_name'],
            'staff_number' => $row['staff_number'] ?? '',
            'department' => $row['department'] ?? '',
            'profile_image' => $row['profile_image'] ?? '',
            'patient_category' => $row['patient_category']
        ];
    }
    $stmt->close();
    return $patients;
}

function find_patient_by_patient_id($patientId) {
    global $db_1;

    $sql = "
        SELECT *
        FROM patients
        WHERE patient_id = ?
        LIMIT 1
    ";

    $stmt = $db_1->prepare($sql);

    if (!$stmt) {

        error_log(
            'find_patient_by_patient_id prepare failed: ' .
            $db_1->error
        );

        return null;
    }

    $stmt->bind_param(
        "s",
        $patientId
    );

    if (!$stmt->execute()) {

        error_log(
            'find_patient_by_patient_id execute failed: ' .
            $stmt->error
        );

        $stmt->close();

        return null;
    }

    $result = $stmt->get_result();

    $patient = $result->fetch_assoc();

    $stmt->close();

    return $patient ?: null;
}

function search_patients_by_name($search)
{
    global $db_1;

    $search = trim($search);

    if ($search === '') {
        return [];
    }


    $sql = "
        SELECT
            id,
            patient_id,
            surname,
            first_name,
            middle_name
        FROM patients
        WHERE
            surname LIKE ?
            OR first_name LIKE ?
            OR middle_name LIKE ?
        ORDER BY surname, first_name
        LIMIT 20
    ";


    $stmt = $db_1->prepare($sql);


    $searchTerm = '%' . $search . '%';


    $stmt->bind_param(
        'sss',
        $searchTerm,
        $searchTerm,
        $searchTerm
    );


    $stmt->execute();


    $result = $stmt->get_result();


    $patients = [];


    while ($row = $result->fetch_assoc()) {

        $patients[] = [

            'id' => (int)$row['id'],

            'patient_id' =>
                $row['patient_id'],

            'surname' =>
                $row['surname'],

            'first_name' =>
                $row['first_name'],

            'middle_name' =>
                $row['middle_name']

        ];
    }


    $stmt->close();


    return $patients;
}

function insert_patient($patient) {
    global $db_1;
    
    if (empty($patient['status'])) {
        $patient['status'] = 'Active';
    }
    
    $errors = validate_patient($patient);
    if (!empty($errors)) {
        return $errors;
    }
    
    $system_patient_id = generate_patient_id();
    
    $surname = trim($patient['surname'] ?? '');
    $firstName = trim($patient['first_name'] ?? '');
    $middleName = trim($patient['middle_name'] ?? '');
    $gender = $patient['gender'] ?? $patient['sex'] ?? 'Male';
    $nationality = trim($patient['nationality'] ?? $patient['country'] ?? 'Nigeria');
    $stateOfOrigin = trim($patient['state_of_origin'] ?? $patient['state'] ?? '');
    $lga = trim($patient['lga'] ?? '');
    $maritalStatus = trim($patient['marital_status'] ?? '');
    $dob = !empty($patient['date_of_birth']) ? $patient['date_of_birth'] : null;
    
    $phone = trim($patient['phone'] ?? '');
    $altPhone = trim($patient['alternate_phone'] ?? '');
    $email = trim($patient['email'] ?? '');
    $address = trim($patient['address'] ?? '');
    $city = trim($patient['city'] ?? '');
    $residentialState = trim($patient['residential_state'] ?? '');
    $postalCode = trim($patient['postal_code'] ?? '');
    $state = $stateOfOrigin;
    
    $category = $patient['patient_category'] ?? 'Student';
    $matricNo = trim($patient['matric_number'] ?? '');
    $staffNo = trim($patient['staff_number'] ?? '');
    $faculty = trim($patient['faculty'] ?? '');
    $department = trim($patient['department'] ?? '');
    $level = trim($patient['level'] ?? '');
    $staffPosition = trim($patient['staff_position'] ?? $patient['position'] ?? '');
    
    $bloodGroup = trim($patient['blood_group'] ?? '');
    $genotype = trim($patient['genotype'] ?? '');
    $allergies = trim($patient['allergies'] ?? '');
    $chronicConditions = trim($patient['chronic_conditions'] ?? '');
    $disabilities = trim($patient['disabilities'] ?? '');
    
    $nextOfKinName = trim($patient['next_of_kin_name'] ?? '');
    $nextOfKinPhone = trim($patient['next_of_kin_phone'] ?? '');
    $nextOfKinRel = trim($patient['next_of_kin_relationship'] ?? '');
    
    $emergencyName = trim($patient['emergency_contact_name'] ?? $patient['emergency_name'] ?? '');
    $emergencyPhone = trim($patient['emergency_contact_phone'] ?? $patient['emergency_phone'] ?? '');
    $emergencyRel = trim($patient['emergency_contact_relationship'] ?? $patient['emergency_relationship'] ?? '');
    
    if (empty($nextOfKinName) && !empty($emergencyName)) {
        $nextOfKinName = $emergencyName;
        $nextOfKinPhone = $emergencyPhone;
        $nextOfKinRel = $emergencyRel;
    }
    
    $occupation = trim($patient['occupation'] ?? '');
    $employer = trim($patient['employer'] ?? '');
    $relPrincipal = trim($patient['relationship_to_principal'] ?? '');
    $principalId = !empty($patient['principal_patient_id']) ? (int)$patient['principal_patient_id'] : null;
    
    $profileImage = $patient['profile_image'] ?? null;
    $status = $patient['status'] ?? 'Active';
    $registrationSource = $patient['registration_source'] ?? 'Manual';
    $registeredBy = $_SESSION['staff_id'] ?? null;
    
    $sql = "INSERT INTO patients (
        patient_id, surname, first_name, middle_name, gender, nationality, state_of_origin, lga, marital_status, date_of_birth,
        phone, alternate_phone, email, address, city, residential_state, postal_code, state,
        patient_category, matric_number, staff_number, faculty, department, level, staff_position,
        blood_group, genotype, allergies, chronic_conditions, disabilities,
        next_of_kin_name, next_of_kin_phone, next_of_kin_relationship,
        occupation, employer, relationship_to_principal, principal_patient_id,
        profile_image, status, registration_source, registered_by
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?
    )";
    
    $query = $db_1->prepare($sql);
    if (!$query) {
        error_log("insert_patient prepare failed: " . $db_1->error);
        return ["Unable to prepare patient registration."];
    }
    
    $types = str_repeat("s", 41);
    $query->bind_param(
        $types,
        $system_patient_id, $surname, $firstName, $middleName, $gender, $nationality, $stateOfOrigin, $lga, $maritalStatus, $dob,
        $phone, $altPhone, $email, $address, $city, $residentialState, $postalCode, $state,
        $category, $matricNo, $staffNo, $faculty, $department, $level, $staffPosition,
        $bloodGroup, $genotype, $allergies, $chronicConditions, $disabilities,
        $nextOfKinName, $nextOfKinPhone, $nextOfKinRel,
        $occupation, $employer, $relPrincipal, $principalId,
        $profileImage, $status, $registrationSource, $registeredBy
    );
    
    if (!$query->execute()) {
        error_log("insert_patient execute failed: " . $query->error);
        $err = $query->error;
        $query->close();
        return ["Failed to insert patient: " . $err];
    }
    
    $newId = $db_1->insert_id;
    $query->close();
    
    // Save emergency contact into patient_emergency_contacts table
    if (!empty($emergencyName) && !empty($emergencyPhone)) {
        $emSql = "INSERT INTO patient_emergency_contacts (patient_id, contact_name, relationship, phone, is_primary) VALUES (?, ?, ?, ?, 1)";
        $emQuery = $db_1->prepare($emSql);
        if ($emQuery) {
            $emQuery->bind_param("isss", $newId, $emergencyName, $emergencyRel, $emergencyPhone);
            $emQuery->execute();
            $emQuery->close();
        }
    }
    
    $actorId = $_SESSION['staff_id'] ?? null;
    if ($actorId !== null) {
        logAction($actorId, 'CREATED', 'patient', $newId);
    }
    
    return [
        'success' => true,
        'id' => $newId
    ];
}

function update_patient($patient) {
    global $db_1;

    $patientDatabaseId = (int)($patient['id'] ?? 0);
    if ($patientDatabaseId <= 0) {
        return ["Invalid patient ID for update."];
    }
    
    $existing = find_patient_by_id($patientDatabaseId);
    if (!$existing) {
        return ["Patient record not found."];
    }

    if (empty($patient['status'])) {
        $patient['status'] = $existing['status'] ?? 'Active';
    }

    $errors = validate_patient($patient);
    if (!empty($errors)) {
        return $errors;
    }

    $surname = trim($patient['surname'] ?? '');
    $firstName = trim($patient['first_name'] ?? '');
    $middleName = trim($patient['middle_name'] ?? '');
    $gender = $patient['gender'] ?? $patient['sex'] ?? 'Male';
    $nationality = trim($patient['nationality'] ?? $patient['country'] ?? 'Nigeria');
    $stateOfOrigin = trim($patient['state_of_origin'] ?? $patient['state'] ?? '');
    $lga = trim($patient['lga'] ?? '');
    $maritalStatus = trim($patient['marital_status'] ?? '');
    $dob = !empty($patient['date_of_birth']) ? $patient['date_of_birth'] : null;

    $phone = trim($patient['phone'] ?? '');
    $altPhone = trim($patient['alternate_phone'] ?? '');
    $email = trim($patient['email'] ?? '');
    $address = trim($patient['address'] ?? '');
    $city = trim($patient['city'] ?? '');
    $residentialState = trim($patient['residential_state'] ?? '');
    $postalCode = trim($patient['postal_code'] ?? '');
    $state = $stateOfOrigin;

    $category = $patient['patient_category'] ?? 'Student';
    $matricNo = trim($patient['matric_number'] ?? '');
    $staffNo = trim($patient['staff_number'] ?? '');
    $faculty = trim($patient['faculty'] ?? '');
    $department = trim($patient['department'] ?? '');
    $level = trim($patient['level'] ?? '');
    $staffPosition = trim($patient['staff_position'] ?? $patient['position'] ?? '');

    $bloodGroup = trim($patient['blood_group'] ?? '');
    $genotype = trim($patient['genotype'] ?? '');
    $allergies = trim($patient['allergies'] ?? '');
    $chronicConditions = trim($patient['chronic_conditions'] ?? '');
    $disabilities = trim($patient['disabilities'] ?? '');

    $nextOfKinName = trim($patient['next_of_kin_name'] ?? '');
    $nextOfKinPhone = trim($patient['next_of_kin_phone'] ?? '');
    $nextOfKinRel = trim($patient['next_of_kin_relationship'] ?? '');

    $emergencyName = trim($patient['emergency_contact_name'] ?? $patient['emergency_name'] ?? '');
    $emergencyPhone = trim($patient['emergency_contact_phone'] ?? $patient['emergency_phone'] ?? '');
    $emergencyRel = trim($patient['emergency_contact_relationship'] ?? $patient['emergency_relationship'] ?? '');

    if (empty($nextOfKinName) && !empty($emergencyName)) {
        $nextOfKinName = $emergencyName;
        $nextOfKinPhone = $emergencyPhone;
        $nextOfKinRel = $emergencyRel;
    }

    $occupation = trim($patient['occupation'] ?? '');
    $employer = trim($patient['employer'] ?? '');
    $relPrincipal = trim($patient['relationship_to_principal'] ?? '');
    $principalId = !empty($patient['principal_patient_id']) ? (int)$patient['principal_patient_id'] : null;
    $status = $patient['status'] ?? 'Active';

    $sql = "
        UPDATE patients SET
            surname = ?,
            first_name = ?,
            middle_name = ?,
            gender = ?,
            nationality = ?,
            state_of_origin = ?,
            lga = ?,
            marital_status = ?,
            date_of_birth = ?,
            phone = ?,
            alternate_phone = ?,
            email = ?,
            address = ?,
            city = ?,
            residential_state = ?,
            postal_code = ?,
            state = ?,
            patient_category = ?,
            matric_number = ?,
            staff_number = ?,
            faculty = ?,
            department = ?,
            level = ?,
            staff_position = ?,
            blood_group = ?,
            genotype = ?,
            allergies = ?,
            chronic_conditions = ?,
            disabilities = ?,
            next_of_kin_name = ?,
            next_of_kin_phone = ?,
            next_of_kin_relationship = ?,
            status = ?,
            occupation = ?,
            employer = ?,
            relationship_to_principal = ?,
            principal_patient_id = ?
        WHERE id = ?
        LIMIT 1
    ";

    $query = $db_1->prepare($sql);
    if (!$query) {
        error_log('update_patient prepare failed: ' . $db_1->error);
        return ["Unable to prepare patient update."];
    }

    $types = str_repeat("s", 37) . "i";
    $query->bind_param(
        $types,
        $surname,
        $firstName,
        $middleName,
        $gender,
        $nationality,
        $stateOfOrigin,
        $lga,
        $maritalStatus,
        $dob,
        $phone,
        $altPhone,
        $email,
        $address,
        $city,
        $residentialState,
        $postalCode,
        $state,
        $category,
        $matricNo,
        $staffNo,
        $faculty,
        $department,
        $level,
        $staffPosition,
        $bloodGroup,
        $genotype,
        $allergies,
        $chronicConditions,
        $disabilities,
        $nextOfKinName,
        $nextOfKinPhone,
        $nextOfKinRel,
        $status,
        $occupation,
        $employer,
        $relPrincipal,
        $principalId,
        $patientDatabaseId
    );

    if (!$query->execute()) {
        error_log('update_patient execute failed: ' . $query->error);
        $err = $query->error;
        $query->close();
        return ["Unable to update patient information: " . $err];
    }
    $query->close();

    // Emergency Contact
    if (!empty($emergencyName) || !empty($emergencyPhone)) {
        $checkSql = "SELECT id FROM patient_emergency_contacts WHERE patient_id = ? AND is_primary = 1 LIMIT 1";
        $checkStmt = $db_1->prepare($checkSql);
        if ($checkStmt) {
            $checkStmt->bind_param("i", $patientDatabaseId);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingEmergency = $checkResult->fetch_assoc();
            $checkStmt->close();

            if ($existingEmergency) {
                $emUpdateSql = "UPDATE patient_emergency_contacts SET contact_name = ?, phone = ?, relationship = ? WHERE id = ?";
                $emUpdateStmt = $db_1->prepare($emUpdateSql);
                if ($emUpdateStmt) {
                    $emUpdateStmt->bind_param("sssi", $emergencyName, $emergencyPhone, $emergencyRel, $existingEmergency['id']);
                    $emUpdateStmt->execute();
                    $emUpdateStmt->close();
                }
            } else {
                $emInsertSql = "INSERT INTO patient_emergency_contacts (patient_id, contact_name, relationship, phone, is_primary) VALUES (?, ?, ?, ?, 1)";
                $emInsertStmt = $db_1->prepare($emInsertSql);
                if ($emInsertStmt) {
                    $emInsertStmt->bind_param("isss", $patientDatabaseId, $emergencyName, $emergencyRel, $emergencyPhone);
                    $emInsertStmt->execute();
                    $emInsertStmt->close();
                }
            }
        }
    }

    $actorId = $_SESSION['staff_id'] ?? null;
    if ($actorId !== null) {
        logAction($actorId, 'UPDATED', 'patient', $patientDatabaseId);
    }

    return true;
}

function update_patient_profile_image($id, $imageName)
{
    global $db_1;

    $sql = "
        UPDATE patients
        SET profile_image = ?
        WHERE id = ?
        LIMIT 1
    ";

    $query =
        $db_1->prepare($sql);

    confirm_query($query);

    $query->bind_param(
        "si",
        $imageName,
        $id
    );

    $query->execute();

    $query->close();

    return true;
}

function delete_patient_image($imageName)
{
    /*
    |--------------------------------------------------------------------------
    | Never delete the generic/default patient image.
    |--------------------------------------------------------------------------
    */

    if (
        empty($imageName) ||
        $imageName === 'default_profile_pic.png'
    ) {

        return false;

    }


    $path =
        __DIR__ .
        '/../public/images/patient_pictures/' .
        basename($imageName);


    if (file_exists($path)) {

        unlink($path);

        return true;

    }


    return false;
}


?>