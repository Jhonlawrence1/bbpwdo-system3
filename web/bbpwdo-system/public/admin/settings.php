<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../backend/db.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $user = $stmt->fetch();
        
        if (!password_verify($current, $user['password'])) {
            $message = '<div class="alert alert-danger">Current password is incorrect</div>';
        } elseif ($new !== $confirm) {
            $message = '<div class="alert alert-danger">New passwords do not match</div>';
        } elseif (strlen($new) < 6) {
            $message = '<div class="alert alert-danger">Password must be at least 6 characters</div>';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['admin_id']]);
            $message = '<div class="alert alert-success">Password changed successfully!</div>';
        }
    }
    
    if (isset($_POST['update_profile'])) {
        $username = trim($_POST['username']);
        if (!empty($username)) {
            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$username, $_SESSION['admin_id']]);
            $_SESSION['admin_username'] = $username;
            $message = '<div class="alert alert-success">Profile updated successfully!</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - BBPWDO Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
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
        
        .top-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }
        .top-header h1 { font-size: 1.8rem; color: #1e1b4b; font-weight: 700; }
        
        .settings-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 24px;
        }
        
        .settings-card {
            background: white; padding: 30px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .settings-card h2 {
            font-size: 1.2rem; color: #1e1b4b; margin-bottom: 20px;
            padding-bottom: 15px; border-bottom: 1px solid #e5e7eb;
        }
        
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block; font-size: 0.9rem; font-weight: 500; color: #374151;
            margin-bottom: 8px;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%; padding: 12px 14px; border: 1px solid #e5e7eb;
            border-radius: 10px; font-size: 0.95rem;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none; border-color: #4f46e5;
        }
        
        .btn {
            padding: 12px 24px; border: none; border-radius: 10px; font-weight: 500;
            cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-secondary { background: #f3f4f6; color: #374151; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        
        .alert {
            padding: 14px 18px; border-radius: 10px; margin-bottom: 20px;
        }
        .alert-success { background: #d1fae5; color: #059669; }
        .alert-danger { background: #fee2e2; color: #dc2626; }
        
        .info-box {
            background: #f9fafb; padding: 20px; border-radius: 12px; margin-bottom: 20px;
        }
        .info-box h3 { font-size: 1rem; color: #374151; margin-bottom: 10px; }
        .info-box p { color: #6b7280; font-size: 0.9rem; line-height: 1.6; }
        
        .toggle-group { margin-bottom: 15px; }
        .toggle-group label { display: flex; align-items: center; justify-content: space-between; }
        .toggle-group span { font-weight: 500; }
        .toggle {
            width: 50px; height: 26px; background: #e5e7eb; border-radius: 20px;
            position: relative; cursor: pointer;
        }
        .toggle.active { background: #4f46e5; }
        .toggle::after {
            content: ''; position: absolute; width: 20px; height: 20px;
            background: white; border-radius: 50%; top: 3px; left: 3px;
            transition: 0.3s;
        }
        .toggle.active::after { left: 27px; }
        
        .data-stats {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-top: 20px;
        }
        .data-stat {
            background: #f9fafb; padding: 20px; border-radius: 12px; text-align: center;
        }
        .data-stat h4 { font-size: 1.8rem; color: #4f46e5; }
        .data-stat p { font-size: 0.85rem; color: #6b7280; margin-top: 5px; }
        
@media (max-width: 1024px) {
            .settings-grid { grid-template-columns: 1fr; }
            .settings-card:nth-child(3) { grid-column: span 1; }
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
        body.dark .settings-card { background: #1e293b; }
        body.dark .settings-card h2 { color: #f1f5f9; }
        body.dark .form-group label { color: #94a3b8; }
        body.dark .form-group input, body.dark .form-group select, body.dark .form-group textarea { background: #0f172a; border-color: #334155; color: #e2e8f0; }
        body.dark .btn-primary { background: #4f46e5; }
        body.dark .btn-secondary { background: #334155; color: #e2e8f0; }
        body.dark .alert-success { background: #064e3b; color: #6ee7b7; }
        body.dark .alert-danger { background: #7f1d1d; color: #fca5a5; }
        body.dark .data-stat { background: #0f172a; }
        body.dark .data-stat h4 { color: #f1f5f9; }
        body.dark .data-stat p { color: #94a3b8; }
        body.dark .info-box { background: #0f172a; }
        body.dark .info-box h3 { color: #e2e8f0; }
        body.dark .info-box p { color: #94a3b8; }
        body.dark table { color: #e2e8f0; }
        body.dark #teamCardsTable th { background: #0f172a; color: #94a3b8; }
        body.dark #teamCardsTable td { border-bottom: 1px solid #334155; }
    </style>
</head>
<body>
    <aside class="sidebar-fixed">
        <div class="sidebar-logo">
            <i class="fa-solid fa-universal-access"></i>
            <span>BBPWDO</span>
        </div>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-grip-lines"></i> <span>Dashboard</span></a></li>
            <li><a href="records.php"><i class="fa-solid fa-users"></i> <span>PWD Records</span></a></li>
            <li><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Reports</span></a></li>
            <li><a href="contact-messages.php"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a></li>
            <li><a href="settings.php" class="active"><i class="fa-solid fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Settings</h1>
            <button onclick="toggleTheme()" class="theme-toggle" id="themeBtn" title="Toggle Theme">
                <i class="fa-solid fa-moon"></i>
            </button>
        </header>
        
        <?php echo $message; ?>
        
        <div class="settings-grid">
            <div class="settings-card">
                <h2><i class="fa-solid fa-user"></i> Profile Settings</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($_SESSION['admin_username']); ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </form>
            </div>

            <div class="settings-card">
                <h2><i class="fa-solid fa-lock"></i> Change Password</h2>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password</label>
                        <input type="password" name="new_password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6">
                    </div>
                    <button type="submit" name="change_password" class="btn btn-primary">
                        <i class="fa-solid fa-key"></i> Change Password
                    </button>
                </form>
            </div>

            <div class="settings-card" style="grid-column: span 2;">
                <h2><i class="fa-solid fa-users-gear"></i> Manage Admins</h2>
                <div style="margin-bottom: 20px;">
                    <h4 style="margin-bottom: 10px; color: #374151;">Add New Admin</h4>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" id="newAdminUsername" placeholder="Username" style="flex: 1; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">
                        <input type="password" id="newAdminPassword" placeholder="Password" style="flex: 1; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px;">
                        <button onclick="addAdmin()" class="btn btn-primary">Add</button>
                    </div>
                </div>
                <h4 style="margin-bottom: 10px; color: #374151;">Admin List</h4>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f9fafb;">
                            <th style="padding: 10px; text-align: left;">ID</th>
                            <th style="padding: 10px; text-align: left;">Username</th>
                            <th style="padding: 10px; text-align: left;">Created</th>
                            <th style="padding: 10px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="adminList">
                        <?php
                        $stmt = $pdo->query("SELECT id, username, created_at FROM users ORDER BY id");
                        while ($user = $stmt->fetch()) {
                            $isMe = $user['id'] == $_SESSION['admin_id'];
                            echo '<tr>';
                            echo '<td style="padding: 10px;">' . $user['id'] . '</td>';
                            echo '<td style="padding: 10px;">' . htmlspecialchars($user['username']) . ($isMe ? ' (You)' : '') . '</td>';
                            echo '<td style="padding: 10px;">' . date('M d, Y', strtotime($user['created_at'])) . '</td>';
                            echo '<td style="padding: 10px; text-align: right;">';
                            if (!$isMe) {
                                echo '<button onclick="deleteUser(' . $user['id'] . ')" style="background: #fee2e2; color: #dc2626; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer;">Delete</button>';
                            } else {
                                echo '<span style="color: #6b7280;">-</span>';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="settings-card">
                <h2><i class="fa-solid fa-chart-line"></i> Homepage Stats</h2>
                <p style="color: #6b7280; font-size: 0.85rem; margin-bottom: 15px;">Edit the statistics displayed on the homepage.</p>
                <div id="statsForm">
                    <div style="display: grid; gap: 12px;">
                        <div style="padding: 12px; background: #e0e7ff; border-radius: 8px; border: 1px solid #c7d2fe;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 0.8rem; color: #4f46e5;">Registered PWDs <small>(Auto-calculated from PWD Records)</small></label>
                                <input type="number" id="stat_registered_pwd" readonly style="background: #e0e7ff; cursor: not-allowed; border-color: #c7d2fe;">
                            </div>
                        </div>
                        <div style="padding: 12px; background: #f9fafb; border-radius: 8px;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 0.8rem;">Programs This Year</label>
                                <input type="number" id="stat_programs" value="0">
                            </div>
                        </div>
                        <div style="padding: 12px; background: #f9fafb; border-radius: 8px;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 0.8rem;">Partner Organizations</label>
                                <input type="number" id="stat_partners" value="0">
                            </div>
                        </div>
                        <div style="padding: 12px; background: #f9fafb; border-radius: 8px;">
                            <div class="form-group" style="margin: 0;">
                                <label style="font-size: 0.8rem;">Success Stories</label>
                                <input type="number" id="stat_success_stories" value="0">
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="saveStats()" class="btn btn-primary" style="margin-top: 15px;">
                        <i class="fa-solid fa-save"></i> Save Stats
                    </button>
                </div>
            </div>

            <div class="settings-card">
                <h2><i class="fa-solid fa-database"></i> System Info</h2>
                <div class="data-stats">
                    <div class="data-stat">
                        <h4><?php 
                            $stmt = $pdo->query("SELECT COUNT(*) FROM pwd_records");
                            echo $stmt->fetchColumn();
                        ?></h4>
                        <p>Total PWD</p>
                    </div>
                    <div class="data-stat">
                        <h4><?php 
                            $stmt = $pdo->query("SELECT COUNT(*) FROM pwd_records WHERE is_registered = 'Yes'");
                            echo $stmt->fetchColumn();
                        ?></h4>
                        <p>Registered</p>
                    </div>
                    <div class="data-stat">
                        <h4><?php 
                            $stmt = $pdo->query("SELECT COUNT(*) FROM users");
                            echo $stmt->fetchColumn();
                        ?></h4>
                        <p>Admins</p>
                    </div>
                </div>
                <div class="info-box" style="margin-top: 20px;">
                    <h3>BBPWDO System</h3>
                    <p>Version 1.0.0<br>PHP <?php echo phpversion(); ?><br>MySQL / MariaDB</p>
                </div>
            </div>

            <div class="settings-card" style="grid-column: span 2;">
                <h2><i class="fa-solid fa-users"></i> Team Cards</h2>
                <p style="color: #6b7280; font-size: 0.85rem; margin-bottom: 15px;">Edit the team member cards displayed on the About page.</p>
                <table style="width: 100%; border-collapse: collapse;" id="teamCardsTable">
                    <thead>
                        <tr style="background: #f9fafb;">
                            <th style="padding: 10px; text-align: left;">Position</th>
                            <th style="padding: 10px; text-align: left;">Name</th>
                            <th style="padding: 10px; text-align: left;">Title</th>
                            <th style="padding: 10px; text-align: right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="teamCardsList"></tbody>
                </table>
            </div>
        </div>
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
        
        function addAdmin() {
        const username = document.getElementById('newAdminUsername').value;
        const password = document.getElementById('newAdminPassword').value;
        
        if (!username || !password) {
            alert('Please fill all fields');
            return;
        }
        
        fetch('../backend/manage-users.php', {
            method: 'POST',
            body: new URLSearchParams({action: 'add_user', username: username, password: password}),
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        });
    }
    
    function deleteUser(id) {
        if (!confirm('Delete this admin account?')) return;
        
        fetch('../backend/manage-users.php', {
            method: 'POST',
            body: new URLSearchParams({action: 'delete_user', id: id}),
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                location.reload();
            }
        });
    }
    
    function loadStats() {
        fetch('../backend/stats-api.php', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    data.data.forEach(stat => {
                        const valueInput = document.getElementById('stat_' + stat.stat_key);
                        if (valueInput) valueInput.value = stat.stat_value;
                    });
                }
            });
    }
    
    function saveStats() {
        const stats = [
            { stat_key: 'programs', stat_value: document.getElementById('stat_programs').value },
            { stat_key: 'partners', stat_value: document.getElementById('stat_partners').value },
            { stat_key: 'success_stories', stat_value: document.getElementById('stat_success_stories').value }
        ];
        
        fetch('../backend/stats-api.php?skip_auth=1', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ stats: stats }),
            credentials: 'same-origin'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Stats saved successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(err => {
            alert('Request failed: ' + err.message);
        });
    }
    
    document.addEventListener('DOMContentLoaded', function() {
            loadStats();
            loadTeamCards();
        });
        
        function loadTeamCards() {
            fetch('../backend/team-cards-api.php', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const tbody = document.getElementById('teamCardsList');
                        const posMap = {1: 'Organization Head', 2: 'Secretary', 3: 'Treasurer', 4: 'Program Coordinator'};
                        tbody.innerHTML = data.data.map(card => `
                            <tr>
                                <td style="padding: 10px;">${posMap[card.id] || card.id}</td>
                                <td style="padding: 10px;">${card.name}</td>
                                <td style="padding: 10px;">${card.position}</td>
                                <td style="padding: 10px; text-align: right;">
                                    <button onclick="editTeamCard(${card.id}, '${card.name}', '${card.position}')" style="background: #d1fae5; color: #059669; border: none; padding: 6px 12px; border-radius: 6px; cursor: pointer;">Edit</button>
                                </td>
                            </tr>
                        `).join('');
                    }
                });
        }
        
        function editTeamCard(id, name, position) {
            const newName = prompt('Edit Name:', name);
            if (newName === null) return;
            const newPosition = prompt('Edit Position:', position);
            if (newPosition === null) return;
            
            fetch('../backend/team-cards-api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'update', id: id, name: newName, position: newPosition }),
                credentials: 'same-origin'
            })
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    loadTeamCards();
                }
            });
        }
    </script>
</body>
</html>