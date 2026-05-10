<?php
/**
 * FIXED MySQL SETUP - Idempotent, handles duplicates
 * Run again: http://localhost/HTTML/web/bbpwdo-system/backend/setup-mysql.php
 */

require_once 'db.php'; // Uses includes/db.php

echo "<h2>🔧 MySQL Database Setup (bbpwdo) - FIXED</h2>";
echo "<pre>";

// Connect & verify
try {
    $stmt = $pdo->query("SELECT DATABASE() as db");
    $row = $stmt->fetch();
    echo "✅ Connected: " . $row['db'] . "\n";
    
    // Verify tables exist
    $tables = ['users', 'pwd_records', 'family_members', 'homepage_stats'];
    foreach ($tables as $table) {
        $exists = $pdo->query("SHOW TABLES LIKE '$table'")->rowCount() > 0;
        echo $exists ? "✅ Table: $table" : "❌ Missing: $table";
        echo "\n";
    }
    
    // Fix admin safely (avoid duplicate)
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute(['admin']);
    if (!$stmt->fetch()) {
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute(['admin', $adminHash]);
        echo "✅ Created admin/admin123\n";
    } else {
        echo "✅ Admin exists\n";
    }
    
    // Safe stats update
    $stats = [
        ['registered_pwd', 0, 'Registered PWDs', 'fa-users', 1],
        ['programs', 50, 'Programs This Year', 'fa-calendar-check', 2],
        ['partners', 25, 'Partner Organizations', 'fa-hand-holding-heart', 3],
        ['success_stories', 100, 'Success Stories', 'fa-award', 4]
    ];
    
    $stmt = $pdo->prepare("INSERT INTO homepage_stats (stat_key, stat_value, stat_label, stat_icon, sort_order) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE stat_value = VALUES(stat_value)");
    foreach ($stats as $s) {
        $stmt->execute($s);
    }
    echo "✅ Stats updated\n";
    
    // Final test
    $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo "\n📊 Users table: $count records\n";
    
    echo "🎉 DB FULLY READY!\n";
    echo "✅ Login: http://localhost/HTTML/web/bbpwdo-system/admin/login.php (admin/admin123)\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
echo "</pre>";
?>

