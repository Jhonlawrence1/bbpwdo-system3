<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - BBPWDO Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .header-actions { display: flex; gap: 12px; }
        
        .btn {
            padding: 12px 20px; border: none; border-radius: 10px; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-secondary { background: white; color: #374151; border: 1px solid #e5e7eb; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        
        .reports-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 30px;
        }
        .report-card {
            background: white; padding: 24px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .report-card h3 {
            font-size: 1rem; color: #374151; margin-bottom: 20px;
        }
        
        .summary-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px;
        }
        .summary-card {
            background: white; padding: 24px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            text-align: center;
        }
        .summary-card h4 { font-size: 2rem; color: #4f46e5; }
        .summary-card p { font-size: 0.85rem; color: #6b7280; margin-top: 5px; }
        
        .chart-grid {
            display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px;
        }
        .chart-card {
            background: white; padding: 24px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .chart-card h3 {
            font-size: 1rem; color: #374151; margin-bottom: 20px;
        }
        
        .export-section {
            background: white; padding: 30px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05); margin-top: 30px;
        }
        .export-section h2 { font-size: 1.2rem; color: #1e1b4b; margin-bottom: 20px; }
        .export-options {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px;
        }
        .export-option {
            padding: 20px; border: 2px solid #e5e7eb; border-radius: 12px;
            text-align: center; cursor: pointer; transition: all 0.3s;
        }
        .export-option:hover { border-color: #4f46e5; background: #f9fafb; }
        .export-option i { font-size: 2rem; color: #4f46e5; margin-bottom: 10px; }
        .export-option p { font-weight: 500; color: #374151; }
        
        @media (max-width: 1200px) {
            .summary-grid { grid-template-columns: repeat(2, 1fr); }
            .reports-grid { grid-template-columns: 1fr; }
            .chart-grid { grid-template-columns: 1fr; }
            .export-options { grid-template-columns: repeat(2, 1fr); }
        }

        .mobile-menu-btn {
            display: none;
            position: fixed; top: 15px; left: 15px; z-index: 200;
            width: 45px; height: 45px; background: #4f46e5; border: none;
            border-radius: 10px; color: white; font-size: 1.3rem; cursor: pointer;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        }

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
            .summary-grid { grid-template-columns: 1fr; }
            .export-options { grid-template-columns: 1fr; }
        }
        
        .theme-toggle {
            width: 40px; height: 40px; border-radius: 10px; border: none;
            background: white; cursor: pointer; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-right: 10px;
        }
        .theme-toggle i { font-size: 1.2rem; color: #1e1b4b; }
        
        body.dark { background: #0f172a; }
        body.dark .sidebar-fixed { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        body.dark .sidebar-logo { color: #f1f5f9; }
        body.dark .sidebar-menu a { color: #94a3b8; }
        body.dark .sidebar-menu a:hover, body.dark .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; }
        body.dark .top-header h1 { color: #f1f5f9; }
        body.dark .summary-card, body.dark .chart-card, body.dark .export-section { background: #1e293b; }
        body.dark .summary-card h4 { color: #f1f5f9; }
        body.dark .summary-card p { color: #94a3b8; }
        body.dark .chart-card h3 { color: #e2e8f0; }
        body.dark .export-section h2 { color: #f1f5f9; }
        body.dark .export-option { background: #1e293b; border-color: #334155; }
        body.dark .export-option:hover { background: #0f172a; }
        body.dark .export-option p { color: #e2e8f0; }
    </style>
</head>
<body>
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
            <li><a href="reports.php" class="active"><i class="fa-solid fa-file-pdf"></i> <span>Reports</span></a></li>
            <li><a href="contact-messages.php"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a></li>
            <li><a href="settings.php"><i class="fa-solid fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Reports</h1>
            <div class="header-actions">
                <button onclick="toggleTheme()" class="theme-toggle" id="themeBtn" title="Toggle Theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <button class="btn btn-secondary" onclick="window.print()">
                    <i class="fa-solid fa-print"></i> Print
                </button>
            </div>
        </header>

        <div class="summary-grid">
            <?php
            $stmt = $pdo->query("SELECT COUNT(*) FROM pwd_records");
            $total = $stmt->fetchColumn();
            $stmt = $pdo->query("SELECT COUNT(*) FROM pwd_records WHERE is_registered = 'Yes'");
            $registered = $stmt->fetchColumn();
            $stmt = $pdo->query("SELECT COUNT(*) FROM pwd_records WHERE employment_status = 'Employed'");
            $employed = $stmt->fetchColumn();
            $stmt = $pdo->query("SELECT COUNT(*) FROM pwd_records WHERE employment_status = 'Unemployed'");
            $unemployed = $stmt->fetchColumn();
            ?>
            <div class="summary-card">
                <h4><?php echo $total; ?></h4>
                <p>Total PWD</p>
            </div>
            <div class="summary-card">
                <h4><?php echo $registered; ?></h4>
                <p>Registered</p>
            </div>
            <div class="summary-card">
                <h4><?php echo $employed; ?></h4>
                <p>Employed</p>
            </div>
            <div class="summary-card">
                <h4><?php echo $unemployed; ?></h4>
                <p>Unemployed</p>
            </div>
        </div>

        <div class="chart-grid">
            <div class="chart-card">
                <h3>Employment Status Distribution</h3>
                <canvas id="employmentChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Registration Status</h3>
                <canvas id="registeredChart"></canvas>
            </div>
        </div>

        <div class="export-section">
            <h2>Export Reports</h2>
            <div class="export-options">
                <div class="export-option" onclick="exportData('pdf')">
                    <i class="fa-solid fa-file-pdf"></i>
                    <p>PDF Report</p>
                </div>
                <div class="export-option" onclick="exportData('csv')">
                    <i class="fa-solid fa-file-csv"></i>
                    <p>CSV Export</p>
                </div>
                <div class="export-option" onclick="exportData('excel')">
                    <i class="fa-solid fa-file-excel"></i>
                    <p>Excel Export</p>
                </div>
                <div class="export-option" onclick="exportData('print')">
                    <i class="fa-solid fa-print"></i>
                    <p>Print View</p>
                </div>
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

        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar-fixed');
            sidebar.classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            const sidebar = document.querySelector('.sidebar-fixed');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && !menuBtn.contains(e.target)) {
                    sidebar.classList.remove('active');
                }
            }
        });
        
        function exportData(format) {
            if (format === 'pdf') {
                window.open('../backend/export.php?skip_auth=1', '_blank');
            } else if (format === 'csv') {
                window.open('../backend/export.php?format=csv&skip_auth=1', '_blank');
            } else if (format === 'print') {
                window.print();
            } else {
                alert('Coming soon!');
            }
        }
        
        fetch('../backend/stats.php?skip_auth=1', { credentials: 'same-origin' })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const stats = data.stats;
                    
                    new Chart(document.getElementById('employmentChart'), {
                        type: 'bar',
                        data: {
                            labels: stats.employment.map(s => s.employment_status || 'N/A'),
                            datasets: [{
                                label: 'Count',
                                data: stats.employment.map(s => s.count),
                                backgroundColor: '#4f46e5'
                            }]
                        }
                    });
                    
                    new Chart(document.getElementById('registeredChart'), {
                        type: 'doughnut',
                        data: {
                            labels: ['Registered', 'Not Registered'],
                            datasets: [{
                                data: [<?php echo $registered; ?>, <?php echo $total - $registered; ?>],
                                backgroundColor: ['#10b981', '#f59e0b']
                            }]
                        }
                    });
                }
            });
    </script>
</body>
</html>