<?php
require_once 'db.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Management Dashboard</title>
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 260px;
            --bg-body: #f8fafc;
            --bg-card: #ffffff;
            --text-color: #0f172a;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --border-color: #e2e8f0;
        }

        [data-bs-theme="dark"] {
            --bg-body: #090d16;
            --bg-card: #151c2c;
            --text-color: #f8fafc;
            --sidebar-bg: #0b0f19;
            --sidebar-text: #64748b;
            --border-color: #1e293b;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-color);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Sidebar Styling */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--sidebar-bg);
            color: #fff;
            padding-top: 1.5rem;
            z-index: 1000;
            border-right: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            margin: 0.2rem 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .sidebar .nav-link.active, 
        .sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        /* Modern Card Styling */
        .card-custom {
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
            background: var(--bg-card);
            color: var(--text-color);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .stat-card {
            border-left: 5px solid transparent;
        }
        .stat-card.stat-primary { border-left-color: #0d6efd; }
        .stat-card.stat-info { border-left-color: #0dcaf0; }
        .stat-card.stat-success { border-left-color: #198754; }
        .stat-card.stat-warning { border-left-color: #ffc107; }

        .theme-toggle-btn {
            border-radius: 50px;
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        /* Table Aesthetics */
        .table-hover tbody tr:hover {
            background-color: rgba(0, 0, 0, 0.015);
        }
        [data-bs-theme="dark"] .table-hover tbody tr:hover {
            background-color: rgba(255, 255, 255, 0.02);
        }

        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content { margin-left: 0; padding: 1.25rem; }
        }
    </style>
</head>
<body>

    <!-- Notification Toast Container -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1100;">
        <div id="liveToast" class="toast align-items-center text-white bg-success border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">Action completed successfully.</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <!-- Sidebar Navigation -->
    <aside class="sidebar d-flex flex-column">
        <div class="px-4 mb-4">
            <h4 class="fw-bold text-white mb-1"><i class="bi bi-wallet2 text-primary me-2"></i>Tracker Admin</h4>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill">
                Role: <?= strtoupper(htmlspecialchars($_SESSION['role'])) ?>
            </span>
        </div>
        <ul class="nav nav-pills flex-column mb-auto" id="filterTabs">
            <li class="nav-item"><a class="nav-link active" data-period="all"><i class="bi bi-grid-1x2 me-2"></i> All Entries</a></li>
            <li class="nav-item"><a class="nav-link" data-period="daily"><i class="bi bi-calendar-day me-2"></i> Daily Check</a></li>
            <li class="nav-item"><a class="nav-link" data-period="weekly"><i class="bi bi-calendar-week me-2"></i> Weekly Check</a></li>
            <li class="nav-item"><a class="nav-link" data-period="monthly"><i class="bi bi-calendar-month me-2"></i> Monthly Check</a></li>
            <li class="nav-item"><a class="nav-link" data-period="yearly"><i class="bi bi-calendar3 me-2"></i> Yearly Check</a></li>
        </ul>
        <div class="p-3 border-top border-secondary border-opacity-25">
            <a href="logout.php" class="btn btn-outline-danger w-100 btn-sm rounded-3"><i class="bi bi-box-arrow-right me-1"></i> Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <main class="main-content">
        <!-- Header -->
        <header class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
                <h3 class="fw-bold mb-0">Income Control Panel</h3>
                <p class="text-muted small mb-0">Logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
            </div>
            
            <div>
                <button id="themeToggleBtn" class="btn btn-outline-secondary theme-toggle-btn d-flex align-items-center gap-2">
                    <i id="themeIcon" class="bi bi-moon-stars-fill"></i>
                    <span id="themeLabel">Dark Mode</span>
                </button>
            </div>
        </header>

        <!-- KPI Summary Cards -->
        <section class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card card-custom p-3 stat-card stat-primary">
                    <span class="text-uppercase small fw-semibold text-muted">Today's Total</span>
                    <h3 class="fw-bold mb-0 mt-1 text-primary" id="statToday">$0.00</h3>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-custom p-3 stat-card stat-info">
                    <span class="text-uppercase small fw-semibold text-muted">This Week</span>
                    <h3 class="fw-bold mb-0 mt-1 text-info" id="statWeek">$0.00</h3>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-custom p-3 stat-card stat-success">
                    <span class="text-uppercase small fw-semibold text-muted">This Month</span>
                    <h3 class="fw-bold mb-0 mt-1 text-success" id="statMonth">$0.00</h3>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card card-custom p-3 stat-card stat-warning">
                    <span class="text-uppercase small fw-semibold text-muted">This Year</span>
                    <h3 class="fw-bold mb-0 mt-1 text-warning" id="statYear">$0.00</h3>
                </div>
            </div>
        </section>

        <!-- Form and Table Grid -->
        <section class="row g-4">
            <!-- Entry Form -->
            <div class="col-lg-4">
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-success me-2"></i>Add Income Record</h5>
                    <form id="addForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Title / Source *</label>
                            <input type="text" id="addTitle" class="form-control" placeholder="e.g. Client Payment" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount ($) *</label>
                            <input type="number" step="0.01" id="addAmount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category *</label>
                            <select id="addCategory" class="form-select" required>
                                <option value="Salary">Salary</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Business">Business</option>
                                <option value="Investments">Investments</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Date *</label>
                            <input type="date" id="addDate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Notes</label>
                            <textarea id="addNotes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success w-100 fw-bold py-2">
                            <i class="bi bi-check-lg me-1"></i> Save Entry
                        </button>
                    </form>
                </div>
            </div>

            <!-- Entries Table Panel -->
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0">Recorded Entries</h5>
                            <span class="small text-muted">Filtered Total: </span>
                            <strong class="text-success fs-5" id="filteredTotal">$0.00</strong>
                        </div>
                        <!-- Client-side Quick Search Filter -->
                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" id="searchInput" class="form-control" placeholder="Search entries...">
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Amount</th>
                                    <th>User</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="entriesTable">
                                <tr><td colspan="6" class="text-center text-muted py-4"><div class="spinner-border spinner-border-sm me-2"></div>Loading entries...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Admin Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Entry</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm">
                    <div class="modal-body py-0">
                        <input type="hidden" id="editId">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Title</label>
                            <input type="text" id="editTitle" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount ($)</label>
                            <input type="number" step="0.01" id="editAmount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Category</label>
                            <select id="editCategory" class="form-select" required>
                                <option value="Salary">Salary</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Business">Business</option>
                                <option value="Investments">Investments</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Date</label>
                            <input type="date" id="editDate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Notes</label>
                            <textarea id="editNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light btn-sm rounded-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold rounded-2">Update Entry</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentPeriod = 'all';
        let userRole = '<?= $_SESSION['role'] ?>';
        let cachedEntries = [];
        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const toastElement = new bootstrap.Toast(document.getElementById('liveToast'));

        document.getElementById('addDate').value = new Date().toISOString().split('T')[0];

        // --- HELPER FUNCTION: HTML ESCAPING ---
        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        // --- TOAST TRIGGER ---
        function showNotification(message, isSuccess = true) {
            const toastNode = document.getElementById('liveToast');
            toastNode.className = `toast align-items-center text-white border-0 shadow bg-${isSuccess ? 'success' : 'danger'}`;
            document.getElementById('toastMessage').textContent = message;
            toastElement.show();
        }

        // --- DARK / LIGHT THEME TOGGLE ---
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        const themeLabel = document.getElementById('themeLabel');
        const htmlElement = document.documentElement;

        function applyTheme(theme) {
            htmlElement.setAttribute('data-bs-theme', theme);
            localStorage.setItem('theme', theme);

            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun-fill text-warning';
                themeLabel.textContent = 'Light Mode';
                themeToggleBtn.classList.replace('btn-outline-secondary', 'btn-outline-warning');
            } else {
                themeIcon.className = 'bi bi-moon-stars-fill text-dark';
                themeLabel.textContent = 'Dark Mode';
                themeToggleBtn.classList.replace('btn-outline-warning', 'btn-outline-secondary');
            }
        }

        const savedTheme = localStorage.getItem('theme') || 'light';
        applyTheme(savedTheme);

        themeToggleBtn.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-bs-theme');
            applyTheme(currentTheme === 'dark' ? 'light' : 'dark');
        });

        // --- DATA RENDER & FETCH ---
        const formatCurrency = (val) => '$' + Number(val).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function renderTable(entries) {
            const tbody = document.getElementById('entriesTable');
            if (entries.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No records found.</td></tr>';
                return;
            }

            tbody.innerHTML = entries.map(row => `
                <tr>
                    <td class="small fw-semibold">${escapeHtml(row.entry_date)}</td>
                    <td class="fw-bold">${escapeHtml(row.title)}</td>
                    <td><span class="badge bg-secondary-subtle text-body border">${escapeHtml(row.category)}</span></td>
                    <td class="text-success fw-bold">+${formatCurrency(row.amount)}</td>
                    <td class="small text-muted">${escapeHtml(row.username)}</td>
                    <td class="text-center">
                        ${userRole === 'admin' ? `
                            <button onclick="openEditModal(${row.id})" class="btn btn-sm btn-outline-primary border-0 me-1" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button onclick="deleteRecord(${row.id})" class="btn btn-sm btn-outline-danger border-0" title="Delete">
                                <i class="bi bi-trash"></i>
                            </button>
                        ` : '<span class="text-muted small">View Only</span>'}
                    </td>
                </tr>
            `).join('');
        }

        async function fetchEntries() {
            try {
                const res = await fetch(`api.php?period=${currentPeriod}`);
                const data = await res.json();

                if (data.success) {
                    document.getElementById('statToday').textContent = formatCurrency(data.stats.today);
                    document.getElementById('statWeek').textContent = formatCurrency(data.stats.week);
                    document.getElementById('statMonth').textContent = formatCurrency(data.stats.month);
                    document.getElementById('statYear').textContent = formatCurrency(data.stats.year);
                    document.getElementById('filteredTotal').textContent = formatCurrency(data.filtered_total);

                    cachedEntries = data.entries;
                    renderTable(cachedEntries);
                }
            } catch (err) {
                console.error("Error fetching data:", err);
            }
        }

        // Live Search Filter Handler
        document.getElementById('searchInput').addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            const filtered = cachedEntries.filter(row => 
                row.title.toLowerCase().includes(query) || 
                row.category.toLowerCase().includes(query) ||
                row.username.toLowerCase().includes(query)
            );
            renderTable(filtered);
        });

        // Add Record Handler
        document.getElementById('addForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                title: document.getElementById('addTitle').value,
                amount: document.getElementById('addAmount').value,
                category: document.getElementById('addCategory').value,
                entry_date: document.getElementById('addDate').value,
                notes: document.getElementById('addNotes').value
            };

            const res = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                document.getElementById('addTitle').value = '';
                document.getElementById('addAmount').value = '';
                document.getElementById('addNotes').value = '';
                showNotification('Income entry recorded successfully.');
                fetchEntries();
            } else {
                showNotification('Failed to create record.', false);
            }
        });

        // Modal Form Pre-fill
        async function openEditModal(id) {
            const res = await fetch(`api.php?id=${id}`);
            const data = await res.json();
            if (data.success) {
                const entry = data.entry;
                document.getElementById('editId').value = entry.id;
                document.getElementById('editTitle').value = entry.title;
                document.getElementById('editAmount').value = entry.amount;
                document.getElementById('editCategory').value = entry.category;
                document.getElementById('editDate').value = entry.entry_date;
                document.getElementById('editNotes').value = entry.notes || '';
                editModal.show();
            }
        }

        // Edit Save Handler
        document.getElementById('editForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                id: document.getElementById('editId').value,
                title: document.getElementById('editTitle').value,
                amount: document.getElementById('editAmount').value,
                category: document.getElementById('editCategory').value,
                entry_date: document.getElementById('editDate').value,
                notes: document.getElementById('editNotes').value
            };

            const res = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                editModal.hide();
                showNotification('Record updated successfully.');
                fetchEntries();
            } else {
                showNotification('Permission denied or failed to update record.', false);
            }
        });

        // Delete Handler
        async function deleteRecord(id) {
            if (confirm('Delete this record permanently?')) {
                const res = await fetch(`api.php?id=${id}`, { method: 'DELETE' });
                if (res.ok) {
                    showNotification('Record deleted.');
                    fetchEntries();
                } else {
                    showNotification('Failed to delete record.', false);
                }
            }
        }

        // Tab Filter Switcher
        document.querySelectorAll('#filterTabs .nav-link').forEach(tab => {
            tab.addEventListener('click', (e) => {
                document.querySelectorAll('#filterTabs .nav-link').forEach(t => t.classList.remove('active'));
                e.currentTarget.classList.add('active');
                currentPeriod = e.currentTarget.dataset.period;
                fetchEntries();
            });
        });

        fetchEntries();
    </script>
</body>
</html>