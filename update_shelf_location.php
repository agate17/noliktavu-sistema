<?php
include 'config.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrator', 'plauktu_kartotajs'])) {
    header('Location: dashboard.php');
    exit;
}

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $shelf_location = $_POST['shelf_location'] ?? null;
    $notes = $_POST['notes'] ?? '';

    if (!$product_id || !$shelf_location) {
        $response['message'] = 'Trūkst nepieciešamās informācijas';
    } else {
        try {
            // Update product shelf location
            $stmt = $pdo->prepare("
                UPDATE products 
                SET shelf_location = ?, 
                    updated_at = CURRENT_TIMESTAMP 
                WHERE id = ?
            ");
            
            if ($stmt->execute([$shelf_location, $product_id])) {
                // Log the change
                $stmt = $pdo->prepare("
                    INSERT INTO shelf_changes 
                    (product_id, old_location, new_location, changed_by, notes) 
                    VALUES (?, (SELECT shelf_location FROM products WHERE id = ?), ?, ?, ?)
                ");
                $stmt->execute([$product_id, $product_id, $shelf_location, $_SESSION['user_id'], $notes]);
                
                $response['success'] = true;
                $response['message'] = 'Plaukta vieta veiksmīgi atjaunināta';
            } else {
                $response['message'] = 'Kļūda atjauninot plaukta vietu';
            }
        } catch (PDOException $e) {
            $response['message'] = 'Datubāzes kļūda: ' . $e->getMessage();
        }
    }
}

header('Content-Type: application/json');
echo json_encode($response); 