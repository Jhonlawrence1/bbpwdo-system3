<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

require_once '../backend/db.php';

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pwd_records");
$totalPWD = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pwd_records WHERE is_registered = 'Yes'");
$registered = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pwd_records WHERE employment_status = 'Employed'");
$employed = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM pwd_records WHERE disability_type LIKE '%Physical%'");
$physical = $stmt->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - BBPWDO Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        .sidebar-menu a i { width: 20px; text-align: center; }
        
        .main-content { margin-left: 260px; padding: 30px; }
        
        .top-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 30px;
        }
        .top-header h1 { font-size: 1.8rem; color: #1e1b4b; font-weight: 700; }
        .header-actions { display: flex; align-items: center; gap: 20px; }
        .user-profile {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; background: white; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;
        }
        
        .stats-grid {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 30px;
        }
        .stat-card {
            background: white; padding: 24px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            display: flex; align-items: center; gap: 20px;
        }
        .stat-icon {
            width: 60px; height: 60px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem;
        }
        .stat-icon.blue { background: #e0e7ff; color: #4f46e5; }
        .stat-icon.green { background: #d1fae5; color: #059669; }
        .stat-icon.purple { background: #ede9fe; color: #7c3aed; }
        .stat-icon.orange { background: #fef3c7; color: #d97706; }
        .stat-info h3 { font-size: 1.8rem; color: #1e1b4b; font-weight: 700; }
        .stat-info p { color: #6b7280; font-size: 0.9rem; margin-top: 4px; }
        
        .charts-section {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 30px;
        }
        .chart-card {
            background: white; padding: 24px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .chart-card h3 {
            font-size: 1rem; color: #374151; font-weight: 600;
            margin-bottom: 20px;
        }
        
        .table-card {
            background: white; padding: 24px; border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .table-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px;
        }
        .table-header h2 { font-size: 1.2rem; color: #1e1b4b; font-weight: 600; }
        .table-search {
            display: flex; gap: 12px;
        }
        .table-search input {
            padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 10px;
            width: 250px; outline: none;
        }
        .table-search select {
            padding: 10px 16px; border: 1px solid #e5e7eb; border-radius: 10px;
            outline: none;
        }
        
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th {
            padding: 14px 16px; text-align: left; font-weight: 600; color: #6b7280;
            border-bottom: 2px solid #f3f4f6; font-size: 0.85rem; text-transform: uppercase;
        }
        .data-table td {
            padding: 16px; border-bottom: 1px solid #f3f4f6;
        }
        .data-table tr:hover { background: #f9fafb; }
        
        .badge {
            padding: 6px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;
        }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-danger { background: #fee2e2; color: #dc2626; }
        
        .action-btns { display: flex; gap: 8px; }
        .action-btns button {
            width: 32px; height: 32px; border: none; border-radius: 8px;
            cursor: pointer; transition: all 0.3s;
        }
        .btn-view { background: #e0e7ff; color: #4f46e5; }
        .btn-edit { background: #d1fae5; color: #059669; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        .action-btns button:hover { transform: scale(1.1); }
        
        .modal {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white; padding: 30px; border-radius: 16px; width: 90%; max-width: 800px;
            max-height: 90vh; overflow-y: auto;
        }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .modal-header h2 { font-size: 1.3rem; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; }
        
        .form-section { margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #e5e7eb; }
        .form-section:last-child { border-bottom: none; }
        .form-section h3 { font-size: 1rem; color: #4f46e5; margin-bottom: 15px; }
        .form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.85rem; font-weight: 500; color: #374151; }
        .form-group input, .form-group select, .form-group textarea {
            padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 8px;
        }
        .modal-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px; }
        
        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
            .charts-section { grid-template-columns: 1fr; }
        }
        
        .theme-toggle {
            width: 40px; height: 40px; border-radius: 10px; border: none;
            background: white; cursor: pointer; display: flex; align-items: center; justify-content: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); transition: all 0.3s;
        }
        .theme-toggle:hover { transform: scale(1.1); }
        .theme-toggle i { font-size: 1.2rem; color: #1e1b4b; }
        
        .mobile-menu-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 200;
            width: 45px;
            height: 45px;
            background: #4f46e5;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 1.3rem;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.4);
        }
        
        .sidebar-overlay {
            display: none;
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 999;
        }
        .sidebar-overlay.active { display: block; }
        
        body.dark .mobile-menu-btn { background: #4f46e5; }
        
        body.dark { background: #0f172a; }
        body.dark .sidebar-fixed { background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%); }
        body.dark .sidebar-logo { color: #f1f5f9; }
        body.dark .sidebar-menu a { color: #94a3b8; }
        body.dark .sidebar-menu a:hover, body.dark .sidebar-menu a.active { background: rgba(255,255,255,0.1); color: white; }
        body.dark .top-header h1 { color: #f1f5f9; }
        body.dark .stat-card, body.dark .chart-card, body.dark .table-card, body.dark .settings-card, body.dark .report-card, body.dark .summary-card, body.dark .message-card, body.dark .records-table { background: #1e293b; }
        body.dark .stat-info h3 { color: #f1f5f9; }
        body.dark .stat-info p, body.dark .summary-card p { color: #94a3b8; }
        body.dark .data-table th { background: #0f172a; color: #94a3b8; }
        body.dark .data-table td { border-bottom: 1px solid #334155; color: #e2e8f0; }
        body.dark .data-table tr:hover { background: #0f172a; }
        body.dark input, body.dark select, body.dark textarea { background: #0f172a; border-color: #334155; color: #e2e8f0; }
        body.dark .filters-bar { background: #1e293b; }
        body.dark .filters-bar label { color: #94a3b8; }
        body.dark .badge-success { background: #064e3b; color: #6ee7b7; }
        body.dark .badge-warning { background: #78350f; color: #fcd34d; }
        body.dark .badge-male { background: #312e81; color: #a5b4fc; }
        body.dark .badge-female { background: #831843; color: #f9a8d4; }
        body.dark .modal-content { background: #1e293b; }
        body.dark .modal-header h2 { color: #f1f5f9; }
        body.dark .btn-secondary { background: #334155; color: #e2e8f0; }
        body.dark .btn-delete { background: #7f1d1d; color: #fca5a5; }
        body.dark .btn-edit { background: #065f46; color: #6ee7b7; }
        body.dark .btn-view { background: #312e81; color: #a5b4fc; }
        body.dark .detail-card { background: #0f172a; }
        body.dark .detail-card h4 { color: #94a3b8; }
        body.dark .detail-card p { color: #e2e8f0; }
        body.dark .user-profile { background: #1e293b; color: white; }
        body.dark .chart-card h3 { color: #e2e8f0; }
        body.dark .table-header h2 { color: #f1f5f9; }
        body.dark .pagination button { background: #1e293b; color: #e2e8f0; border-color: #334155; }
        body.dark .pagination button.active { background: #4f46e5; }
        
        @media (max-width: 768px) {
            .sidebar-fixed {
                position: fixed; left: -260px; top: 0; bottom: 0;
                width: 260px; z-index: 1000;
                transition: left 0.3s ease;
            }
            .sidebar-fixed.active {
                left: 0;
            }
            .sidebar-logo span, .sidebar-menu a span { display: inline; }
            .sidebar-menu a { justify-content: flex-start; padding: 14px 16px; }
            .main-content { margin-left: 0; padding: 70px 15px 20px 15px; }
            .stats-grid { grid-template-columns: 1fr; }
            .table-search { flex-direction: column; width: 100%; }
            .table-search input, .table-search select { width: 100%; }
            .top-header { flex-direction: column; gap: 15px; align-items: flex-start; }
            .top-header h1 { font-size: 1.4rem; }
            .header-actions { width: 100%; justify-content: space-between; }
            .mobile-menu-btn { display: flex; align-items: center; justify-content: center; }
            .user-profile { padding: 8px 12px; }
            .user-profile span { display: none; }
            .data-table { display: block; overflow-x: auto; }
            .form-grid { grid-template-columns: 1fr; }
            .modal-content { width: 95%; padding: 20px; margin: 10px; }
            .charts-section { grid-template-columns: 1fr; }
            .table-header { flex-direction: column; gap: 15px; align-items: flex-start; }
        }
        
        @media (max-width: 480px) {
            .stats-grid { gap: 15px; }
            .stat-card { padding: 16px; flex-direction: column; text-align: center; }
            .stat-icon { width: 50px; height: 50px; font-size: 1.2rem; }
            .stat-info h3 { font-size: 1.4rem; }
            .top-header h1 { font-size: 1.2rem; }
            .user-avatar { width: 35px; height: 35px; font-size: 0.9rem; }
            .action-btns { flex-direction: column; }
            .action-btns button { width: 28px; height: 28px; font-size: 0.8rem; }
        }
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
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-grip-lines"></i> <span>Dashboard</span></a></li>
            <li><a href="records.php"><i class="fa-solid fa-users"></i> <span>PWD Records</span></a></li>
            <li><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Reports</span></a></li>
            <li><a href="contact-messages.php"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a></li>
            <li><a href="settings.php"><i class="fa-solid fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>Dashboard</h1>
            <div class="header-actions">
                <button onclick="toggleTheme()" class="theme-toggle" id="themeBtn" title="Toggle Theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <div class="user-profile">
                    <div class="user-avatar">A</div>
                    <span><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalPWD; ?></h3>
                    <p>Total PWD</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="fa-solid fa-id-card"></i></div>
                <div class="stat-info">
                    <h3><?php echo $registered; ?></h3>
                    <p>Registered</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon purple"><i class="fa-solid fa-briefcase"></i></div>
                <div class="stat-info">
                    <h3><?php echo $employed; ?></h3>
                    <p>Employed</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fa-solid fa-wheelchair"></i></div>
                <div class="stat-info">
                    <h3><?php echo $physical; ?></h3>
                    <p>Physical Disability</p>
                </div>
            </div>
        </div>

        <div class="charts-section">
            <div class="chart-card">
                <h3>Disability Types</h3>
                <canvas id="disabilityChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Employment Status</h3>
                <canvas id="employmentChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Gender Distribution</h3>
                <canvas id="sexChart"></canvas>
            </div>
        </div>

        <div class="table-card">
            <div class="table-header">
                <h2>PWD Records</h2>
                <div class="table-search">
                    <input type="text" id="searchInput" placeholder="Search...">
                    <select id="filterStatus">
                        <option value="">All Status</option>
                        <option value="Yes">Registered</option>
                        <option value="No">Not Registered</option>
                    </select>
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Disability</th>
                        <th>PWD ID</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="recordsBody">
                    <tr><td colspan="8" style="text-align: center;">Loading...</td></tr>
                </tbody>
            </table>
            <div id="pagination" style="margin-top: 20px; display: flex; gap: 8px;"></div>
        </div>
    </main>

    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>PWD Details</h2>
                <button class="modal-close" onclick="closeViewModal()">&times;</button>
            </div>
            <div id="viewContent"></div>
        </div>
    </div>

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
        
        let currentPage = 1;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadRecords();
            initCharts();
            document.getElementById('searchInput').addEventListener('input', debounce(loadRecords, 500));
            document.getElementById('filterStatus').addEventListener('change', loadRecords);
        });
        
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        
        function loadRecords() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('filterStatus').value;
            
            fetch(`../backend/fetch.php?search=${encodeURIComponent(search)}&status=${status}&page=${currentPage}&skip_auth=1`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        renderTable(data.data);
                    } else {
                        document.getElementById('recordsBody').innerHTML = '<tr><td colspan="8" style="text-align: center;">Error loading data</td></tr>';
                    }
                });
        }
        
        function renderTable(records) {
            const tbody = document.getElementById('recordsBody');
            if (!records || records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align: center;">No records found</td></tr>';
                return;
            }
            
            tbody.innerHTML = records.map(r => `
                <tr>
                    <td>${r.id}</td>
                    <td>${r.last_name}, ${r.first_name}</td>
                    <td>${r.age}</td>
                    <td>${r.sex}</td>
                    <td>${r.disability_type || '-'}</td>
                    <td>${r.pwd_id_number || '-'}</td>
                    <td><span class="badge ${r.is_registered === 'Yes' ? 'badge-success' : 'badge-warning'}">${r.is_registered}</span></td>
                    <td class="action-btns">
                        <button class="btn-view" onclick="viewRecord(${r.id})"><i class="fa-solid fa-eye"></i></button>
                        <button class="btn-edit"><i class="fa-solid fa-edit"></i></button>
                        <button class="btn-delete" onclick="deleteRecord(${r.id})"><i class="fa-solid fa-trash"></i></button>
                    </td>
                </tr>
            `).join('');
        }
        
        function viewRecord(id) {
            fetch(`../backend/fetch.php?view=${id}&skip_auth=1`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.data.length > 0) {
                        const r = data.data[0];
                        document.getElementById('viewContent').innerHTML = `
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 30px;">
                                <div>
                                    <h3 style="color: #4f46e5; margin-bottom: 15px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;">Personal Information</h3>
                                    <div class="detail-grid">
                                        <div class="detail-card"><h4>Full Name</h4><p>${r.last_name}, ${r.first_name} ${r.middle_name || ''} ${r.suffix || ''}</p></div>
                                        <div class="detail-card"><h4>Age</h4><p>${r.age}</p></div>
                                        <div class="detail-card"><h4>Sex</h4><p>${r.sex || '-'}</p></div>
                                        <div class="detail-card"><h4>Birthdate</h4><p>${r.birthdate || '-'}</p></div>
                                        <div class="detail-card"><h4>Blood Type</h4><p>${r.blood_type || '-'}</p></div>
                                        <div class="detail-card"><h4>Civil Status</h4><p>${r.civil_status || '-'}</p></div>
                                        <div class="detail-card"><h4>Contact Number</h4><p>${r.contact_number || '-'}</p></div>
                                        <div class="detail-card"><h4>Address</h4><p>${r.address || '-'}</p></div>
                                    </div>
                                </div>
                                <div>
                                    <h3 style="color: #4f46e5; margin-bottom: 15px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;">PWD Information</h3>
                                    <div class="detail-grid">
                                        <div class="detail-card"><h4>PWD ID Number</h4><p>${r.pwd_id_number || '-'}</p></div>
                                        <div class="detail-card"><h4>Date Issued</h4><p>${r.issued_date || '-'}</p></div>
                                        <div class="detail-card"><h4>Expiry Date</h4><p>${r.expiry_date || '-'}</p></div>
                                        <div class="detail-card"><h4>Registered</h4><p>${r.is_registered}</p></div>
                                    </div>
                                </div>
                                <div>
                                    <h3 style="color: #4f46e5; margin-bottom: 15px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;">Employment & Education</h3>
                                    <div class="detail-grid">
                                        <div class="detail-card"><h4>Employment Status</h4><p>${r.employment_status || '-'}</p></div>
                                        <div class="detail-card"><h4>Employment Type</h4><p>${r.employment_type || '-'}</p></div>
                                        <div class="detail-card"><h4>Elementary</h4><p>${r.education_elementary || '-'}</p></div>
                                        <div class="detail-card"><h4>High School</h4><p>${r.education_highschool || '-'}</p></div>
                                        <div class="detail-card"><h4>College</h4><p>${r.education_college || '-'}</p></div>
                                        <div class="detail-card"><h4>Vocational</h4><p>${r.education_vocational || '-'}</p></div>
                                    </div>
                                </div>
                                <div>
                                    <h3 style="color: #4f46e5; margin-bottom: 15px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;">Disability Information</h3>
                                    <div class="detail-grid">
                                        <div class="detail-card"><h4>Disability Type</h4><p>${r.disability_type || '-'}</p></div>
                                        <div class="detail-card"><h4>Assistive Device</h4><p>${r.assistive_device || '-'}</p></div>
                                    </div>
                                </div>
                                <div>
                                    <h3 style="color: #4f46e5; margin-bottom: 15px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;">Skills & Trainings</h3>
                                    <div class="detail-grid">
                                        <div class="detail-card"><h4>Skills</h4><p>${r.skills || '-'}</p></div>
                                        <div class="detail-card"><h4>Trainings</h4><p>${r.trainings || '-'}</p></div>
                                    </div>
                                </div>
                                ${r.family_members && r.family_members.length > 0 ? `
                                <div style="grid-column: 1 / -1;">
                                    <h3 style="color: #4f46e5; margin-bottom: 15px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px;">Family Members</h3>
                                    <table class="family-table" style="width: 100%;">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Age</th>
                                                <th>Civil Status</th>
                                                <th>Relationship</th>
                                                <th>Occupation</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${(r.family_members || []).map(m => `
                                                <tr>
                                                    <td>${m.name || '-'}</td>
                                                    <td>${m.age || '-'}</td>
                                                    <td>${m.civil_status || '-'}</td>
                                                    <td>${m.relationship || '-'}</td>
                                                    <td>${m.occupation || '-'}</td>
                                                </tr>
                                            `).join('')}
                                        </tbody>
                                    </table>
                                </div>
                                ` : ''}
                            </div>
                        `;
                        document.getElementById('viewModal').classList.add('active');
                    }
                });
        }
        
        function closeViewModal() {
            document.getElementById('viewModal').classList.remove('active');
        }
        
        function deleteRecord(id) {
            if (confirm('Delete this record?')) {
                const formData = new FormData();
                formData.append('id', id);
                fetch('../backend/delete.php?skip_auth=1', { method: 'POST', body: formData, credentials: 'same-origin' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            loadRecords();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
            }
        }
        
        function initCharts() {
            fetch('../backend/stats.php?skip_auth=1', { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stats = data.stats;
                        
                        if (stats.disability && stats.disability.length > 0) {
                            new Chart(document.getElementById('disabilityChart'), {
                                type: 'doughnut',
                                data: {
                                    labels: stats.disability.map(s => s.disability_type || 'N/A'),
                                    datasets: [{ data: stats.disability.map(s => s.count), backgroundColor: ['#4f46e5', '#7c3aed', '#06b6d4', '#10b981', '#f59e0b'] }]
                                }
                            });
                        }
                        
                        if (stats.employment && stats.employment.length > 0) {
                            new Chart(document.getElementById('employmentChart'), {
                                type: 'bar',
                                data: {
                                    labels: stats.employment.map(s => s.employment_status || 'N/A'),
                                    datasets: [{ label: 'Count', data: stats.employment.map(s => s.count), backgroundColor: '#4f46e5' }]
                                }
                            });
                        }
                        
                        if (stats.sex && stats.sex.length > 0) {
                            new Chart(document.getElementById('sexChart'), {
                                type: 'pie',
                                data: {
                                    labels: stats.sex.map(s => s.sex || 'N/A'),
                                    datasets: [{ data: stats.sex.map(s => s.count), backgroundColor: ['#4f46e5', '#ec4899'] }]
                                }
                            });
                        }
                    }
                });
        }
    </script>
</body>
</html>