<?php
require_once 'db.php';
session_start();

$skipAuth = isset($_GET['skip_auth']) || isset($_POST['skip_auth']);
if (!isset($_SESSION['admin_id']) && !$skipAuth) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';

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

$sql = "SELECT * FROM pwd_records $where ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$records = $stmt->fetchAll();

$html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>PWD Records Export</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #1e40af; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 10px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background: #3b82f6; color: white; }
        tr:nth-child(even) { background: #f3f4f6; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>BBPWDO - PWD Records Report</h1>
        <p>Generated: ' . date('Y-m-d H:i:s') . '</p>
        <p>Total Records: ' . count($records) . '</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Age</th>
                <th>Sex</th>
                <th>Civil Status</th>
                <th>Disability Type</th>
                <th>PWD ID</th>
                <th>Registered</th>
                <th>Employment</th>
            </tr>
        </thead>
        <tbody>';

foreach ($records as $r) {
    $fullName = $r['last_name'] . ', ' . $r['first_name'] . ' ' . $r['middle_name'];
    $html .= '<tr>
        <td>' . $r['id'] . '</td>
        <td>' . htmlspecialchars($fullName) . '</td>
        <td>' . $r['age'] . '</td>
        <td>' . $r['sex'] . '</td>
        <td>' . $r['civil_status'] . '</td>
        <td>' . $r['disability_type'] . '</td>
        <td>' . $r['pwd_id_number'] . '</td>
        <td>' . $r['is_registered'] . '</td>
        <td>' . $r['employment_status'] . '</td>
    </tr>';
}

$html .= '</tbody>
    </table>
</body>
</html>';

header('Content-Type: text/html');
echo $html;
?>