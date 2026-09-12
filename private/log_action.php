<?php
function logAction($staffId, $action, $entityType = null, $entityId = null) {
    global $db_1;
    
    if (!$staffId || !$action) return;

    try {
        $sql = "INSERT INTO audit_logs (staff_id, action, entity_type, entity_id, created_at) VALUES (?, ?, ?, ?, NOW())";
        $query = $db_1->prepare($sql);
        if ($query) {
            $query->bind_param("issi", $staffId, $action, $entityType, $entityId);
            $query->execute();
            $query->close();
        }
    } catch (\Throwable $e) {
        error_log("logAction failed: " . $e->getMessage());
    }
}