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

    <style>
        :root {
            /* Compact Sidebar Sizes */
            --sidebar-width: 220px;
            --sidebar-collapsed-width: 64px;
            --bg-body: #f4f6fb;
            --bg-card: #ffffff;
            --text-color: #0f172a;
            --sidebar-bg: #0f172a;
            --sidebar-text: #94a3b8;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.05);
            --sidebar-active-bg: #6366f1;
            --border-color: #e2e8f0;
        }

        [data-bs-theme="dark"] {
            --bg-body: #0b0f19;
            --bg-card: #151d2a;
            --text-color: #f8fafc;
            --sidebar-bg: #070a12;
            --sidebar-text: #64748b;
            --sidebar-hover-bg: rgba(255, 255, 255, 0.03);
            --sidebar-active-bg: #4f46e5;
            --border-color: #1e293b;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-color);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        /* Compact Dynamic Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background: var(--sidebar-bg);
            color: #fff;
            padding: 1rem 0.5rem;
            z-index: 1000;
            border-right: 1px solid var(--border-color);
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
            overflow-x: hidden;
            white-space: nowrap;
        }

        .sidebar-brand {
            padding: 0.25rem 0.5rem 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar .nav-label {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--sidebar-text);
            padding: 0.35rem 0.5rem;
            margin-top: 0.5rem;
            transition: opacity 0.2s ease;
        }

        .sidebar .nav-link {
            color: var(--sidebar-text);
            padding: 0.55rem 0.65rem;
            border-radius: 8px;
            margin-bottom: 0.25rem;
            font-weight: 500;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            border: 1px solid transparent;
        }

        .sidebar .nav-link i {
            font-size: 1.1rem;
            min-width: 1.5rem;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .sidebar .nav-link:hover {
            color: #ffffff;
            background: var(--sidebar-hover-bg);
        }

        .sidebar .nav-link:hover i {
            transform: scale(1.1);
        }

        .sidebar .nav-link.active {
            color: #ffffff;
            background: var(--sidebar-active-bg);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
            font-weight: 600;
        }

        .sidebar .nav-link.active i {
            color: #ffffff;
        }

        .nav-text {
            margin-left: 0.4rem;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Collapsed Sidebar Styles */
        body.sidebar-collapsed .sidebar {
            width: var(--sidebar-collapsed-width);
        }

        body.sidebar-collapsed .main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        body.sidebar-collapsed .nav-text,
        body.sidebar-collapsed .nav-label,
        body.sidebar-collapsed .brand-text,
        body.sidebar-collapsed .role-badge,
        body.sidebar-collapsed .logout-text {
            opacity: 0;
            visibility: hidden;
            display: none !important;
        }

        body.sidebar-collapsed .sidebar-brand {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }

        body.sidebar-collapsed .sidebar .nav-link {
            justify-content: center;
            padding: 0.55rem 0;
        }

        body.sidebar-collapsed .sidebar .nav-link i {
            margin-right: 0;
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
            height: 100%;
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
        .bg-gradient-cyan { background: linear-gradient(135deg, #06b6d4 0%, #0e7490 100%); }
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

        /* Floating Action Button (FAB) */
        .fab-btn {
            position: fixed;
            bottom: 25px;
            right: 25px;
            z-index: 1050;
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            border: none;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            cursor: pointer;
        }

        .fab-btn:hover {
            transform: scale(1.1) rotate(90deg);
            box-shadow: 0 12px 28px rgba(16, 185, 129, 0.6);
            color: #ffffff;
        }

        .fab-btn i {
            font-size: 1.65rem;
        }

        /* 5-Column Responsive Layout for KPIs */
        @media (min-width: 1200px) {
            .col-xl-custom {
                flex: 0 0 auto;
                width: 20%;
            }
        }

        @media (max-width: 991.98px) {
            .sidebar { width: var(--sidebar-collapsed-width); }
            .main-content { margin-left: var(--sidebar-collapsed-width); padding: 1.25rem; }
            .nav-text, .nav-label, .brand-text, .role-badge, .logout-text { display: none !important; }
            .sidebar-brand { justify-content: center; }
            .sidebar .nav-link { justify-content: center; }
            .fab-btn { bottom: 20px; right: 20px; width: 52px; height: 52px; }
            .fab-btn i { font-size: 1.4rem; }
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

    <!-- Floating Action Button (FAB) -->
    <button type="button" class="fab-btn" id="floatingAddBtn" title="Add New Transaction" onclick="handleFloatingAdd()">
        <i class="bi bi-plus-lg"></i>
    </button>

    <!-- Compact Collapsible Sidebar Navigation -->
    <aside class="sidebar d-flex flex-column" id="sidebar">
        <div class="sidebar-brand">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-wallet2 text-warning fs-4"></i>
                <h5 class="fw-bold text-white mb-0 brand-text" style="font-size: 1.05rem;">Tracker Admin</h5>
            </div>
        </div>

        <div class="role-badge px-1 mb-2">
            <span class="badge bg-indigo-subtle text-warning border border-warning-subtle rounded-pill px-2 py-1 w-100 text-center" style="font-size: 0.7rem;">
                <i class="bi bi-shield-check me-1"></i><?= strtoupper(htmlspecialchars($_SESSION['role'])) ?>
            </span>
        </div>

        <div class="nav-label">Main Navigation</div>
        <ul class="nav nav-pills flex-column mb-auto" id="filterTabs">
            <li class="nav-item">
                <a class="nav-link active" data-period="all" title="Transactions Dashboard">
                    <i class="bi bi-grid-1x2"></i><span class="nav-text">Transactions</span>
                </a>
            </li>
            
            <div class="nav-label">Management</div>
            <li class="nav-item">
                <a class="nav-link" id="serviceCategoryBtn" onclick="openCategoryModal()" title="Service Category">
                    <i class="bi bi-tags"></i><span class="nav-text">Service Category</span>
                </a>
            </li>
        </ul>

        <div class="pt-2 border-top border-secondary border-opacity-25 mt-auto">
            <a href="logout.php" class="btn btn-outline-danger w-100 btn-sm rounded-3 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1" style="font-size: 0.8rem;" title="Logout">
                <i class="bi bi-box-arrow-right fs-6"></i><span class="logout-text">Logout (<?= htmlspecialchars($_SESSION['username']) ?>)</span>
            </a>
        </div>
    </aside>

    <!-- Main Content Panel -->
    <main class="main-content" id="reportContent">
        <!-- Header Controls -->
        <header class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2 border-bottom gap-2">
            <div class="d-flex align-items-center gap-3">
                <button id="sidebarToggle" class="btn btn-light border shadow-sm rounded-3 px-2 py-1" title="Toggle Sidebar">
                    <i class="bi bi-list fs-4"></i>
                </button>
                <div>
                    <h3 class="fw-bold mb-0 text-primary"><i class="bi bi-speedometer2 me-2"></i>Income Control Panel</h3>
                    <p class="text-muted small mb-0">Logged in as <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></p>
                </div>
            </div>
            
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button onclick="exportToExcel()" class="btn btn-sm btn-outline-success fw-bold"><i class="bi bi-file-earmark-excel me-1"></i> Excel</button>
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
                <span class="fw-bold text-primary" id="targetProgressText">0% (₹0 / ₹15,000)</span>
            </div>
            <div class="progress" style="height: 12px; border-radius: 10px;">
                <div id="targetProgressBar" class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%;"></div>
            </div>
        </section>

        <!-- Dynamic KPI Cards -->
        <section class="row g-3 mb-4">
            <div class="col-sm-6 col-md-4 col-xl-custom">
                <div class="card p-3 kpi-card bg-gradient-blue shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Daily Income (Today)</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statToday">₹0.00</h2>
                    <i class="bi bi-sun kpi-icon"></i>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-custom">
                <div class="card p-3 kpi-card bg-gradient-purple shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Weekly (Sun - Sat)</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statWeek">₹0.00</h2>
                    <i class="bi bi-calendar-week kpi-icon"></i>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-custom">
                <div class="card p-3 kpi-card bg-gradient-emerald shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Monthly Income</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statMonth">₹0.00</h2>
                    <i class="bi bi-graph-up-arrow kpi-icon"></i>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-custom">
                <div class="card p-3 kpi-card bg-gradient-cyan shadow">
                    <span class="text-uppercase small fw-bold text-white-50">12-Mo Avg Income</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statMonthlyAvg">₹0.00</h2>
                    <i class="bi bi-calendar2-range kpi-icon"></i>
                </div>
            </div>
            <div class="col-sm-6 col-md-4 col-xl-custom">
                <div class="card p-3 kpi-card bg-gradient-amber shadow">
                    <span class="text-uppercase small fw-bold text-white-50">Yearly Income</span>
                    <h2 class="fw-bold mb-0 mt-1" id="statYear">₹0.00</h2>
                    <i class="bi bi-trophy kpi-icon"></i>
                </div>
            </div>
        </section>

        <!-- Single Full-Width Monthly Chart Section -->
        <section class="row g-4 mb-4">
            <div class="col-12">
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
        </section>

        <!-- Form, Service Breakdown & Table Grid -->
        <section class="row g-4">
            <div class="col-lg-4">
                <div class="card card-custom p-4 mb-4" id="addTransactionCard">
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
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Service Category *</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" onclick="openCategoryModal()">+ Manage</button>
                            </div>
                            <select id="addCategory" class="form-select border-primary-subtle" required></select>
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

                <!-- Service Totals Card (A to Z Sorted) -->
                <div class="card card-custom p-4" id="serviceTotalsCard">
                    <h5 class="fw-bold mb-3 text-info"><i class="bi bi-journal-check me-2"></i>Service Totals (A - Z)</h5>
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

            <!-- Detailed Transactions Grid with Dynamic Month Controls -->
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <div>
                            <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-stars text-warning me-2"></i>Detailed Transactions</h5>
                            <span class="small text-muted">Filtered Total: </span>
                            <strong class="text-success fs-5" id="filteredTotal">₹0.00</strong>
                        </div>

                        <!-- Month Navigator Controls -->
                        <div class="d-flex align-items-center gap-1 bg-light border rounded-3 p-1">
                            <button id="prevMonthBtn" class="btn btn-sm btn-outline-secondary px-2 py-0 fw-bold" title="Previous Month">&lt;</button>
                            <span id="monthRangeDisplay" class="fw-bold text-primary small px-2"></span>
                            <button id="nextMonthBtn" class="btn btn-sm btn-outline-secondary px-2 py-0 fw-bold" title="Next Month">&gt;</button>
                        </div>
                        
                        <div class="d-flex gap-2 flex-wrap">
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

    <!-- Floating Add Transaction Modal for Mobile View -->
    <div class="modal fade" id="addTransactionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-success text-white rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="bi bi-plus-circle-fill me-2"></i>Add New Transaction</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="modalAddForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Title / Customer Name *</label>
                            <input type="text" id="modalAddTitle" class="form-control" placeholder="e.g. Web Development" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Amount (₹) *</label>
                            <input type="number" step="0.01" id="modalAddAmount" class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold mb-0">Service Category *</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" onclick="openCategoryModalFromFloating()">+ Manage</button>
                            </div>
                            <select id="modalAddCategory" class="form-select" required></select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Transaction Date *</label>
                            <input type="date" id="modalAddDate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Notes</label>
                            <textarea id="modalAddNotes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light btn-sm rounded-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm fw-bold rounded-2"><i class="bi bi-check-lg me-1"></i> Save Transaction</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin Edit Transaction Modal -->
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
                            <select id="editCategory" class="form-select" required></select>
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

    <!-- Service Category Manager Modal -->
    <div class="modal fade" id="categoryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header bg-dark text-white rounded-top-4">
                    <h5 class="modal-title fw-bold text-white"><i class="bi bi-tags me-2"></i>Service Category Manager</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="addCategoryForm" class="mb-4">
                        <label class="form-label small fw-bold">Add New Category</label>
                        <div class="input-group">
                            <input type="text" id="newCategoryInput" class="form-control" placeholder="Category Name..." required>
                            <button class="btn btn-success fw-bold" type="submit"><i class="bi bi-plus-lg me-1"></i> Add</button>
                        </div>
                    </form>

                    <h6 class="fw-bold mb-2">Existing Categories</h6>
                    <ul class="list-group rounded-3" id="categoryListGroup"></ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let userRole = '<?= $_SESSION['role'] ?>';
        let cachedEntries = [];
        let categories = [];
        let trendChart = null;
        const MONTHLY_TARGET = 15000;

        let currentDate = new Date();

        const editModal = new bootstrap.Modal(document.getElementById('editModal'));
        const categoryModal = new bootstrap.Modal(document.getElementById('categoryModal'));
        const addTransactionModal = new bootstrap.Modal(document.getElementById('addTransactionModal'));
        const toastElement = new bootstrap.Toast(document.getElementById('liveToast'));

        const todayStr = new Date().toISOString().split('T')[0];
        document.getElementById('addDate').value = todayStr;
        document.getElementById('modalAddDate').value = todayStr;

        // Floating Action Button Behavior
        function handleFloatingAdd() {
            if (window.innerWidth < 992) {
                // Mobile View: Trigger Pop-up Modal Form
                addTransactionModal.show();
            } else {
                // Desktop View: Scroll Smoothly to Section & Focus Text Box
                const addCard = document.getElementById('addTransactionCard');
                addCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                document.getElementById('addTitle').focus();
            }
        }

        function openCategoryModalFromFloating() {
            addTransactionModal.hide();
            openCategoryModal();
        }

        // Sidebar Collapse Handler
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.addEventListener('click', () => {
            document.body.classList.toggle('sidebar-collapsed');
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
        });

        if (localStorage.getItem('sidebarCollapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }

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

        async function fetchCategories() {
            try {
                const res = await fetch('categories_api.php');
                const data = await res.json();
                if (data.success) {
                    categories = data.categories;
                    renderCategoryDropdowns();
                }
            } catch (err) {
                console.error("Failed to load categories:", err);
            }
        }

        function renderCategoryDropdowns() {
            const optionsHtml = categories.map(cat => `<option value="${escapeHtml(cat.name)}">${escapeHtml(cat.name)}</option>`).join('');
            document.getElementById('addCategory').innerHTML = optionsHtml;
            document.getElementById('modalAddCategory').innerHTML = optionsHtml;
            document.getElementById('editCategory').innerHTML = optionsHtml;
        }

        function renderCategoryList() {
            const listGroup = document.getElementById('categoryListGroup');
            listGroup.innerHTML = categories.map(cat => `
                <li class="list-group-item d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold">${escapeHtml(cat.name)}</span>
                    <div>
                        <button class="btn btn-sm btn-outline-primary border-0 me-1" onclick="editCategoryPrompt(${cat.id}, '${escapeHtml(cat.name)}')" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger border-0" onclick="deleteCategory(${cat.id}, '${escapeHtml(cat.name)}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </li>
            `).join('');
        }

        function openCategoryModal() {
            renderCategoryList();
            categoryModal.show();
        }

        document.getElementById('addCategoryForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const input = document.getElementById('newCategoryInput');
            const name = input.value.trim();

            if (!name) return;

            const res = await fetch('categories_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name })
            });
            const data = await res.json();

            if (data.success) {
                input.value = '';
                showNotification(`Category "${name}" added.`);
                await fetchCategories();
                renderCategoryList();
            } else {
                showNotification(data.message || 'Error adding category.', false);
            }
        });

        async function editCategoryPrompt(id, currentName) {
            const newName = prompt('Edit Category Name:', currentName);
            if (!newName || newName.trim() === '' || newName.trim() === currentName) return;

            const res = await fetch('categories_api.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, name: newName.trim() })
            });
            const data = await res.json();

            if (data.success) {
                showNotification(`Category updated to "${newName.trim()}".`);
                await fetchCategories();
                renderCategoryList();
            } else {
                showNotification(data.message || 'Error updating category.', false);
            }
        }

        async function deleteCategory(id, name) {
            if (confirm(`Delete category "${name}"?`)) {
                const res = await fetch(`categories_api.php?id=${id}`, { method: 'DELETE' });
                const data = await res.json();

                if (data.success) {
                    showNotification(`Category "${name}" deleted.`);
                    await fetchCategories();
                    renderCategoryList();
                } else {
                    showNotification(data.message || 'Error deleting category.', false);
                }
            }
        }

        // Dark/Light Theme Handler
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

        const formatCurrency = (val) => '₹' + Number(val).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        function formatDateToDDMMYYYY(dateObj) {
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            return `${day}-${month}-${year}`;
        }

        function formatDateToYYYYMMDD(dateObj) {
            const day = String(dateObj.getDate()).padStart(2, '0');
            const month = String(dateObj.getMonth() + 1).padStart(2, '0');
            const year = dateObj.getFullYear();
            return `${year}-${month}-${day}`;
        }

        function renderTable(entries) {
            const tbody = document.getElementById('entriesTable');
            if (entries.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">No records found for this month.</td></tr>';
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

            // Target Progress Calculation based on ₹15,000
            const percent = Math.min(100, Math.round((monthTotal / MONTHLY_TARGET) * 100));
            document.getElementById('targetProgressBar').style.width = percent + '%';
            document.getElementById('targetProgressText').textContent = `${percent}% (${formatCurrency(monthTotal)} / ${formatCurrency(MONTHLY_TARGET)})`;

            // Service Summary Table - Sorted Alphabetically (A to Z)
            const serviceTbody = document.getElementById('serviceSummaryTable');
            const cats = Object.keys(serviceTotals).sort((a, b) => a.localeCompare(b));
            
            if(cats.length === 0) {
                serviceTbody.innerHTML = '<tr><td colspan="2" class="text-center text-muted">No data available</td></tr>';
            } else {
                serviceTbody.innerHTML = cats.map(cat => `
                    <tr>
                        <td class="fw-semibold">${escapeHtml(cat)}</td>
                        <td class="text-end fw-bold text-success">${formatCurrency(serviceTotals[cat])}</td>
                    </tr>
                `).join('');
            }

            // Trend Bar Chart
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
                const res = await fetch(`api.php?period=all`);
                const data = await res.json();

                if (data.success) {
                    cachedEntries = data.entries;

                    // ----------------------------------------------------
                    // 1) Calculate Today's Exact Total
                    // ----------------------------------------------------
                    const now = new Date();
                    const todayISO = formatDateToYYYYMMDD(now);

                    // ----------------------------------------------------
                    // 2) Calculate Sunday to Saturday Week Bounds
                    // ----------------------------------------------------
                    const currentDayOfWeek = now.getDay(); // 0 = Sun, 1 = Mon, ..., 6 = Sat
                    
                    const sunDate = new Date(now);
                    sunDate.setDate(now.getDate() - currentDayOfWeek);
                    
                    const satDate = new Date(now);
                    satDate.setDate(now.getDate() + (6 - currentDayOfWeek));

                    const sunISO = formatDateToYYYYMMDD(sunDate);
                    const satISO = formatDateToYYYYMMDD(satDate);

                    let todayTotal = 0;
                    let weekTotal = 0;

                    cachedEntries.forEach(row => {
                        const amt = parseFloat(row.amount) || 0;

                        // Today's Check
                        if (row.entry_date === todayISO) {
                            todayTotal += amt;
                        }

                        // Current Week Check (Sunday to Saturday)
                        if (row.entry_date >= sunISO && row.entry_date <= satISO) {
                            weekTotal += amt;
                        }
                    });

                    // Update UI Cards
                    document.getElementById('statToday').textContent = formatCurrency(todayTotal);
                    document.getElementById('statWeek').textContent = formatCurrency(weekTotal);
                    document.getElementById('statMonth').textContent = formatCurrency(data.stats.month);
                    document.getElementById('statYear').textContent = formatCurrency(data.stats.year);

                    // 12-Month Average Monthly Income
                    const yearlyTotal = parseFloat(data.stats.year) || 0;
                    const twelveMonthAvg = yearlyTotal / 12;
                    document.getElementById('statMonthlyAvg').textContent = formatCurrency(twelveMonthAvg);

                    updateAnalytics(cachedEntries, data.stats.month);
                    applyFilters();
                }
            } catch (err) {
                console.error("Error fetching data:", err);
            }
        }

        function applyFilters() {
            const query = document.getElementById('searchInput').value.toLowerCase().trim();

            const year = currentDate.getFullYear();
            const month = currentDate.getMonth();

            const startDateObj = new Date(year, month, 1);
            const endDateObj = new Date(year, month + 1, 0);

            const startDateFormatted = formatDateToDDMMYYYY(startDateObj);
            const endDateFormatted = formatDateToDDMMYYYY(endDateObj);

            // Dynamic Month Navigator Header Text
            document.getElementById('monthRangeDisplay').textContent = `< ${startDateFormatted} to ${endDateFormatted} >`;

            const startDateISO = formatDateToYYYYMMDD(startDateObj);
            const endDateISO = formatDateToYYYYMMDD(endDateObj);

            let total = 0;
            const filtered = cachedEntries.filter(row => {
                const matchesSearch = row.title.toLowerCase().includes(query) || 
                                      row.category.toLowerCase().includes(query) ||
                                      row.username.toLowerCase().includes(query);
                
                let matchesDate = true;
                if (row.entry_date < startDateISO || row.entry_date > endDateISO) {
                    matchesDate = false;
                }

                if (matchesSearch && matchesDate) {
                    total += parseFloat(row.amount) || 0;
                    return true;
                }
                return false;
            });

            document.getElementById('filteredTotal').textContent = formatCurrency(total);
            renderTable(filtered);
        }

        // Monthly Navigation Controls (< & > Buttons)
        document.getElementById('prevMonthBtn').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() - 1);
            applyFilters();
        });

        document.getElementById('nextMonthBtn').addEventListener('click', () => {
            currentDate.setMonth(currentDate.getMonth() + 1);
            applyFilters();
        });

        document.getElementById('searchInput').addEventListener('input', applyFilters);

        function exportToExcel() {
            const ws = XLSX.utils.json_to_sheet(cachedEntries);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, "Transactions");
            XLSX.writeFile(wb, "Income_Report.xlsx");
        }

        // Inline Form Submit
        document.getElementById('addForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                title: document.getElementById('addTitle').value,
                amount: document.getElementById('addAmount').value,
                category: document.getElementById('addCategory').value,
                entry_date: document.getElementById('addDate').value,
                notes: document.getElementById('addNotes').value
            };

            await submitNewTransaction(payload, () => {
                document.getElementById('addTitle').value = '';
                document.getElementById('addAmount').value = '';
                document.getElementById('addNotes').value = '';
            });
        });

        // Mobile Form Submit
        document.getElementById('modalAddForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                title: document.getElementById('modalAddTitle').value,
                amount: document.getElementById('modalAddAmount').value,
                category: document.getElementById('modalAddCategory').value,
                entry_date: document.getElementById('modalAddDate').value,
                notes: document.getElementById('modalAddNotes').value
            };

            await submitNewTransaction(payload, () => {
                document.getElementById('modalAddTitle').value = '';
                document.getElementById('modalAddAmount').value = '';
                document.getElementById('modalAddNotes').value = '';
                addTransactionModal.hide();
            });
        });

        async function submitNewTransaction(payload, onSuccess) {
            const res = await fetch('api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                if (onSuccess) onSuccess();
                showNotification('Transaction saved successfully.');
                fetchEntries();
            } else {
                showNotification('Failed to create record.', false);
            }
        }

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

        // Load Initial Categories & Data
        fetchCategories();
        fetchEntries();
    </script>
</body>
</html>
