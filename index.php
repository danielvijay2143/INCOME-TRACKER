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
    <title>Advanced Income & Service Management Dashboard</title>
    <!-- Bootstrap 5.3 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SheetJS (Excel Export) -->
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <!-- html2pdf.js (PDF Export) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

    <style>
        :root {
            --sidebar-width: 260px;
            --bg-body: #f4f6fb;
            --bg-card: #ffffff;
            --text-color: #0f172a;
            --sidebar-bg: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            --sidebar-text: #a5b4fc;
            --border-color: #e2e8f0;
        }

        [data-bs-theme="dark"] {
            --bg-body: #0b0f19;
            --bg-card: #151d2a;
            --text-color: #f8fafc;
            --sidebar-bg: linear-gradient(180deg, #070a12 0%, #0f172a 100%);
            --sidebar-text: #818cf8;
            --border-color: #1e293b;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-color);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

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
            border-radius: 12px;
            margin: 0.3rem 0.8rem;
            font-weight: 500;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .sidebar .nav-link.active, 
        .sidebar .nav-link:hover {
            color: #ffffff;
            background: rgba(99, 102, 241, 0.25);
            border-left: 4px solid #6366f1;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
        }

        .card-custom {
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            background: var(--bg-card);
            color: var(--text-color);
        }

        .kpi-card {
            border: none;
            border-radius: 16px;
            color: #fff;
            position: relative;
            overflow: hidden;
        }
        .kpi-card .kpi-icon {
            position: absolute;
            right: 15px;
            bottom: 10px;
            font-size: 3.5rem;
            opacity: 0.2;
        }

        .bg-gradient-blue { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
        .bg-gradient-purple { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
        .bg-gradient-emerald { background: linear-gradient(135deg, #10b981 0%, #047857 100%); }
        .bg-gradient-amber { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); }

        .theme-toggle-btn {
            border-radius: 50px;
            padding: 0.4rem 1rem;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(99, 102, 241, 0.04);
        }

        .chart-container {
            position: relative;
            min-height: 280px;
        }

        @media (max-width: 991.98px) {
            .sidebar { position: relative; width: 100%; height: auto; }
            .main-content { margin-left: 0; padding: 1.25rem; }
        }
    </style>
</head>
<body>

    <!-- Toast Notification Container -->
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
            <h4 class="fw-bold text-white mb-1"><i class="bi bi-wallet2 text-warning me-2"></i>Tracker Admin</h4>
            <span class="badge bg-indigo-subtle text-warning border border-warning-subtle rounded-pill">
                Role: <?= strtoupper(htmlspecialchars($_SESSION['role'])) ?>
            </span>
        </div>
        <ul class="nav nav-pills flex-column mb-auto" id="filterTabs">
            <li class="nav-item"><a class="nav-link active" data-period="all"><i class="bi bi-grid-1x2 me-2"></i> All Entries</a></li>
            <li class="nav-item"><a class="nav-link" data-period="daily"><i class="bi bi-calendar-day me-2"></i> Daily Report</a></li>
            <li class="nav-item"><a class="nav-link" data-period="weekly"><i class="bi bi-calendar-week me-2"></i> Weekly Report</a></li>
            <li class="nav-item"><a class="nav-link" data-period="monthly"><i class="bi bi-calendar-month me-2"></i> Monthly Report</a></li>
            <li class="nav-item"><a class="nav-link" data-period="yearly"><i class="bi bi-calendar3 me-2"></i> Yearly Report</a></li>
        </ul>
        <div class="p-3 border-top border-secondary border-opacity-25">
            <a href="logout.php" class="btn btn-outline-danger w-100 btn-sm rounded-3"><i class="bi bi-box-arrow-right me-1"></i> Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</a>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <main class="main-content" id="reportContent">
        <!-- Header Controls -->
        <header class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
            <div>
                <h3 class="fw-bold mb-0 text-primary"><i class="bi bi-speedometer2 me-2"></i>Income Control Panel</h3>
                <p class="text-muted small mb-0">Logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
            </div>
            
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <!-- Export Buttons -->
                <button onclick="exportToExcel()" class="btn btn-sm btn-outline-success fw-bold"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
                <button onclick="exportToPDF()" class="btn btn-sm btn-outline-danger fw-bold"><i class="bi bi-file-earmark-pdf me-1"></i> PDF Report</button>
                <button id="themeToggleBtn" class="btn btn-outline-secondary theme-toggle-btn d-flex align-items-center gap-2 ms-2">
                    <i id="themeIcon" class="bi bi-moon-stars-fill"></i>
                    <span id="themeLabel">Dark Mode</span>
                </button>
            </div>
        </header>

        <!-- Monthly Target Progress Card -->
        <section class="card card-custom p-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="fw-bold text-muted"><i class="bi bi-bullseye text-danger me-2"></i>Monthly Revenue Target Progress</span>
                <span class="fw-bold text-primary" id="targetProgressText">0% (₹0 / ₹5,00,000)</span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 10px;">
                <div id="targetProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
            </div>
        </section>

        <!-- Dynamic KPI Cards -->
        <section class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3 kpi-card bg-gradient-blue shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Daily Income</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statToday">₹0.00</h2>
                    <i class="bi bi-sun kpi-icon"></i>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3 kpi-card bg-gradient-purple shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Weekly Income</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statWeek">₹0.00</h2>
                    <i class="bi bi-calendar-week kpi-icon"></i>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3 kpi-card bg-gradient-emerald shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Monthly Income</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statMonth">₹0.00</h2>
                    <i class="bi bi-graph-up-arrow kpi-icon"></i>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card p-3 kpi-card bg-gradient-amber shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Yearly Income</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statYear">₹0.00</h2>
                    <i class="bi bi-trophy kpi-icon"></i>
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-primary"><i class="bi bi-bar-chart-line-fill me-2"></i>Monthly Income Analytics</h5>
                        <span class="badge bg-primary rounded-pill">Monthly Trend</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="monthlyTrendChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card card-custom p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold mb-0 text-success"><i class="bi bi-pie-chart-fill me-2"></i>Service Share</h5>
                    </div>
                    <div class="chart-container d-flex align-items-center justify-content-center">
                        <canvas id="servicePieChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <!-- Form, Service Breakdown & Table Grid -->
        <section class="row g-4">
            <div class="col-lg-4">
                <div class="card card-custom p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-success"><i class="bi bi-plus-circle-fill me-2"></i>Add Transaction</h5>
                    <form id="addForm">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Title / Customer Name *</label>
                            <input type="text" id="addTitle" class="form-control border-primary-subtle" placeholder="e.g. Web Development" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount (₹) *</label>
                            <input type="number" step="0.01" id="addAmount" class="form-control border-primary-subtle" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Service Category *</label>
                            <select id="addCategory" class="form-select border-primary-subtle" required>
                                <option value="Salary">Salary</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Business">Business</option>
                                <option value="Investments">Investments</option>
                                <option value="Consulting">Consulting</option>
                                <option value="Maintenance">Maintenance</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Transaction Date *</label>
                            <input type="date" id="addDate" class="form-control border-primary-subtle" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Notes</label>
                            <textarea id="addNotes" class="form-control border-primary-subtle" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                        <button type="submit" id="saveBtn" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Save Transaction
                        </button>
                    </form>
                </div>

                <!-- Service Totals -->
                <div class="card card-custom p-4">
                    <h5 class="fw-bold mb-3 text-info"><i class="bi bi-journal-check me-2"></i>Service Totals</h5>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Service</th>
                                    <th class="text-end">Total Amount</th>
                                </tr>
                            </thead>
                            <tbody id="serviceSummaryTable">
                                <tr><td colspan="2" class="text-center text-muted">Loading summary...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Transactions Grid -->
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-stars text-warning me-2"></i>Detailed Transactions</h5>
                            <span class="small text-muted">Filtered Total: </span>
                            <strong class="text-success fs-5" id="filteredTotal">₹0.00</strong>
                        </div>
                        
                        <!-- Custom Date Range & Quick Search -->
                        <div class="d-flex gap-2 flex-wrap">
                            <input type="date" id="startDateFilter" class="form-control form-control-sm" style="max-width: 130px;" title="Start Date">
                            <input type="date" id="endDateFilter" class="form-control form-control-sm" style="max-width: 130px;" title="End Date">
                            <div class="input-group input-group-sm" style="max-width: 180px;">
                                <span class="input-group-text bg-primary text-white"><i class="bi bi-search"></i></span>
                                <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                            </div>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="transactionTable">
                            <thead class="table-primary">
                                <tr>
                                    <th>Date</th>
                                    <th>Title</th>
                                    <th>Service</th>
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
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm">
                    <div class="modal-body">
                        <input type="hidden" id="editId">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Title</label>
                            <input type="text" id="editTitle" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount (₹)</label>
                            <input type="number" step="0.01" id="editAmount" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Service Category</label>
                            <select id="editCategory" class="form-select" required>
                                <option value="Salary">Salary</option>
                                <option value="Freelance">Freelance</option>
                                <option value="Business">Business</option>
                                <option value="Investments">Investments</option>
                                <option value="Consulting">Consulting</option>
                                <option value="Maintenance">Maintenance</option>
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
        let trendChart = null;
        let pieChart = null;
        const MONTHLY_TARGET = 500000; // Monthly goal: ₹5,00,000

        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const toastElement = new bootstrap.Toast(document.getElementById('liveToast'));

        document.getElementById('addDate').value = new Date().toISOString().split('T')[0];

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/[&<>"']/g, function(m) {
                return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
            });
        }

        function showNotification(message, isSuccess = true) {
            const toastNode = document.getElementById('liveToast');
            toastNode.className = `toast align-items-center text-white border-0 shadow bg-${isSuccess ? 'success' : 'danger'}`;
            document.getElementById('toastMessage').textContent = message;
            toastElement.show();
        }

        // Theme Switcher
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

        // INR Formatter
        const formatCurrency = (val) => '₹' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

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
                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-semibold">${escapeHtml(row.category)}</span></td>
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

        function updateAnalytics(entries, monthTotal) {
            const serviceTotals = {};
            const monthlyTotals = {};

            entries.forEach(row => {
                const amt = parseFloat(row.amount) || 0;
                serviceTotals[row.category] = (serviceTotals[row.category] || 0) + amt;

                if(row.entry_date) {
                    const monthKey = row.entry_date.substring(0, 7);
                    monthlyTotals[monthKey] = (monthlyTotals[monthKey] || 0) + amt;
                }
            });

            // Target Progress Calculation
            const percent = Math.min(100, Math.round((monthTotal / MONTHLY_TARGET) * 100));
            document.getElementById('targetProgressBar').style.width = percent + '%';
            document.getElementById('targetProgressText').textContent = `${percent}% (${formatCurrency(monthTotal)} / ${formatCurrency(MONTHLY_TARGET)})`;

            // Service Summary Table
            const serviceTbody = document.getElementById('serviceSummaryTable');
            const categories = Object.keys(serviceTotals);
            
            if(categories.length === 0) {
                serviceTbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No data available</td></tr>';
            } else {
                serviceTbody.innerHTML = categories.map(cat => `
                    <tr>
                        <td class="fw-semibold">${escapeHtml(cat)}</td>
                        <td class="text-end fw-bold text-success">${formatCurrency(serviceTotals[cat])}</td>
                    </tr>
                `).join('');
            }

            // Pie Chart
            const pieCtx = document.getElementById('servicePieChart').getContext('2d');
            if (pieChart) pieChart.destroy();
            pieChart = new Chart(pieCtx, {
                type: 'doughnut',
                data: {
                    labels: Object.keys(serviceTotals),
                    datasets: [{
                        data: Object.values(serviceTotals),
                        backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#06b6d4', '#64748b']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });

            // Bar Chart
            const sortedMonths = Object.keys(monthlyTotals).sort();
            const monthValues = sortedMonths.map(m => monthlyTotals[m]);

            const trendCtx = document.getElementById('monthlyTrendChart').getContext('2d');
            if (trendChart) trendChart.destroy();
            trendChart = new Chart(trendCtx, {
                type: 'bar',
                data: {
                    labels: sortedMonths,
                    datasets: [{
                        label: 'Monthly Total (₹)',
                        data: monthValues,
                        backgroundColor: '#6366f1',
                        borderRadius: 8
                    }]
                },
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false,
                    scales: { y: { beginAtZero: true } }
                }
            });
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
                    updateAnalytics(cachedEntries, data.stats.month);
                }
            } catch (err) {
                console.error("Error fetching data:", err);
            }
        }

        // Live Search & Custom Date Filter
        function applyFilters() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();
            const startDate = document.getElementById('startDateFilter').value;
            const endDate = document.getElementById('endDateFilter').value;

            const filtered = cachedEntries.filter(row => {
                const matchesSearch = row.title.toLowerCase().includes(query) || 
                                      row.category.toLowerCase().includes(query) ||
                                      row.username.toLowerCase().includes(query);
                
                let matchesDate = true;
                if (startDate && row.entry_date < startDate) matchesDate = false;
                if (endDate && row.entry_date > endDate) matchesDate = false;

                return matchesSearch && matchesDate;
            });

            renderTable(filtered);
        }

        document.getElementById('searchInput').addEventListener('input', applyFilters);
        document.getElementById('startDateFilter').addEventListener('change', applyFilters);
        document.getElementById('endDateFilter').addEventListener('change', applyFilters);

        // Export Functions
        function exportToExcel() {
            const ws = XLSX.utils.json_to_sheet(cachedEntries);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Transactions");
            XLSX.writeFile(wb, "Income_Report.xlsx");
        }

        function exportToPDF() {
            const element = document.getElementById('reportContent');
            html2pdf().set({
                margin: 0.5,
                filename: 'Income_Dashboard_Report.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2 },
                jsPDF: { unit: 'in', format: 'letter', orientation: 'landscape' }
            }).from(element).save();
        }

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
                showNotification('Transaction saved successfully.');
                fetchEntries();
            } else {
                showNotification('Failed to create record.', false);
            }
        });

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
                showNotification('Failed to update record.', false);
            }
        });

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
