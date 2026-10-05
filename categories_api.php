<?php
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

// GET: Fetch all categories
if ($method === 'GET') {
    $stmt = $pdo->query("SELECT id, name FROM service_categories ORDER BY name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'categories' => $categories]);
    exit;
}

// POST: Add a new category
if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim($input['name'] ?? '');

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Category name is required']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO service_categories (name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
        echo json_encode(['success' => true, 'id' => $pdo->lastInsertId(), 'name' => $name]);
    } catch (PDOException $e) {
        if ($e->getCode() == '23000') {
            echo json_encode(['success' => false, 'message' => 'Category already exists']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error']);
        }
    }
    exit;
}

// PUT: Update an existing category
if ($method === 'PUT') {
    $input = json_decode(file_get_contents('php://input'), true);
    $id = intval($input['id'] ?? 0);
    $name = trim($input['name'] ?? '');

    if ($id <= 0 || empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE service_categories SET name = :name WHERE id = :id");
        $stmt->execute([':name' => $name, ':id' => $id]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error updating category']);
    }
    exit;
}

// DELETE: Delete a category
if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM service_categories WHERE id = :id");
    $stmt->execute([':id' => $id]);
    echo json_encode(['success' => true]);
    exit;
}
