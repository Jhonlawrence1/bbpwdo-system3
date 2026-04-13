<?php
/**
 * Test DB Connection & Schema
 * http://localhost/HTTML/web/bbpwdo-system/backend/test-db.php  
 */
require_once 'db.php';

echo "<h2>🧪 Database Test</h2>";
echo "<pre>";

// Connection test
try {
    $stmt = $pdo->query("SELECT 1");
    echo "✅ PDO Connection: OK\n";
    
    $db = $pdo->query("SELECT DATABASE()")->fetchColumn();
    echo "📍 Database: $db\n";
    
    // Schema check
    $cols = $pdo->query("DESCRIBE pwd_records")->fetchAll(PDO::FETCH_ASSOC);
    $colNames = array_column($cols, 'Field');
    
    echo "pwd_records columns (" . count($cols) . " total):\n";
    foreach ($colNames as $col) {
        $status = in_array($col, ['assistive_device', 'guardian_name', 'skills', 'trainings', 'education_elementary']) ? '✅' : '  ';
        echo "  $status $col\n";
    }
    
    // Test query (should work now)
    $count = $pdo->query("SELECT COUNT(*) FROM pwd_records")->fetchColumn();
    echo "\n📊 Records count: $count\n";
    
    // Test insert (safe)
    $stmt = $pdo->prepare("INSERT INTO homepage_stats (stat_key, stat_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE stat_value = stat_value");
    $stmt->execute(['test', 1]);
    echo "✅ INSERT/UPDATE test OK\n";
    
    echo "\n🎉 <strong>ALL TESTS PASSED! Error fixed.</strong>";
    echo "\n💡 Test your app: http://localhost/HTTML/web/bbpwdo-system/admin/records.php";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage();
}
echo "</pre>";
?>
</xai:function_call}





<xai:function_call name="create_file">
<parameter name="absolute_path">c:/xampp/htdocs/HTTML/web/bbpwdo-system/TODO.md
