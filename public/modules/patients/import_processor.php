<?php
require_once('../../../private/config.php');
require_password_reset();

if (!hasPermission('create_patient')) {
    redirect_to(url_wrap('/staff/dashboard.php'));
}

if (is_post_request() && isset($_FILES['csv_file'])) {
    
    $file = $_FILES['csv_file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = "File upload error.";
        redirect_to(url_wrap('/modules/patients/import.php'));
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'csv') {
        $_SESSION['error'] = "Invalid file type. Please upload a CSV.";
        redirect_to(url_wrap('/modules/patients/import.php'));
    }
    
    $handle = fopen($file['tmp_name'], 'r');
    if ($handle === false) {
        $_SESSION['error'] = "Could not read the uploaded file.";
        redirect_to(url_wrap('/modules/patients/import.php'));
    }
    
    $headers = fgetcsv($handle);
    if (!$headers) {
        $_SESSION['error'] = "The CSV file is empty or invalid.";
        redirect_to(url_wrap('/modules/patients/import.php'));
    }
    
    // Normalize headers
    $headers = array_map('strtolower', array_map('trim', $headers));
    
    $successCount = 0;
    $errorCount = 0;
    
    while (($data = fgetcsv($handle)) !== false) {
        // Map row to associative array
        $row = array_combine($headers, $data);
        if (!$row) continue; // Skip bad rows
        
        $patient = [
            'surname' => $row['surname'] ?? '',
            'first_name' => $row['first_name'] ?? '',
            'middle_name' => $row['middle_name'] ?? '',
            'gender' => $row['gender'] ?? '',
            'phone' => $row['phone'] ?? '',
            'email' => $row['email'] ?? '',
            'patient_category' => $row['category'] ?? 'Student',
            'status' => 'Active',
            'profile_image' => null,
            // Provide empty strings for required fields not in simple CSV
            'nationality' => '',
            'state_of_origin' => '',
            'lga' => '',
            'marital_status' => '',
            'date_of_birth' => '',
            'alternate_phone' => '',
            'residential_address' => '',
            'emergency_name' => '',
            'emergency_phone' => '',
            'emergency_relationship' => '',
            'blood_group' => '',
            'genotype' => '',
            'allergies' => '',
            'chronic_conditions' => '',
            'disabilities' => ''
        ];
        
        $result = insert_patient($patient);
        if ($result['success'] === true) {
            $successCount++;
        } else {
            $errorCount++;
        }
    }
    
    fclose($handle);
    
    $_SESSION['message'] = "Import complete. Successfully imported $successCount patients. Failed: $errorCount.";
    redirect_to(url_wrap('/modules/patients/index.php'));
    
} else {
    redirect_to(url_wrap('/modules/patients/import.php'));
}
?>
