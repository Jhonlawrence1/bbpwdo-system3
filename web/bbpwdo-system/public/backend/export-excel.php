<?php
error_reporting(0);
ini_set('display_errors', 0);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$employment = isset($_GET['employment']) ? $_GET['employment'] : '';
$disability = isset($_GET['disability']) ? $_GET['disability'] : '';

try {
    require_once 'db.php';
    
    if (!$pdo) {
        throw new Exception("Database connection failed");
    }
    
    $where = '1=1';
    $params = [];
    
    if ($search) {
        $where .= " AND (last_name LIKE :search OR first_name LIKE :search OR pwd_id_number LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if ($status) {
        $where .= " AND is_registered = :status";
        $params[':status'] = $status;
    }
    
    if ($employment) {
        $where .= " AND employment_status = :employment";
        $params[':employment'] = $employment;
    }
    
    if ($disability) {
        $where .= " AND disability_type LIKE :disability";
        $params[':disability'] = "%$disability%";
    }
    
    $sql = "SELECT * FROM pwd_records WHERE $where ORDER BY id DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $records = $stmt->fetchAll();
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="bbpwdo_records_' . date('Y-m-d') . '.xls"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo "BBPWDO PWD Records\n";
    echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";
    
    $headers = ['ID', 'Last Name', 'First Name', 'Middle Name', 'Sex', 'Age', 'Birthdate', 'Civil Status', 'Contact Number', 'Address', 'PWD ID Number', 'Registered', 'Employment Status', 'Disability Type'];
    echo implode("\t", $headers) . "\n";
    
    foreach ($records as $r) {
        echo $r['id'] . "\t";
        echo ($r['last_name'] ?? '') . "\t";
        echo ($r['first_name'] ?? '') . "\t";
        echo ($r['middle_name'] ?? '') . "\t";
        echo ($r['sex'] ?? '') . "\t";
        echo ($r['age'] ?? '') . "\t";
        echo ($r['birthdate'] ?? '') . "\t";
        echo ($r['civil_status'] ?? '') . "\t";
        echo ($r['contact_number'] ?? '') . "\t";
        echo str_replace("\t", " ", ($r['address'] ?? '')) . "\t";
        echo ($r['pwd_id_number'] ?? '') . "\t";
        echo ($r['is_registered'] ?? '') . "\t";
        echo ($r['employment_status'] ?? '') . "\t";
        echo ($r['disability_type'] ?? '') . "\n";
    }
    
} catch (Exception $e) {
    header('Content-Type: text/plain');
    echo "Error: " . $e->getMessage();
}
?>