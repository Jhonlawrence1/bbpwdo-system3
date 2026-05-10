<?php
$dbUrl = 'mysql://root:lcVREDwKUblqHHhXVgcrEWCXhNjdEOCp@monorail.proxy.rlwy.net:19171/railway';

$url = parse_url($dbUrl);
$host = $url['host'];
$port = $url['port'];
$dbName = ltrim($url['path'], '/');
$user = $url['user'];
$pass = $url['pass'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbName;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $columns = [
        ['name' => 'assistive_device', 'type' => 'VARCHAR(200)'],
        ['name' => 'employment_type', 'type' => 'VARCHAR(50)'],
        ['name' => 'employer_address', 'type' => 'TEXT'],
        ['name' => 'education_elementary', 'type' => 'VARCHAR(100)'],
        ['name' => 'education_highschool', 'type' => 'VARCHAR(100)'],
        ['name' => 'education_college', 'type' => 'VARCHAR(100)'],
        ['name' => 'education_vocational', 'type' => 'VARCHAR(100)'],
        ['name' => 'guardian_name', 'type' => 'VARCHAR(100)'],
        ['name' => 'guardian_relationship', 'type' => 'VARCHAR(50)'],
        ['name' => 'guardian_contact', 'type' => 'VARCHAR(50)'],
        ['name' => 'guardian_address', 'type' => 'TEXT'],
        ['name' => 'skills', 'type' => 'TEXT'],
        ['name' => 'trainings', 'type' => 'TEXT']
    ];
    
    $results = [];
    
    foreach ($columns as $col) {
        try {
            $pdo->exec("ALTER TABLE pwd_records ADD COLUMN " . $col['name'] . " " . $col['type']);
            $results[] = "Added: " . $col['name'];
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                $results[] = "Exists: " . $col['name'];
            } else {
                $results[] = "Error " . $col['name'] . ": " . $e->getMessage();
            }
        }
    }
    
    echo json_encode(['success' => true, 'message' => implode(', ', $results)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
