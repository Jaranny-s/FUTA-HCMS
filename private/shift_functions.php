<?php

/**
 * Returns available shift definitions with metadata.
 */
function get_shift_definitions() {
    return [
        'Morning' => [
            'name' => 'Morning Shift',
            'time' => '08:00 AM - 02:00 PM',
            'bg' => '#e0f2fe',
            'color' => '#0369a1',
            'border' => '#bae6fd',
            'icon' => 'bi-sun-fill'
        ],
        'Afternoon' => [
            'name' => 'Afternoon Shift',
            'time' => '02:00 PM - 08:00 PM',
            'bg' => '#fef3c7',
            'color' => '#b45309',
            'border' => '#fde68a',
            'icon' => 'bi-brightness-high-fill'
        ],
        'Night' => [
            'name' => 'Night Shift (Call Duty)',
            'time' => '08:00 PM - 08:00 AM',
            'bg' => '#f3e8ff',
            'color' => '#7e22ce',
            'border' => '#e9d5ff',
            'icon' => 'bi-moon-stars-fill'
        ],
        'Off Duty' => [
            'name' => 'Off Duty',
            'time' => 'Rest Day / Standby',
            'bg' => '#f1f5f9',
            'color' => '#475569',
            'border' => '#e2e8f0',
            'icon' => 'bi-slash-circle'
        ]
    ];
}

/**
 * Assigns a shift to a doctor and manages duty_status transition.
 */
function assign_doctor_shift($doctor_id, $shift, $assigned_by_staff_id = null) {
    global $db_1;
    $valid_shifts = ['Morning', 'Afternoon', 'Night', 'Off Duty'];
    if (!in_array($shift, $valid_shifts)) {
        return false;
    }
    $doc_id = (int)$doctor_id;

    // Fetch current doctor details
    $doc_res = $db_1->query("SELECT full_name, role, current_shift, duty_status FROM staff WHERE id = {$doc_id} LIMIT 1");
    $doctor = $doc_res ? $doc_res->fetch_assoc() : null;
    if (!$doctor) return false;

    // If setting to Off Duty, automatically update duty_status to Off Duty
    // If setting to an active shift and duty_status was Off Duty, set to Available
    $new_duty_status = $doctor['duty_status'];
    if ($shift === 'Off Duty') {
        $new_duty_status = 'Off Duty';
    } elseif ($doctor['duty_status'] === 'Off Duty') {
        $new_duty_status = 'Available';
    }

    $stmt = $db_1->prepare("UPDATE staff SET current_shift = ?, duty_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $shift, $new_duty_status, $doc_id);
    $success = $stmt->execute();
    $stmt->close();

    if ($success && function_exists('logAction')) {
        $staff_id = $assigned_by_staff_id ?? ($_SESSION['staff_id'] ?? 1);
        logAction($staff_id, "Assigned shift '{$shift}' (Duty: {$new_duty_status}) to Dr. {$doctor['full_name']}", 'staff', $doc_id);
    }
    return $success;
}

/**
 * Retrieves full shift roster of all doctors.
 */
function get_doctor_shift_roster() {
    global $db_1;
    $sql = "SELECT s.id, s.system_staff_id, s.full_name, s.email, s.department, 
                   s.status, s.duty_status, s.current_shift, s.profile_image,
                   COUNT(CASE WHEN e.status IN ('Waiting', 'In Progress') THEN 1 END) as active_queue_count
            FROM staff s
            LEFT JOIN encounters e ON e.doctor_id = s.id
            WHERE s.role = 'doctor'
            GROUP BY s.id
            ORDER BY s.full_name ASC";
    return $db_1->query($sql);
}
?>
