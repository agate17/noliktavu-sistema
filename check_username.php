<?php
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$username = isset($input['username']) ? sanitizeInput($input['username']) : '';

if (empty($username)) {
    echo json_encode(['available' => false, 'message' => 'Username is required']);
    exit();
}

if (!validateUsername($username)) {
    echo json_encode(['available' => false, 'message' => 'Username can only contain letters and numbers (3-50 characters)']);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
        echo json_encode(['available' => false, 'message' => 'Username is already taken']);
    } else {
        echo json_encode(['available' => true, 'message' => 'Username is available']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['available' => false, 'message' => 'Error checking username']);
}
?>