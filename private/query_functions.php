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
    
    $sql = "INSERT INTO staff ";
    $sql .= "(system_staff_id, full_name, email, password, role, role_id, department, profile_image, password_reset_required) ";
    $sql .= "VALUES ";
    $sql .= "(?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $query = $db_1->prepare($sql);
    
    $query->bind_param("sssssissi", $system_staff_id, $staff['full_name'], $staff['email'], $hashed_password, $role_name, $role_id, $staff['department'], $staff['profile_image'], $staff['password_reset_required']);
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
    
    $password_sent = !is_blank($staff['hashed_password']);
    
    $errors = validate_staff($staff, ['password_required' => $password_sent]);
    if(!empty($errors)) {
        return $errors;
    }
    
    $sql = "UPDATE staff SET ";
    $sql .= "full_name = ?, ";
    $sql .= "email = ?, ";
    if($password_sent) {
        $sql .= "password = ?, ";
    }
    $sql .= "role = ?, ";
    $sql .= "department = ?, ";
    $sql .= "profile_image = ? ";
    $sql .= "WHERE id = ? ";
    $sql .= "LIMIT 1";
    
    $query = $db_1->prepare($sql);
    confirm_query($query);
    
    if($password_sent) {
        $hashed_password = password_hash($staff['hashed_password'], PASSWORD_BCRYPT);
        $query->bind_param("ssssssi", $staff['full_name'], $staff['email'], $hashed_password, $staff['role'], $staff['department'], $staff['profile_image'], $staff['id']);
    } else {
        $query->bind_param("sssssi", $staff['full_name'], $staff['email'], $staff['role'], $staff['department'], $staff['profile_image'], $staff['id']);
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


    /*
    |--------------------------------------------------------------------------
    | Basic Information
    |--------------------------------------------------------------------------
    */

    // Surname
    if (is_blank($patient['surname'] ?? '')) {

        $errors[] = "Surname cannot be blank.";

    } elseif (!has_length($patient['surname'], [
        'min' => 2,
        'max' => 255
    ])) {

        $errors[] =
            "Surname must be between 2 and 255 characters.";
    }


    // First Name
    if (is_blank($patient['first_name'] ?? '')) {

        $errors[] = "First name cannot be blank.";

    } elseif (!has_length($patient['first_name'], [
        'min' => 2,
        'max' => 255
    ])) {

        $errors[] =
            "First name must be between 2 and 255 characters.";
    }


    // Gender
    if (is_blank($patient['gender'] ?? '')) {

        $errors[] = "Gender cannot be blank.";
    }


    // Date of Birth
    if (is_blank($patient['date_of_birth'] ?? '')) {

        $errors[] =
            "Please select the patient's date of birth.";
    }


    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */

    // Phone
    if (is_blank($patient['phone'] ?? '')) {

        $errors[] =
            "Please add a phone number.";

    } elseif (!has_length($patient['phone'], [
        'max' => 20
    ])) {

        $errors[] =
            "Phone number must have at most 20 characters.";
    }


    // Alternate Phone
    if (
        !empty($patient['alternate_phone']) &&
        !has_length($patient['alternate_phone'], [
            'max' => 20
        ])
    ) {

        $errors[] =
            "Alternate phone number must have at most 20 characters.";
    }


    // Email
    if (is_blank($patient['email'] ?? '')) {

        $errors[] =
            "Please fill in an email address.";

    } else {

        if (!has_length($patient['email'], [
            'max' => 255
        ])) {

            $errors[] =
                "Email address must be at most 255 characters.";
        }

        if (!has_valid_email_format($patient['email'])) {

            $errors[] =
                "Email has invalid character(s).";
        }
    }


    // Address
    if (is_blank($patient['address'] ?? '')) {

        $errors[] =
            "Please add an address.";

    } elseif (!has_length($patient['address'], [
        'min' => 3,
        'max' => 255
    ])) {

        $errors[] =
            "Address field must contain between 3 and 255 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | Patient Category
    |--------------------------------------------------------------------------
    */

    if (is_blank($patient['patient_category'] ?? '')) {

        $errors[] =
            "Select the appropriate patient category.";
    }


    /*
    |--------------------------------------------------------------------------
    | Category-Specific Information
    |--------------------------------------------------------------------------
    |
    | Only fields applicable to the selected patient category
    | are required.
    |
    */

    $patientCategory =
        trim($patient['patient_category'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Student
    |--------------------------------------------------------------------------
    */

    if ($patientCategory === 'Student') {

        // Matric Number
        if (is_blank($patient['matric_number'] ?? '')) {

            $errors[] =
                "Please fill in the student's matriculation number.";

        } elseif (!has_length($patient['matric_number'], [
            'max' => 50
        ])) {

            $errors[] =
                "Matriculation number is too long.";
        }


        // Faculty
        if (is_blank($patient['faculty'] ?? '')) {

            $errors[] =
                "Please fill in the student's faculty.";

        } elseif (!has_length($patient['faculty'], [
            'max' => 255
        ])) {

            $errors[] =
                "Faculty must be at most 255 characters.";
        }


        // Department
        if (is_blank($patient['department'] ?? '')) {

            $errors[] =
                "Department cannot be blank for a student.";

        } elseif (!has_length($patient['department'], [
            'min' => 2,
            'max' => 50
        ])) {

            $errors[] =
                "Department must be between 2 and 50 characters.";
        }


        // Level
        if (is_blank($patient['level'] ?? '')) {

            $errors[] =
                "Please fill in the student's level.";

        } elseif (!has_length($patient['level'], [
            'max' => 50
        ])) {

            $errors[] =
                "Level is too long.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Staff
    |--------------------------------------------------------------------------
    */

    elseif ($patientCategory === 'Staff') {

        // Staff Number
        if (is_blank($patient['staff_number'] ?? '')) {

            $errors[] =
                "Please fill in the staff number.";

        } elseif (!has_length($patient['staff_number'], [
            'max' => 50
        ])) {

            $errors[] =
                "Staff number is too long.";
        }


        // Staff Position
        if (is_blank($patient['staff_position'] ?? '')) {

            $errors[] =
                "Please fill in the staff position.";

        } elseif (!has_length($patient['staff_position'], [
            'max' => 255
        ])) {

            $errors[] =
                "Staff position must be at most 255 characters.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Dependant
    |--------------------------------------------------------------------------
    */

    elseif ($patientCategory === 'Dependant') {

        // Relationship to Principal
        if (
            is_blank(
                $patient['relationship_to_principal'] ?? ''
            )
        ) {

            $errors[] =
                "Please fill in the dependant's relationship to the principal patient.";

        } elseif (!has_length(
            $patient['relationship_to_principal'],
            [
                'min' => 2,
                'max' => 255
            ]
        )) {

            $errors[] =
                "The relationship to the principal patient must be between 2 and 255 characters.";
        }


        // Principal Patient ID
        if (empty($patient['principal_patient_id'])) {

            $errors[] =
                "Please select the principal patient for this dependant.";

        } elseif (
            !is_numeric($patient['principal_patient_id']) ||
            (int)$patient['principal_patient_id'] <= 0
        ) {

            $errors[] =
                "The principal patient ID is invalid.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | External
    |--------------------------------------------------------------------------
    */

    elseif ($patientCategory === 'External') {

        // Occupation
        if (is_blank($patient['occupation'] ?? '')) {

            $errors[] =
                "Please fill in the patient's occupation.";

        } elseif (!has_length($patient['occupation'], [
            'max' => 255
        ])) {

            $errors[] =
                "Occupation must be at most 255 characters.";
        }


        // Employer
        if (is_blank($patient['employer'] ?? '')) {

            $errors[] =
                "Please fill in the patient's employer.";

        } elseif (!has_length($patient['employer'], [
            'max' => 255
        ])) {

            $errors[] =
                "Employer must be at most 255 characters.";
        }
    }

    /* 
|--------------------------------------------------------------------------
| Principal Patient
|--------------------------------------------------------------------------
*/

$principalPatientId =
    !empty($patient['principal_patient_id'])
    ? (int)$patient['principal_patient_id']
    : null;

if ($principalPatientId !== null) {

    if ($principalPatientId === (int)($patient['id'] ?? 0)) {

        $errors[] =
            "A patient cannot be their own principal patient.";

    } else {

        global $db_1;

        $checkSql = "
            SELECT id
            FROM patients
            WHERE id = ?
            LIMIT 1
        ";

        $checkStmt =
            $db_1->prepare($checkSql);

        if ($checkStmt) {

            $checkStmt->bind_param(
                "i",
                $principalPatientId
            );

            $checkStmt->execute();

            $checkResult =
                $checkStmt->get_result();

            if (!$checkResult->fetch_assoc()) {

                $errors[] =
                    "The selected principal patient could not be found.";

            }

            $checkStmt->close();

        } else {

            $errors[] =
                "Unable to verify the selected principal patient.";
        }
    }
}

    /*
    |--------------------------------------------------------------------------
    | Medical Information
    |--------------------------------------------------------------------------
    */

    // Blood Group
    if (is_blank($patient['blood_group'] ?? '')) {

        $errors[] =
            "Please fill in the patient's blood group.";

    } elseif (!has_length($patient['blood_group'], [
        'min' => 2,
        'max' => 5
    ])) {

        $errors[] =
            "The blood group must be between 2 and 5 characters.";
    }


    // Genotype
    if (is_blank($patient['genotype'] ?? '')) {

        $errors[] =
            "Please fill in the patient's genotype.";

    } elseif (!has_length($patient['genotype'], [
        'min' => 2,
        'max' => 5
    ])) {

        $errors[] =
            "The genotype field must have between 2 and 5 characters.";
    }


    // Allergies
    if (is_blank($patient['allergies'] ?? '')) {

        $errors[] =
            "Please fill in the patient's allergies if any, or put 'None'.";

    } elseif (!has_length($patient['allergies'], [
        'min' => 2,
        'max' => 255
    ])) {

        $errors[] =
            "The allergies field must have between 2 and 255 characters.";
    }


    // Chronic Conditions
    if (is_blank($patient['chronic_conditions'] ?? '')) {

        $errors[] =
            "Please fill in the patient's chronic conditions if any, or put 'None'.";

    } elseif (!has_length($patient['chronic_conditions'], [
        'min' => 2,
        'max' => 255
    ])) {

        $errors[] =
            "The chronic conditions field must have between 2 and 255 characters.";
    }


    // Disabilities
    if (
        !empty($patient['disabilities']) &&
        !has_length($patient['disabilities'], [
            'max' => 255
        ])
    ) {

        $errors[] =
            "The disabilities field must be at most 255 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | Emergency Contact
    |--------------------------------------------------------------------------
    */

    if (is_blank($patient['emergency_name'] ?? '')) {

        $errors[] =
            "Please fill in the name of the patient's emergency contact.";

    } elseif (!has_length($patient['emergency_name'], [
        'min' => 2,
        'max' => 255
    ])) {

        $errors[] =
            "The emergency contact name must be between 2 and 255 characters.";
    }


    if (is_blank($patient['emergency_phone'] ?? '')) {

        $errors[] =
            "Please fill in the phone number of the patient's emergency contact.";

    } elseif (!has_length($patient['emergency_phone'], [
        'max' => 20
    ])) {

        $errors[] =
            "The emergency contact phone number must have at most 20 characters.";
    }


    if (is_blank($patient['emergency_relationship'] ?? '')) {

        $errors[] =
            "Please fill in the relationship of the patient with the emergency contact.";

    } elseif (!has_length($patient['emergency_relationship'], [
        'min' => 2,
        'max' => 255
    ])) {

        $errors[] =
            "The emergency contact relationship must be between 2 and 255 characters.";
    }


    /*
    |--------------------------------------------------------------------------
    | Patient Status
    |--------------------------------------------------------------------------
    */

    if (is_blank($patient['status'] ?? '')) {

        $errors[] =
            "Select the patient's current status.";
    }


    return $errors;
}

function search_patients_for_principal($search, $currentPatientId) {

    global $db_1;


    $search =
        trim($search);

    $currentPatientId =
        (int)$currentPatientId;


    if (
        $search === '' ||
        $currentPatientId <= 0
    ) {

        return [];

    }


    $sql = "
        SELECT
            id,
            patient_id,
            surname,
            first_name,
            middle_name,
            patient_category

        FROM patients

        WHERE id != ?

        AND (
            surname LIKE ?
            OR first_name LIKE ?
            OR middle_name LIKE ?
        )

        ORDER BY
            surname ASC,
            first_name ASC

        LIMIT 10
    ";


    $stmt =
        $db_1->prepare($sql);


    if (!$stmt) {

        error_log(
            'search_patients_for_principal prepare failed: ' .
            $db_1->error
        );

        return [];

    }


    $searchTerm =
        '%' . $search . '%';


    $stmt->bind_param(
        'isss',
        $currentPatientId,
        $searchTerm,
        $searchTerm,
        $searchTerm
    );


    if (!$stmt->execute()) {

        error_log(
            'search_patients_for_principal execute failed: ' .
            $stmt->error
        );

        $stmt->close();

        return [];

    }


    $result =
        $stmt->get_result();


    $patients = [];


    while (
        $row =
            $result->fetch_assoc()
    ) {

        $patients[] = [

            'id' =>
                (int)$row['id'],

            'patient_id' =>
                $row['patient_id'],

            'surname' =>
                $row['surname'],

            'first_name' =>
                $row['first_name'],

            'middle_name' =>
                $row['middle_name'],

            'patient_category' =>
                $row['patient_category']

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
    
    $errors = validate_patient($patient);
    if(!empty($errors)) {
        return $errors;
    }
    
    $system_patient_id = generate_patient_id();
    
    
    $sql = "INSERT INTO patients ";
    $sql .= "(patient_id, surname, first_name, middle_name, sex, date_of_birth, phone, email, address, patient_category, matric_number, staff_number, faculty, department, level, position, blood_group, genotype, allergies, chronic_conditions, emergency_contact_name, emergency_contact_phone, emergency_contact_relationship, profile_image, status) ";
    $sql .= "VALUES ";
    $sql .= "(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $query = $db_1->prepare($sql);
    
    $query->bind_param("ssssssisssssssissssssisss", $system_patient_id, $patient['surname'], $patient['first_name'], $patient['middle_name'], $patient['sex'], $patient['date_of_birth'], $patient['phone'], $patient['email'], $patient['address'], $patient['patient_category'], $patient['matric_number'], $patient['staff_number'], $patient['faculty'], $patient['department'], $patient['level'], $patient['position'], $patient['blood_group'], $patient['genotype'], $patient['allergies'], $patient['chronic_conditions'], $patient['emergency_contact_name'], $patient['emergency_contact_phone'], $patient['emergency_contact_relationship'], $patient['profile_image'], $patient['status']);
    $query->execute();
    
    $newId = $db_1->insert_id; // THIS is the real ID

if ($query->affected_rows > 0) {
    $actorId = $_SESSION['staff_id'] ?? $newId; // fallback
    logAction($actorId, 'CREATED', 'patient', $newId);
}
    
    $query->close();
    
    return [
    'success' => true,
    'id' => $newId
];
}


function update_patient($patient) {
    global $db_1;


    /*
    |--------------------------------------------------------------------------
    | Validate patient data
    |--------------------------------------------------------------------------
    */

    $errors = validate_patient($patient);

    if (!empty($errors)) {
        return $errors;
    }


    /*
    |--------------------------------------------------------------------------
    | Prepare Patient Update
    |--------------------------------------------------------------------------
    |
    | patient_id is NOT changed.
    | registration_source is NOT changed.
    | registered_by is NOT changed.
    |
    */

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

    error_log(
        'update_patient prepare failed: ' .
        $db_1->error
    );

    return [
        "Unable to prepare patient update."
    ];

}


    /*
    |--------------------------------------------------------------------------
    | Basic Information
    |--------------------------------------------------------------------------
    */

    $surname =
        trim($patient['surname'] ?? '');

    $firstName =
        trim($patient['first_name'] ?? '');

    $middleName =
        trim($patient['middle_name'] ?? '');

    $gender =
        $patient['gender'] ?? null;

    $nationality =
        $patient['nationality'] ?? null;

    $stateOfOrigin =
        $patient['state_of_origin'] ?? null;

    $lga =
        $patient['lga'] ?? null;

    $maritalStatus =
        $patient['marital_status'] ?? null;

    $dateOfBirth =
        $patient['date_of_birth'] ?? null;


    /*
    |--------------------------------------------------------------------------
    | Contact Information
    |--------------------------------------------------------------------------
    */

    $phone =
        trim($patient['phone'] ?? '');

    $alternatePhone =
        trim($patient['alternate_phone'] ?? '');

    $email =
        trim($patient['email'] ?? '');

    $address =
        trim($patient['address'] ?? '');

    $city =
        trim($patient['city'] ?? '');

    $residentialState =
        trim($patient['residential_state'] ?? '');

    $postalCode =
        trim($patient['postal_code'] ?? '');

    $state =
        trim($patient['state'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | University / Employment Information
    |--------------------------------------------------------------------------
    */

    $patientCategory =
        $patient['patient_category'] ?? null;

    $matricNumber =
        trim($patient['matric_number'] ?? '');

    $staffNumber =
        trim($patient['staff_number'] ?? '');

    $faculty =
        trim($patient['faculty'] ?? '');

    $department =
        trim($patient['department'] ?? '');

    $level =
        trim($patient['level'] ?? '');

    $staffPosition =
        trim($patient['staff_position'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Medical Information
    |--------------------------------------------------------------------------
    */

    $bloodGroup =
        trim($patient['blood_group'] ?? '');

    $genotype =
        trim($patient['genotype'] ?? '');

    $allergies =
        trim($patient['allergies'] ?? '');

    $chronicConditions =
        trim($patient['chronic_conditions'] ?? '');

    $disabilities =
        trim($patient['disabilities'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Other Information
    |--------------------------------------------------------------------------
    */

    $status =
        $patient['status'] ?? 'Active';

    $occupation =
        trim($patient['occupation'] ?? '');

    $employer =
        trim($patient['employer'] ?? '');

    $relationshipToPrincipal =
        trim($patient['relationship_to_principal'] ?? '');

    $principalPatientId =
        !empty($patient['principal_patient_id'])
        ? (int)$patient['principal_patient_id']
        : null;

    $patientDatabaseId =
        (int)$patient['id'];


    /*
    |--------------------------------------------------------------------------
    | Bind Parameters
    |--------------------------------------------------------------------------
    |
    | 33 string values
    | 2 integer values
    |
    */

    $query->bind_param(
        "sssssssssssssssssssssssssssssssssii",

        $surname,
        $firstName,
        $middleName,
        $gender,
        $nationality,
        $stateOfOrigin,
        $lga,
        $maritalStatus,
        $dateOfBirth,

        $phone,
        $alternatePhone,
        $email,
        $address,
        $city,
        $residentialState,
        $postalCode,
        $state,

        $patientCategory,
        $matricNumber,
        $staffNumber,
        $faculty,
        $department,
        $level,
        $staffPosition,

        $bloodGroup,
        $genotype,
        $allergies,
        $chronicConditions,
        $disabilities,

        $status,
        $occupation,
        $employer,
        $relationshipToPrincipal,

        $principalPatientId,
        $patientDatabaseId
    );


    /*
    |--------------------------------------------------------------------------
    | Execute Update
    |--------------------------------------------------------------------------
    */

   if (!$query->execute()) {

    error_log(
        'update_patient execute failed: ' .
        $query->error
    );

    $query->close();

    return [
        "Unable to update patient information."
    ];

}


    /*
    |--------------------------------------------------------------------------
    | Audit Log
    |--------------------------------------------------------------------------
    */

    if ($query->affected_rows > 0) {

        $actorId =
            $_SESSION['staff_id'] ?? null;

        if ($actorId !== null) {

            logAction(
                $actorId,
                'UPDATED',
                'patient',
                $patientDatabaseId
            );
        }
    }

    $query->close();


    /*
    |--------------------------------------------------------------------------
    | Emergency Contact
    |--------------------------------------------------------------------------
    |
    | Emergency contact information is stored separately.
    |
    */

    $emergencyName =
        trim($patient['emergency_name'] ?? '');

    $emergencyPhone =
        trim($patient['emergency_phone'] ?? '');

    $emergencyRelationship =
        trim($patient['emergency_relationship'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Find Existing Primary Emergency Contact
    |--------------------------------------------------------------------------
    */

    $checkSql = "
        SELECT id
        FROM patient_emergency_contacts
        WHERE patient_id = ?
        AND is_primary = 1
        LIMIT 1
    ";

    $checkStmt =
        $db_1->prepare($checkSql);


    if (!$checkStmt) {

        error_log(
            'Emergency contact lookup failed: ' .
            $db_1->error
        );

        return ["Unable to verify the patient's emergency contact information."];
    }


    $checkStmt->bind_param(
        "i",
        $patientDatabaseId
    );

    $checkStmt->execute();

    $checkResult =
        $checkStmt->get_result();

    $existingEmergency =
        $checkResult->fetch_assoc();

    $checkStmt->close();


    /*
    |--------------------------------------------------------------------------
    | Update Existing Emergency Contact
    |--------------------------------------------------------------------------
    */

    if ($existingEmergency) {

        $emergencyId =
            (int)$existingEmergency['id'];


        $emergencySql = "
            UPDATE patient_emergency_contacts
            SET
                contact_name = ?,
                relationship = ?,
                phone = ?
            WHERE id = ?
            LIMIT 1
        ";


        $emergencyStmt =
            $db_1->prepare($emergencySql);


        if (!$emergencyStmt) {

    error_log(
        'Emergency contact update prepare failed: ' . $db_1->error
    );

    return [
        "Unable to update the patient's emergency contact information."
    ];
}


$emergencyStmt->bind_param(
    "sssi",
    $emergencyName,
    $emergencyRelationship,
    $emergencyPhone,
    $emergencyId
);


if (!$emergencyStmt->execute()) {

    error_log(
        'Emergency contact update failed: ' .
        $emergencyStmt->error
    );

    $emergencyStmt->close();

    return [
        "Unable to update the patient's emergency contact information."
    ];
}


$emergencyStmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | Create Emergency Contact If None Exists
    |--------------------------------------------------------------------------
    */

    else {

        if (
            $emergencyName !== '' ||
            $emergencyPhone !== '' ||
            $emergencyRelationship !== ''
        ) {

            $isPrimary = 1;


            $emergencySql = "
                INSERT INTO patient_emergency_contacts (
                    patient_id,
                    contact_name,
                    relationship,
                    phone,
                    is_primary
                )
                VALUES (?, ?, ?, ?, ?)
            ";


            $emergencyStmt =
                $db_1->prepare($emergencySql);


            if (!$emergencyStmt) {

    error_log(
        'Emergency contact insert prepare failed: ' .
        $db_1->error
    );

    return [
        "Unable to create the patient's emergency contact."
    ];
}


$emergencyStmt->bind_param(
    "isssi",
    $patientDatabaseId,
    $emergencyName,
    $emergencyRelationship,
    $emergencyPhone,
    $isPrimary
);


if (!$emergencyStmt->execute()) {

    error_log(
        'Emergency contact insert failed: ' .
        $emergencyStmt->error
    );

    $emergencyStmt->close();

    return [
        "Unable to create the patient's emergency contact."
    ];
}


$emergencyStmt->close();
        }
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