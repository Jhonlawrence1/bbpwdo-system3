<?php
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once 'db.php';

try {
    // Create family_members table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS family_members (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pwd_id INT NOT NULL,
        name VARCHAR(100),
        age INT,
        civil_status VARCHAR(30),
        relationship VARCHAR(50),
        occupation VARCHAR(100),
        FOREIGN KEY (pwd_id) REFERENCES pwd_records(id) ON DELETE CASCADE
    )");
    
    // Check if columns exist in pwd_records, add if missing
    $columns = $pdo->query("SHOW COLUMNS FROM pwd_records")->fetchAll(PDO::FETCH_COLUMN);
    
    $newColumns = [
        'disability_type' => 'TEXT',
        'assistive_device' => 'TEXT',
        'employment_type' => 'VARCHAR(50)',
        'education_elementary' => 'VARCHAR(100)',
        'education_highschool' => 'VARCHAR(100)',
        'education_college' => 'VARCHAR(100)',
        'education_vocational' => 'VARCHAR(100)',
        'guardian_name' => 'VARCHAR(100)',
        'guardian_relationship' => 'VARCHAR(50)',
        'guardian_contact' => 'VARCHAR(20)',
        'guardian_address' => 'TEXT',
        'skills' => 'TEXT',
        'trainings' => 'TEXT'
    ];
    
    foreach ($newColumns as $col => $type) {
        if (!in_array($col, $columns)) {
            $pdo->exec("ALTER TABLE pwd_records ADD COLUMN $col $type");
        }
    }
    
    echo json_encode(['success' => true, 'message' => 'Database setup completed']);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
