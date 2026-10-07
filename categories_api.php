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
$userRole = $_SESSION['role'] ?? 'user';

// Helper function to validate CSRF tokens
function validateCsrfToken($token) {
    return !empty($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// --- GET: FETCH CATEGORIES ---
if ($method === 'GET') {
    try {
        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'categories' => $categories]);
    } catch (\PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to fetch categories.']);
    }
    exit;
}

// --- POST: ADD CATEGORY ---
if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $input    = json_decode($rawInput, true) ?? $_POST;

    // CSRF Check
    $csrfToken = $input['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
        exit;
    }

    $name = trim($input['name'] ?? '');

    if (!empty($name)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
            $stmt->execute([$name]);
            echo json_encode(['success' => true, 'message' => 'Category added successfully']);
        } catch (\PDOException $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Category already exists or an error occurred']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category name required']);
    }
    exit;
}

// --- PUT: EDIT CATEGORY ---
if ($method === 'PUT') {
    $rawInput = file_get_contents('php://input');
    $input    = json_decode($rawInput, true);

    // Admin Role Check
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission denied. Admin role required.']);
        exit;
    }

    // CSRF Check
    $csrfToken = $input['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
        exit;
    }

    $id   = filter_var($input['id'] ?? null, FILTER_VALIDATE_INT);
    $name = trim($input['name'] ?? '');

    if ($id && !empty($name)) {
        $stmt = $pdo->prepare("UPDATE categories SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        echo json_encode(['success' => true, 'message' => 'Category updated']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
    }
    exit;
}

// --- DELETE: REMOVE CATEGORY ---
if ($method === 'DELETE') {
    // Admin Role Check
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Permission denied. Admin role required.']);
        exit;
    }

    // CSRF Check via query string
    $csrfToken = $_GET['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'CSRF validation failed.']);
        exit;
    }

    $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
    if ($id) {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Category deleted']);
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    }
    exit;
}
?>