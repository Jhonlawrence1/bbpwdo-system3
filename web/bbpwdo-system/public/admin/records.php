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
    <title>PWD Records - BBPWDO Admin</title>
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
            transition: background 0.3s ease;
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
        .header-actions { display: flex; gap: 12px; }
        
        .btn {
            padding: 12px 20px; border: none; border-radius: 10px; font-weight: 500;
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            transition: all 0.3s;
        }
        .btn-primary { background: #4f46e5; color: white; }
        .btn-secondary { background: white; color: #374151; border: 1px solid #e5e7eb; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        
        .filters-bar {
            display: flex; gap: 16px; margin-bottom: 24px; flex-wrap: wrap;
            background: white; padding: 20px; border-radius: 12px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .filter-group { display: flex; flex-direction: column; gap: 6px; }
        .filter-group label { font-size: 0.8rem; font-weight: 500; color: #6b7280; }
        .filter-group input, .filter-group select {
            padding: 10px 14px; border: 1px solid #e5e7eb; border-radius: 8px; min-width: 180px;
        }
        
        .records-table {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        .table-scroll { overflow-x: auto; }
        
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 16px; text-align: left; font-weight: 600; color: #6b7280;
            background: #f9fafb; font-size: 0.8rem; text-transform: uppercase;
            border-bottom: 2px solid #e5e7eb;
        }
        td { padding: 16px; border-bottom: 1px solid #f3f4f6; }
        tr:hover { background: #f9fafb; }
        
        .badge {
            padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 500;
        }
        .badge-success { background: #d1fae5; color: #059669; }
        .badge-warning { background: #fef3c7; color: #d97706; }
        .badge-male { background: #e0e7ff; color: #4f46e5; }
        .badge-female { background: #fce7f3; color: #db2777; }
        
        .action-btns { display: flex; gap: 8px; }
        .action-btns button {
            width: 32px; height: 32px; border: none; border-radius: 8px;
            cursor: pointer; transition: all 0.3s;
        }
        .btn-view { background: #e0e7ff; color: #4f46e5; }
        .btn-edit { background: #d1fae5; color: #059669; }
        .btn-delete { background: #fee2e2; color: #dc2626; }
        
        .pagination {
            display: flex; justify-content: center; gap: 8px; margin-top: 24px;
        }
        .pagination button {
            padding: 8px 14px; border: 1px solid #e5e7eb; background: white;
            border-radius: 8px; cursor: pointer;
        }
        .pagination button.active { background: #4f46e5; color: white; border-color: #4f46e5; }
        
        .modal {
            display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 1000; justify-content: center; align-items: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white; padding: 30px; border-radius: 16px; width: 95%; max-width: 900px;
            max-height: 90vh; overflow-y: auto;
        }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .modal-header h2 { font-size: 1.3rem; color: #1e1b4b; }
        .modal-close { background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #6b7280; }
        
        .detail-grid {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;
        }
        .detail-card {
            background: #f9fafb; padding: 16px; border-radius: 10px;
        }
        .detail-card h4 {
            font-size: 0.75rem; color: #6b7280; text-transform: uppercase;
            margin-bottom: 8px;
        }
        .detail-card p { font-weight: 500; color: #1e1b4b; }
        
@media (max-width: 768px) {
            .sidebar-fixed { width: 70px; }
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
        body.dark .records-table { background: #1e293b; }
        body.dark .data-table th { background: #0f172a; color: #94a3b8; }
        body.dark .data-table td { border-bottom: 1px solid #334155; color: #e2e8f0; }
        body.dark .data-table tr:hover { background: #0f172a; }
        body.dark .filters-bar { background: #1e293b; }
        body.dark .filters-bar label { color: #94a3b8; }
        body.dark input, body.dark select { background: #0f172a; border-color: #334155; color: #e2e8f0; }
        body.dark .badge-success { background: #064e3b; color: #6ee7b7; }
        body.dark .badge-warning { background: #78350f; color: #fcd34d; }
        body.dark .badge-male { background: #312e81; color: #a5b4fc; }
        body.dark .badge-female { background: #831843; color: #f9a8d4; }
        body.dark .btn-secondary { background: #334155; color: #e2e8f0; }
        body.dark .btn-primary { background: #4f46e5; }
        body.dark .modal-content { background: #1e293b; }
        body.dark .modal-header h2 { color: #f1f5f9; }
        body.dark .detail-card { background: #0f172a; }
        body.dark .detail-card h4 { color: #94a3b8; }
        body.dark .detail-card p { color: #e2e8f0; }
        body.dark .form-group label { color: #94a3b8; }
        body.dark .form-group input, body.dark .form-group select { background: #0f172a; border-color: #334155; color: #e2e8f0; }
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
            <li><a href="records.php" class="active"><i class="fa-solid fa-users"></i> <span>PWD Records</span></a></li>
            <li><a href="reports.php"><i class="fa-solid fa-file-pdf"></i> <span>Reports</span></a></li>
            <li><a href="contact-messages.php"><i class="fa-solid fa-envelope"></i> <span>Messages</span></a></li>
            <li><a href="settings.php"><i class="fa-solid fa-cog"></i> <span>Settings</span></a></li>
            <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <h1>PWD Records</h1>
            <div class="header-actions">
                <button onclick="toggleTheme()" class="theme-toggle" id="themeBtn" title="Toggle Theme">
                    <i class="fa-solid fa-moon"></i>
                </button>
                <button class="btn btn-secondary" onclick="exportExcel()">
                    <i class="fa-solid fa-file-excel"></i> Export Excel
                </button>
                <button class="btn btn-primary" onclick="addNew()">
                    <i class="fa-solid fa-plus"></i> Add New
                </button>
            </div>
        </header>

        <div class="filters-bar">
            <div class="filter-group">
                <label>Search</label>
                <input type="text" id="searchInput" placeholder="Name, PWD ID...">
            </div>
            <div class="filter-group">
                <label>Status</label>
                <select id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Yes">Registered</option>
                    <option value="No">Not Registered</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Employment</label>
                <select id="employmentFilter">
                    <option value="">All</option>
                    <option value="Employed">Employed</option>
                    <option value="Unemployed">Unemployed</option>
                    <option value="Self-Employed">Self-Employed</option>
                    <option value="Retired">Retired</option>
                </select>
            </div>
            <div class="filter-group">
                <label>Disability Type</label>
                <select id="disabilityFilter">
                    <option value="">All Types</option>
                    <option value="Physical">Physical</option>
                    <option value="Visual">Visual</option>
                    <option value="Hearing">Hearing</option>
                    <option value="Learning">Learning</option>
                    <option value="Multiple">Multiple</option>
                    <option value="Mental">Mental</option>
                    <option value="Others">Others</option>
                </select>
            </div>
        </div>

        <div class="records-table">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Full Name</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Disability Type</th>
                            <th>PWD ID No.</th>
                            <th>Registered</th>
                            <th>Employment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="recordsBody">
                        <tr><td colspan="9" style="text-align: center;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="pagination" id="pagination"></div>
    </main>

    <div class="modal" id="viewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>PWD Record Details</h2>
                <button class="modal-close" onclick="closeModal('viewModal')">&times;</button>
            </div>
            <div id="viewContent"></div>
        </div>
    </div>

    <div class="modal" id="addModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New PWD Record</h2>
                <button class="modal-close" onclick="closeModal('addModal')">&times;</button>
            </div>
            <form id="addForm">
                <div class="detail-grid">
                    <div class="form-group">
                        <label>Last Name *</label>
                        <input type="text" name="lastName" required>
                    </div>
                    <div class="form-group">
                        <label>First Name *</label>
                        <input type="text" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label>Middle Name</label>
                        <input type="text" name="middleName">
                    </div>
                    <div class="form-group">
                        <label>Sex *</label>
                        <select name="sex" required>
                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Age *</label>
                        <input type="number" name="age" required>
                    </div>
                    <div class="form-group">
                        <label>Birthdate *</label>
                        <input type="date" name="birthdate" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contactNumber">
                    </div>
                    <div class="form-group">
                        <label>Civil Status</label>
                        <select name="civilStatus">
                            <option value="">Select</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>PWD ID Number</label>
                        <input type="text" name="pwdIdNumber">
                    </div>
                </div>
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
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
        
        let currentPage = 1;
        
        document.addEventListener('DOMContentLoaded', function() {
            loadRecords();
            document.getElementById('searchInput').addEventListener('input', debounce(loadRecords, 500));
            document.getElementById('statusFilter').addEventListener('change', loadRecords);
            document.getElementById('employmentFilter').addEventListener('change', loadRecords);
            document.getElementById('disabilityFilter').addEventListener('change', loadRecords);
            document.getElementById('addForm').addEventListener('submit', saveRecord);
        });
        
        function debounce(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
        
        function loadRecords() {
            fetch(`../backend/fetch.php?search=${encodeURIComponent(document.getElementById('searchInput').value)}&status=${document.getElementById('statusFilter').value}&employment=${document.getElementById('employmentFilter').value}&disability=${document.getElementById('disabilityFilter').value}&page=${currentPage}&skip_auth=1`, { credentials: 'same-origin' })
                .then(res => res.json())
                .then(data => {
                    if (data.success) renderTable(data.data);
                    else {
                        console.error('Error:', data.message);
                        document.getElementById('recordsBody').innerHTML = '<tr><td colspan="9" style="text-align: center;">Error loading data: ' + (data.message || 'Unknown error') + '</td></tr>';
                    }
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    document.getElementById('recordsBody').innerHTML = '<tr><td colspan="9" style="text-align: center;">Error loading data</td></tr>';
                });
        }
        
        function renderTable(records) {
            const tbody = document.getElementById('recordsBody');
            if (!records || records.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align: center;">No records found</td></tr>';
                return;
            }
            tbody.innerHTML = records.map(r => `
                <tr>
                    <td>${r.id}</td>
                    <td>${r.last_name}, ${r.first_name}</td>
                    <td>${r.age}</td>
                    <td><span class="badge badge-${r.sex === 'Male' ? 'male' : 'female'}">${r.sex}</span></td>
                    <td>${r.disability_type || '-'}</td>
                    <td>${r.pwd_id_number || '-'}</td>
                    <td><span class="badge ${r.is_registered === 'Yes' ? 'badge-success' : 'badge-warning'}">${r.is_registered}</span></td>
                    <td>${r.employment_status || '-'}</td>
                    <td class="action-btns">
                        <button class="btn-view" onclick="viewRecord(${r.id})"><i class="fa-solid fa-eye"></i></button>
                        <button class="btn-edit" onclick="editRecord(${r.id})"><i class="fa-solid fa-edit"></i></button>
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
        
        function editRecord(id) {
            alert('Edit feature coming soon!');
        }
        
        function deleteRecord(id) {
            if (confirm('Are you sure you want to delete this record?')) {
                const formData = new FormData();
                formData.append('id', id);
                fetch('../backend/delete.php?skip_auth=1', { method: 'POST', body: formData, credentials: 'same-origin' })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) loadRecords();
                        else alert('Error: ' + data.message);
                    });
            }
        }
        
        function addNew() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function saveRecord(e) {
            e.preventDefault();
            const formData = new FormData(document.getElementById('addForm'));
            fetch('../backend/submit.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert('Record saved successfully!');
                        closeModal('addModal');
                        document.getElementById('addForm').reset();
                        loadRecords();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
        
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        
        function exportExcel() {
            const search = document.getElementById('searchInput').value;
            const status = document.getElementById('statusFilter').value;
            const employment = document.getElementById('employmentFilter').value;
            const disability = document.getElementById('disabilityFilter').value;
            window.open(`../backend/export-excel.php?skip_auth=1&search=${search}&status=${status}&employment=${employment}&disability=${disability}`, '_blank');
        }
    </script>
</body>
</html>