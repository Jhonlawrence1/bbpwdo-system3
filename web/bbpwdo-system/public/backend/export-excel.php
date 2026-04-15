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
    
    $headers = ['ID', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Sex', 'Age', 'Birthdate', 'Blood Type', 'Civil Status', 'Contact Number', 'Address', 'PWD ID Number', 'Issued Date', 'Expiry Date', 'Registered', 'Employment Status', 'Employment Type', 'Elementary', 'High School', 'College', 'Vocational', 'Disability Type', 'Assistive Device', 'Guardian Name', 'Guardian Contact', 'Skills', 'Trainings'];
    echo implode("\t", $headers) . "\n";
    
    foreach ($records as $r) {
        echo $r['id'] . "\t";
        echo ($r['last_name'] ?? '') . "\t";
        echo ($r['first_name'] ?? '') . "\t";
        echo ($r['middle_name'] ?? '') . "\t";
        echo ($r['suffix'] ?? '') . "\t";
        echo ($r['sex'] ?? '') . "\t";
        echo ($r['age'] ?? '') . "\t";
        echo ($r['birthdate'] ?? '') . "\t";
        echo ($r['blood_type'] ?? '') . "\t";
        echo ($r['civil_status'] ?? '') . "\t";
        echo ($r['contact_number'] ?? '') . "\t";
        echo str_replace(array("\t", "\n", "\r"), " ", ($r['address'] ?? '')) . "\t";
        echo ($r['pwd_id_number'] ?? '') . "\t";
        echo ($r['issued_date'] ?? '') . "\t";
        echo ($r['expiry_date'] ?? '') . "\t";
        echo ($r['is_registered'] ?? '') . "\t";
        echo ($r['employment_status'] ?? '') . "\t";
        echo ($r['employment_type'] ?? '') . "\t";
        echo ($r['education_elementary'] ?? '') . "\t";
        echo ($r['education_highschool'] ?? '') . "\t";
        echo ($r['education_college'] ?? '') . "\t";
        echo ($r['education_vocational'] ?? '') . "\t";
        echo ($r['disability_type'] ?? '') . "\t";
        echo ($r['assistive_device'] ?? '') . "\t";
        echo ($r['guardian_name'] ?? '') . "\t";
        echo ($r['guardian_contact'] ?? '') . "\t";
        echo ($r['skills'] ?? '') . "\t";
        echo ($r['trainings'] ?? '') . "\n";
    }
    
    echo "\n--- FAMILY MEMBERS ---\n";
    echo "PWD ID\tFamily Member Name\tAge\tCivil Status\tRelationship\tOccupation\n";
    
    $famStmt = $pdo->prepare("SELECT * FROM family_members WHERE pwd_id = :pwd_id");
    foreach ($records as $r) {
        $famStmt->execute([':pwd_id' => $r['id']]);
        $family = $famStmt->fetchAll();
        if (!empty($family)) {
            foreach ($family as $f) {
                echo $r['id'] . "\t";
                echo ($f['name'] ?? '') . "\t";
                echo ($f['age'] ?? '') . "\t";
                echo ($f['civil_status'] ?? '') . "\t";
                echo ($f['relationship'] ?? '') . "\t";
                echo ($f['occupation'] ?? '') . "\n";
            }
        }
    }
    
} catch (Exception $e) {
    header('Content-Type: text/plain');
    echo "Error: " . $e->getMessage();
}
?>