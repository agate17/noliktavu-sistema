<?php
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    echo json_encode(['success' => false, 'error' => 'Nav autorizācijas']);
    exit();
}

// Validate input
if (empty($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'Trūkst atskaites ID']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Delete report items first
    $stmt = $pdo->prepare("DELETE FROM shelf_report_items WHERE report_id = ?");
    $stmt->execute([$_POST['id']]);

    // Delete the report
    $stmt = $pdo->prepare("DELETE FROM shelf_reports WHERE id = ?");
    $stmt->execute([$_POST['id']]);

    $pdo->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 