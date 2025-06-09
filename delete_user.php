<?php
include 'config.php';

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'administrator') {
    echo json_encode(['success' => false, 'message' => 'Nav atļauts']);
    exit;
}

// Check if user_id is provided
if (!isset($_POST['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Nav norādīts lietotājs']);
    exit;
}

$user_id = $_POST['user_id'];

// Prevent deleting yourself
if ($user_id == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'Nevar dzēst pašu sevi']);
    exit;
}

try {
    // Delete the user
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$user_id]);

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Neizdevās dzēst lietotāju']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Datubāzes kļūda']);
}
?> 