<?php
/**
 * ONE-TIME SETUP: Run this once via browser to fix Railway DB
 * http://localhost/HTTML/web/bbpwdo-system/backend/full-setup.php
 */
require_once 'db.php';

echo "<h2>🔧 BBPWDO Database Setup for Railway</h2>";
echo "<pre>";

// Test connection first
try {
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $row = $stmt->fetch();
    echo "✅ Connected to: " . $row['db'] . "\n";
    
    // Ensure default admin (admin/admin123)
    $adminPass = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password) VALUES ('admin', ?)");
    $stmt->execute([$adminPass]);
    echo "✅ Admin user 'admin' / 'admin123' ready\n";
    
    // Default stats
    $stats = [
        ['registered_pwd', 0, 'Registered PWDs', 'fa-users', 1],
        ['programs', 50, 'Programs This Year', 'fa-calendar-check', 2],
        ['partners', 25, 'Partner Organizations', 'fa-hand-holding-heart', 3],
        ['success_stories', 100, 'Success Stories', 'fa-award', 4]
    ];
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($stats as $s) {
        $stmt->execute($s);
    }
    echo "✅ Homepage stats initialized\n";
    
    // Verify pwd_records columns (key fix)
    $cols = $pdo->query("DESCRIBE pwd_records")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('assistive_device', $cols)) {
        echo "✅ pwd_records has 'assistive_device' column ✓\n";
    } else {
        echo "❌ Missing assistive_device - check logs\n";
    }
    
    echo "\n🎉 <strong>SUCCESS! DB ready for Render/GitHub.</strong>\n";
    echo "Login: admin/admin123\n";
    echo "Delete this file after running once.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>

