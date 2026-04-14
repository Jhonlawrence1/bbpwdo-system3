<?php
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename=bbpwdo_records_' . date('Y-m-d') . '.xls');
header('Pragma: no-cache');
header('Expires: 0');

require_once 'db.php';
session_start();

$skipAuth = isset($_GET['skip_auth']) || isset($_POST['skip_auth']);
if (!isset($_SESSION['admin_id']) && !$skipAuth) {
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$employment = isset($_GET['employment']) ? $_GET['employment'] : '';
$disability = isset($_GET['disability']) ? $_GET['disability'] : '';

$where = '';
$params = [];

if ($search) {
    $conditions = [];
    $conditions[] = "last_name LIKE :search";
    $conditions[] = "first_name LIKE :search";
    $conditions[] = "pwd_id_number LIKE :search";
    $where .= "WHERE (" . implode(" OR ", $conditions) . ")";
    $params[':search'] = "%$search%";
}

if ($status) {
    $where .= $where ? " AND " : "WHERE ";
    $where .= "is_registered = :status";
    $params[':status'] = $status;
}

if ($employment) {
    $where .= $where ? " AND " : "WHERE ";
    $where .= "employment_status = :employment";
    $params[':employment'] = $employment;
}

if ($disability) {
    $where .= $where ? " AND " : "WHERE ";
    $where .= "disability_type LIKE :disability";
    $params[':disability'] = "%$disability%";
}

$sql = "SELECT * FROM pwd_records $where ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

echo "BBPWDO PWD Records\n";
echo "Generated: " . date('Y-m-d H:i:s') . "\n\n";

$headers = [
    'ID', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Sex', 'Age', 'Birthdate',
    'Blood Type', 'Civil Status', 'Contact Number', 'Address', 'PWD ID Number',
    'Issued Date', 'Expiry Date', 'Registered', 'Employment Status', 'Employment Type',
    'Elementary', 'High School', 'College', 'Vocational', 'Disability Type',
    'Assistive Device', 'Guardian Name', 'Guardian Relationship', 'Guardian Contact',
    'Guardian Address', 'Skills', 'Trainings'
];
echo implode("\t", $headers) . "\n";

foreach ($records as $r) {
    echo $r['id'] . "\t";
    echo $r['last_name'] . "\t";
    echo $r['first_name'] . "\t";
    echo ($r['middle_name'] ?? '') . "\t";
    echo ($r['suffix'] ?? '') . "\t";
    echo ($r['sex'] ?? '') . "\t";
    echo $r['age'] . "\t";
    echo ($r['birthdate'] ?? '') . "\t";
    echo ($r['blood_type'] ?? '') . "\t";
    echo ($r['civil_status'] ?? '') . "\t";
    echo ($r['contact_number'] ?? '') . "\t";
    echo ($r['address'] ?? '') . "\t";
    echo ($r['pwd_id_number'] ?? '') . "\t";
    echo ($r['issued_date'] ?? '') . "\t";
    echo ($r['expiry_date'] ?? '') . "\t";
    echo $r['is_registered'] . "\t";
    echo ($r['employment_status'] ?? '') . "\t";
    echo ($r['employment_type'] ?? '') . "\t";
    echo ($r['education_elementary'] ?? '') . "\t";
    echo ($r['education_highschool'] ?? '') . "\t";
    echo ($r['education_college'] ?? '') . "\t";
    echo ($r['education_vocational'] ?? '') . "\t";
    echo ($r['disability_type'] ?? '') . "\t";
    echo ($r['assistive_device'] ?? '') . "\t";
    echo ($r['guardian_name'] ?? '') . "\t";
    echo ($r['guardian_relationship'] ?? '') . "\t";
    echo ($r['guardian_contact'] ?? '') . "\t";
    echo ($r['guardian_address'] ?? '') . "\t";
    echo ($r['skills'] ?? '') . "\t";
    echo ($r['trainings'] ?? '') . "\n";
}

$famStmt = $pdo->prepare("SELECT * FROM family_members WHERE pwd_id = :pwd_id");
foreach ($records as $r) {
    $famStmt->execute([':pwd_id' => $r['id']]);
    $family = $famStmt->fetchAll();
    if (!empty($family)) {
        echo "\n--- Family Members for PWD ID " . $r['id'] . " (" . $r['last_name'] . ", " . $r['first_name'] . ") ---\n";
        echo "Name\tAge\tCivil Status\tRelationship\tOccupation\n";
        foreach ($family as $f) {
            echo ($f['name'] ?? '') . "\t";
            echo ($f['age'] ?? '') . "\t";
            echo ($f['civil_status'] ?? '') . "\t";
            echo ($f['relationship'] ?? '') . "\t";
            echo ($f['occupation'] ?? '') . "\n";
        }
    }
}
?>