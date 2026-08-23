<?php
function logAction($staffId, $action, $entityType = null, $entityId = null) {
    global $db_1;
    
	 if (!$staffId || !$action) return; //  prevent bad/NULL logs

	
    $sql = "INSERT INTO audit_logs ";
    $sql .= "(staff_id, action, entity_type, entity_id, created_at) ";
    $sql .=  "VALUES ";
    $sql .= "(?, ?, ?, ?, NOW())";

    $query = $db_1->prepare($sql);

    $query->bind_param("issi", $staffId, $action, $entityType, $entityId);
    $query->execute();
    $query->close();
}