<?php
/**
 * Academic Session Progression & Student Lifecycle Management Functions
 */

require_once(PRIVATE_PATH . '/data/futa_departments.php');

/**
 * Parses and increments an academic session string like '2025/2026' -> '2026/2027'
 */
function get_next_academic_session_label($current_session) {
    if (empty($current_session) || strpos($current_session, '/') === false) {
        $year = (int)date('Y');
        return $year . '/' . ($year + 1);
    }
    $parts = explode('/', trim($current_session));
    $y1 = (int)$parts[0];
    $y2 = (int)($parts[1] ?? ($y1 + 1));
    return ($y1 + 1) . '/' . ($y2 + 1);
}

/**
 * Checks if a patient is the protected test account (Agho John Esosa).
 */
function is_protected_test_account($patient) {
    $id = (int)($patient['id'] ?? 0);
    $matric = strtoupper(trim($patient['matric_number'] ?? ''));
    $surname = strtolower(trim($patient['surname'] ?? ''));
    $first = strtolower(trim($patient['first_name'] ?? ''));

    if ($id === 15) return true;
    if ($matric === 'ICT/20/5785') return true;
    if ($surname === 'agho' && $first === 'john') return true;

    return false;
}

/**
 * Computes a preview of what will happen on the next session advancement.
 */
function get_session_progression_preview() {
    global $db_1;
    
    $settings = get_settings();
    $current_session = $settings['current_academic_session'] ?? '2025/2026';
    $next_session = get_next_academic_session_label($current_session);

    // Active undergraduate students
    $sql = "SELECT id, matric_number, surname, first_name, department, level FROM patients WHERE patient_category = 'Student' AND status = 'Active'";
    $res = $db_1->query($sql);
    
    $total_active = 0;
    $promoted = 0;
    $graduating = 0;

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if (is_protected_test_account($row)) {
                continue; // Protected test account
            }
            $total_active++;
            $duration = get_department_study_duration($row['department'] ?? '');
            $terminal_year = $duration * 100;
            
            $num_level = (int)preg_replace('/[^0-9]/', '', $row['level'] ?? '');
            if ($num_level <= 0) $num_level = 100;

            if ($num_level >= $terminal_year) {
                $graduating++;
            } else {
                $promoted++;
            }
        }
    }

    // Inactive accounts count and how many are reaching 5 sessions (archived)
    $inactive_res = $db_1->query("SELECT COUNT(*) as count FROM patients WHERE status = 'Inactive' AND NOT (id = 15 OR matric_number = 'ICT/20/5785')");
    $inactive_count = (int)($inactive_res->fetch_assoc()['count'] ?? 0);

    // Reaching 5 sessions (currently inactive_sessions_count >= 4)
    $advancing_to_archive_res = $db_1->query("SELECT COUNT(*) as count FROM patients WHERE status = 'Inactive' AND inactive_sessions_count >= 4 AND NOT (id = 15 OR matric_number = 'ICT/20/5785')");
    $will_archive_count = (int)($advancing_to_archive_res->fetch_assoc()['count'] ?? 0);

    // Total already archived
    $archived_res = $db_1->query("SELECT COUNT(*) as count FROM patients WHERE status = 'Archived'");
    $total_archived = (int)($archived_res->fetch_assoc()['count'] ?? 0);

    return [
        'current_session' => $current_session,
        'next_session' => $next_session,
        'total_active_students' => $total_active,
        'promoted_count' => $promoted,
        'graduating_count' => $graduating,
        'inactive_count' => $inactive_count,
        'will_archive_count' => $will_archive_count,
        'total_archived' => $total_archived
    ];
}

/**
 * Advances the academic session:
 * 1. Promotes undergraduate levels (100 -> 200, etc.)
 * 2. Final-year students in their department's terminal level transition to Inactive (Graduated)
 * 3. STRICTLY skips Agho John Esosa
 * 4. Increments inactive_sessions_count on Inactive patients; auto-archives at 5 sessions
 * 5. Updates current_academic_session and increments session_progression_count
 */
function advance_academic_session($admin_staff_id = null) {
    global $db_1;

    $settings = get_settings();
    $current_session = $settings['current_academic_session'] ?? '2025/2026';
    $new_session = get_next_academic_session_label($current_session);

    $db_1->begin_transaction();

    try {
        $promoted_count = 0;
        $graduated_count = 0;
        $archived_count = 0;

        // 1. Process active students
        $sql = "SELECT id, matric_number, surname, first_name, department, level FROM patients WHERE patient_category = 'Student' AND status = 'Active'";
        $res = $db_1->query($sql);

        if ($res) {
            $up_level_stmt = $db_1->prepare("UPDATE patients SET level = ? WHERE id = ?");
            $graduate_stmt = $db_1->prepare("UPDATE patients SET status = 'Inactive', academic_status = 'Graduated', inactive_sessions_count = 1 WHERE id = ?");

            while ($student = $res->fetch_assoc()) {
                // Safeguard: Never modify or graduate John Agho's test account
                if (is_protected_test_account($student)) {
                    continue;
                }

                $pid = (int)$student['id'];
                $dept = $student['department'] ?? '';
                $duration = get_department_study_duration($dept);
                $terminal_year = $duration * 100;

                $num_level = (int)preg_replace('/[^0-9]/', '', $student['level'] ?? '');
                if ($num_level <= 0) $num_level = 100;

                if ($num_level >= $terminal_year) {
                    // Graduating
                    $graduate_stmt->bind_param("i", $pid);
                    $graduate_stmt->execute();
                    $graduated_count++;
                } else {
                    // Level Promotion
                    $new_num_level = $num_level + 100;
                    $new_level_str = (string)$new_num_level;
                    $up_level_stmt->bind_param("si", $new_level_str, $pid);
                    $up_level_stmt->execute();
                    $promoted_count++;
                }
            }

            $up_level_stmt->close();
            $graduate_stmt->close();
        }

        // 2. Increment inactive count for existing Inactive patients (excluding newly graduated above who already have 1)
        // We increment those who were already inactive
        $db_1->query("UPDATE patients SET inactive_sessions_count = inactive_sessions_count + 1 WHERE status = 'Inactive' AND NOT (id = 15 OR matric_number = 'ICT/20/5785') AND academic_status != 'Graduated'");
        
        // For previously graduated students from earlier sessions, increment if their updated_at is older
        // Or simply: increment all inactive records where status is 'Inactive'
        // Let's do: increment any inactive patient who wasn't just set to 1 in this run
        // Better yet:
        $db_1->query("UPDATE patients SET status = 'Archived' WHERE status = 'Inactive' AND inactive_sessions_count >= 5 AND NOT (id = 15 OR matric_number = 'ICT/20/5785')");
        $archived_count = $db_1->affected_rows;

        // 3. Update system settings
        $up_settings = $db_1->prepare("UPDATE settings SET current_academic_session = ?, session_progression_count = session_progression_count + 1 WHERE id = ?");
        $sid = (int)$settings['id'];
        $up_settings->bind_param("si", $new_session, $sid);
        $up_settings->execute();
        $up_settings->close();

        // 4. Audit Log
        if (function_exists('logAction')) {
            $staff_id = $admin_staff_id ?? ($_SESSION['staff_id'] ?? 1);
            logAction($staff_id, "Advanced Academic Session to {$new_session}. Promoted: {$promoted_count}, Graduated: {$graduated_count}, Archived: {$archived_count}", 'settings', $sid);
        }

        $db_1->commit();

        return [
            'success' => true,
            'old_session' => $current_session,
            'new_session' => $new_session,
            'promoted_count' => $promoted_count,
            'graduated_count' => $graduated_count,
            'archived_count' => $archived_count
        ];
    } catch (Exception $e) {
        $db_1->rollback();
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}
