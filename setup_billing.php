<?php
require_once('private/config.php');

$sql = "CREATE TABLE IF NOT EXISTS billing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    encounter_id INT,
    patient_id INT,
    billing_category VARCHAR(50),
    amount DECIMAL(10,2),
    payment_status VARCHAR(20) DEFAULT 'Pending',
    remarks TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($db_1->query($sql)) {
    echo "Billing table created successfully.\n";
} else {
    echo "Error creating table: " . $db_1->error . "\n";
}
?>
