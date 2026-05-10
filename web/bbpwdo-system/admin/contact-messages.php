<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../backend/db.php';

$stmt = $pdo->query("SELECT * FROM contact_messages ORDER BY id DESC");
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - BBPWDO Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { animation: none !important; opacity: 1 !important; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f5f7fa; }
        
        .sidebar-fixed {
            position: fixed; left: 0; top: 0; bottom: 0;
            width: 260px; background: linear-gradient(180deg, #1e1b4b 0%, #312e81 100%);
            padding: 25px 20px; z-index: 100;
        }
        .sidebar-logo {
            display: flex; align-items: center; gap: 12px;
            color: white; font-size: 1.4rem; font-weight: 700;
            margin-bottom: 40px; padding: 0 10px;
        }
        .sidebar-logo i { font-size: 1.8rem; }
        .sidebar-menu { list-style: none; }
        .sidebar-menu li { margin-bottom: 8px; }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 14px;
            padding: 14px 16px; color: rgba(255,255,255,0.7);
            text-decoration: none; border-radius: 12px; font-weight: 500;
            transition: all 0.3s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.15); color: white;
        }
        
        .main-content { margin-left: 260px; padding: 30px; }
        
        .top-header { margin-bottom: 30px; }
        .top-header h1 { font-size: 1.8rem; color: #1e1b4b; font-weight: 700; }
        
        .messages-grid { display: grid; gap: 20px; }
        
        .message-card {
            background: white; padding: 24px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border-left: 4px solid #4f46e5;
        }
        .message-card.unread { border-left-color: #10b981; }
        
        .message-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 15px;
        }
        .message-header h3 { font-size: 1.1rem; color: #1e1b4b; }
        .message-header span { font-size: 0.8rem; color: #6b7280; }
        
        .message-info { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-bottom: 15px; }
        .message-info div { font-size: 0.9rem; }
        .message-info strong { color: #374151; }
        
        .message-content {
            background: #f9fafb; padding: 15px; border-radius: 10px;
            font-size: 0.95rem; color: #374151; line-height: 1.6;
        }
        
        .empty-state {
            text-align: center; padding: 60px; color: #6b7280;
        }
        .empty-state i { font-size: 3rem; margin-bottom: 20px; }

        .mobile-menu-btn {
            display: none;
            position: fixed; top: 15px; left: 15px; z-index: 200;
            width: 45px; height: 45px; background: #4f46e5; border: none;
            border-radius: 10px; color: white; font-size: 1.3rem; cursor: pointer;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 999;
        }
        .sidebar-overlay.active { display: block; }

        @media (max-width: 768px) {
            .mobile-menu-btn { display: flex; align-items: center; justify-content: center; }
            .sidebar-fixed {
                position: fixed; left: -260px; top: 0; bottom: 0;
                width: 260px; z-index: 1000;
                transition: left 0.3s ease;
            }
            .sidebar-fixed.active { left: 0; }
            .sidebar-logo span, .sidebar-menu a span { display: inline; }
            .sidebar-menu a { justify-content: flex-start; }
            .main-content { margin-left: 0; padding: 70px 15px 20px 15px; width: 100%; }
            .message-info { grid-template-columns: 1fr; }
        }
        
        .theme-toggle {
            width: 40px; height: 40px; border-radius: 10px; border: none;
            background: white; cursor: pointer; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .theme-toggle i { font-size: 1.2rem; color: #1e1b4b; }
        
        body.dark { background: #0f172a; }
        body.dark .sidebar-fixed { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        body.dark .sidebar-logo { color: #f1f5f9; }
        body.dark .sidebar-menu a { color: #94a3b8; }
        body.dark .sidebar-menu a:hover, body.dark .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; }
        body.dark .top-header h1 { color: #f1f5f9; }
        body.dark .message-card { background: #1e293b; }
        body.dark .message-card h3 { color: #f1f5f9; }
        body.dark .message-card span { color: #94a3b8; }
        body.dark .message-info div strong { color: #94a3b8; }
        body.dark .message-content { background: #0f172a; color: #e2e8f0; }
        body.dark .empty-state { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <button class="mobile-menu-btn" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
    
    <aside class="sidebar-fixed" id="sidebar">
        <div class="sidebar-logo">
            <i class="fa-solid fa-universal-access"></i>
            <span>BBPWDO</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-grip-lines"></i> <span>Dashboard</span></a></li>
            <li><a href="records.php"><i class="fa-solid fa-users"></i> <span>PWD Records</span></a></li>
            <li><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Reports</span></a></li>
            <li><a href="settings.php"><i class="fa-solid fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="contact-messages.php" class="active"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a></li>
            <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Contact Messages</h1>
            <button onclick="toggleTheme()" class="theme-toggle" id="themeBtn" title="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
        </header>

        <?php if (empty($messages)): ?>
            <div class="empty-state">
                <i class="fa-solid fa-inbox"></i>
                <p>No messages yet</p>
            </div>
        <?php else: ?>
            <div class="messages-grid">
                <?php foreach ($messages as $msg): ?>
                    <div class="message-card">
                        <div class="message-header">
                            <div>
                                <h3><?php echo htmlspecialchars($msg['subject']); ?></h3>
                                <span><?php echo date('M d, Y h:i A', strtotime($msg['created_at'])); ?></span>
                            </div>
                            <button onclick="deleteMessage(<?php echo $msg['id']; ?>)" style="background: #fee2e2; color: #dc2626; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                        <div class="message-info">
                            <div><strong>Name:</strong> <?php echo htmlspecialchars($msg['name']); ?></div>
                            <div><strong>Email:</strong> <?php echo htmlspecialchars($msg['email']); ?></div>
                            <div><strong>Phone:</strong> <?php echo htmlspecialchars($msg['phone'] ?? '-'); ?></div>
                        </div>
                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

<script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        if (savedTheme === 'dark') {
            document.body.classList.add('dark');
            document.getElementById('themeBtn').innerHTML = '<i class="fa-solid fa-sun"></i>';
        }
        
        function toggleTheme() {
            const body = document.body;
            const btn = document.getElementById('themeBtn');
            body.classList.toggle('dark');
            if (body.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                btn.innerHTML = '<i class="fa-solid fa-sun"></i>';
            } else {
                localStorage.setItem('theme', 'light');
                btn.innerHTML = '<i class="fa-solid fa-moon"></i>';
            }
        }

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar-fixed');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('active');
            if (overlay) overlay.classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar-fixed');
            const overlay = document.getElementById('sidebarOverlay');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                    sidebar.classList.remove('active');
                    if (overlay) overlay.classList.remove('active');
                }
            }
        });
        
        function deleteMessage(id) {
        if (!confirm('Delete this message?')) return;
        
        fetch('../backend/delete-message.php', {
            method: 'POST',
            body: new URLSearchParams({id: id}),
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting message');
            }
        });
    }
    </script>
</body>
</html>