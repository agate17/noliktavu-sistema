<?php
require_once 'config.php';
require_once 'auth.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Nav autorizācijas']);
    exit();
}

// Validate input
if (empty($_POST['report_name']) || empty($_POST['report_type'])) {
    echo json_encode(['success' => false, 'error' => 'Trūkst nepieciešamie lauki']);
    exit();
}

try {
    $pdo->beginTransaction();

    // Insert report record
    $stmt = $pdo->prepare("INSERT INTO shelf_reports (name, type, date_from, date_to, created_by) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['report_name'],
        $_POST['report_type'],
        !empty($_POST['date_from']) ? $_POST['date_from'] : null,
        !empty($_POST['date_to']) ? $_POST['date_to'] : null,
        $_SESSION['user_id']
    ]);
    
    $reportId = $pdo->lastInsertId();

    // Get products based on report type
    if ($_POST['report_type'] === 'current') {
        // Get current shelf status
        $stmt = $pdo->prepare("
            SELECT p.*, c.name as category_name, co.name as company_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN companies co ON p.company_id = co.id
            WHERE p.shelf_location IS NOT NULL
            ORDER BY p.shelf_location, p.name
        ");
        $stmt->execute();
    } else {
        // Get shelf changes
        $query = "
            SELECT p.*, c.name as category_name, co.name as company_name,
                   sc.old_location, sc.new_location, sc.created_at, sc.notes
            FROM shelf_changes sc
            JOIN products p ON sc.product_id = p.id
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN companies co ON p.company_id = co.id
            WHERE 1=1
        ";
        
        $params = [];
        if (!empty($_POST['date_from'])) {
            $query .= " AND sc.created_at >= ?";
            $params[] = $_POST['date_from'];
        }
        if (!empty($_POST['date_to'])) {
            $query .= " AND sc.created_at <= ?";
            $params[] = $_POST['date_to'] . ' 23:59:59';
        }
        
        $query .= " ORDER BY sc.created_at DESC";
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
    }

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Insert report items
    $stmt = $pdo->prepare("
        INSERT INTO shelf_report_items (report_id, product_id, shelf_location, quantity, notes)
        VALUES (?, ?, ?, ?, ?)
    ");

    foreach ($products as $product) {
        $stmt->execute([
            $reportId,
            $product['id'],
            $product['shelf_location'],
            $product['quantity'],
            $_POST['report_type'] === 'changes' ? 
                "Izmaiņa: {$product['old_location']} -> {$product['new_location']} ({$product['created_at']})" : 
                null
        ]);
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'report_id' => $reportId]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 