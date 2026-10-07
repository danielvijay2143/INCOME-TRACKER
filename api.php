<?php
// Start session before accessing session variables
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once 'db.php';

// Auth Guard
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$method   = $_SERVER['REQUEST_METHOD'];
$userId   = $_SESSION['user_id'];
$userRole = $_SESSION['role'] ?? 'user';

// Helper function to validate CSRF tokens
function validateCsrfToken($token) {
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// --- GET REQUEST: FETCH ENTRIES & ANALYTICS ---
if ($method === 'GET') {
    // Single Entry Fetch for Admin Edit Modal
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        $stmt = $pdo->prepare("SELECT * FROM income_entries WHERE id = ?");
        $stmt->execute([$id]);
        $entry = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($entry) {
            echo json_encode(['success' => true, 'entry' => $entry]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Record not found.']);
        }
        exit;
    }

    $period   = $_GET['period'] ?? 'all';
    $category = $_GET['category'] ?? '';

    $whereClauses = ["1=1"];
    $params = [];

    if ($period === 'daily') {
        $whereClauses[] = "entry_date = CURDATE()";
    } elseif ($period === 'weekly') {
        $whereClauses[] = "YEARWEEK(entry_date, 1) = YEARWEEK(CURDATE(), 1)";
    } elseif ($period === 'monthly') {
        $whereClauses[] = "MONTH(entry_date) = MONTH(CURDATE()) AND YEAR(entry_date) = YEAR(CURDATE())";
    } elseif ($period === 'yearly') {
        $whereClauses[] = "YEAR(entry_date) = YEAR(CURDATE())";
    }

    if (!empty($category)) {
        $whereClauses[] = "category = ?";
        $params[] = $category;
    }

    $whereSQL = implode(" AND ", $whereClauses);

    $stmt = $pdo->prepare("SELECT e.*, u.username FROM income_entries e JOIN users u ON e.user_id = u.id WHERE $whereSQL ORDER BY entry_date DESC, id DESC");
    $stmt->execute($params);
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stats = [
        'today' => (float)($pdo->query("SELECT SUM(amount) FROM income_entries WHERE entry_date = CURDATE()")->fetchColumn() ?: 0.00),
        'week'  => (float)($pdo->query("SELECT SUM(amount) FROM income_entries WHERE YEARWEEK(entry_date, 1) = YEARWEEK(CURDATE(), 1)")->fetchColumn() ?: 0.00),
        'month' => (float)($pdo->query("SELECT SUM(amount) FROM income_entries WHERE MONTH(entry_date) = MONTH(CURDATE()) AND YEAR(entry_date) = YEAR(CURDATE())")->fetchColumn() ?: 0.00),
        'year'  => (float)($pdo->query("SELECT SUM(amount) FROM income_entries WHERE YEAR(entry_date) = YEAR(CURDATE())")->fetchColumn() ?: 0.00),
    ];

    $filteredTotal = array_reduce($entries, fn($sum, $item) => $sum + (float)$item['amount'], 0.0);

    echo json_encode([
        'success'        => true,
        'stats'          => $stats,
        'filtered_total' => $filteredTotal,
        'entries'        => $entries,
        'user_role'      => $userRole
    ]);
    exit;
}

// --- POST REQUEST: ADD OR EDIT ENTRY ---
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input    = json_decode($rawInput, true) ?? $_POST;

    // Validate CSRF
    $csrfToken = $input['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'CSRF validation failed.']);
        exit;
    }

    $id        = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    $title     = trim($input['title'] ?? '');
    $amount    = filter_var($input['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    $category  = trim($input['category'] ?? '');
    $entryDate = $input['entry_date'] ?? '';
    $notes     = trim($input['notes'] ?? '');

    // Validate Date Format (YYYY-MM-DD)
    if ($entryDate && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $entryDate)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid date format. Expected YYYY-MM-DD.']);
        exit;
    }

    if ($title && $amount && $entryDate && $category) {
        if ($id) {
            // Edit Control Authorization (Admin Only)
            if ($userRole !== 'admin') {
                http_response_code(403);
                echo json_encode(['error' => 'Permission denied. Admin role required.']);
                exit;
            }

            $stmt = $pdo->prepare("UPDATE income_entries SET title = ?, amount = ?, category = ?, entry_date = ?, notes = ? WHERE id = ?");
            $stmt->execute([$title, $amount, $category, $entryDate, $notes, $id]);
            echo json_encode(['success' => true, 'message' => 'Entry updated successfully']);
        } else {
            // New Entry Creation
            $stmt = $pdo->prepare("INSERT INTO income_entries (user_id, title, amount, category, entry_date, notes) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $title, $amount, $category, $entryDate, $notes]);
            echo json_encode(['success' => true, 'message' => 'Entry created successfully']);
        }
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Required fields missing.']);
    exit;
}

// --- DELETE REQUEST: REMOVE ENTRY ---
if ($method === 'DELETE') {
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Permission denied. Admin role required.']);
        exit;
    }

    // CSRF check via URL parameter for DELETE requests
    $csrfToken = $_GET['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['error' => 'CSRF validation failed.']);
        exit;
    }

    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM income_entries WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Entry deleted successfully']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID.']);
    exit;
}
?>