<?php
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in and has appropriate permissions
if (!isLoggedIn() || !hasRole(['administrator', 'plauktu_kartotajs'])) {
    header('Location: login.php');
    exit();
}

// Get filter parameters
$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : null;
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : null;
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : null;

// Build the query
$query = "SELECT sc.*, p.name as product_name, u.username as changed_by_username 
          FROM shelf_changes sc 
          JOIN products p ON sc.product_id = p.id 
          JOIN users u ON sc.changed_by = u.id 
          WHERE 1=1";
$params = [];

if ($product_id) {
    $query .= " AND sc.product_id = ?";
    $params[] = $product_id;
}
if ($date_from) {
    $query .= " AND DATE(sc.created_at) >= ?";
    $params[] = $date_from;
}
if ($date_to) {
    $query .= " AND DATE(sc.created_at) <= ?";
    $params[] = $date_to;
}

$query .= " ORDER BY sc.created_at DESC";

try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $changes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all products for the filter dropdown
    $products = $pdo->query("SELECT id, name FROM products ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Kļūda ielādējot vēsturi: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plauktu izvietojuma vēsture - STASH</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Plauktu izvietojuma vēsture</h1>
                <div class="user-info">
                    Lietotājs: <strong><?= htmlspecialchars(getCurrentUsername()) ?></strong>
                    (<?= htmlspecialchars(getCurrentUserRole()) ?>)
                </div>
            </div>
            
            <div class="content">
                <!-- Filter form -->
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="product_id">Produkts:</label>
                        <select name="product_id" id="product_id">
                            <option value="">Visi produkti</option>
                            <?php foreach ($products as $product): ?>
                                <option value="<?= $product['id'] ?>" <?= $product_id == $product['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($product['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="date_from">No datuma:</label>
                        <input type="date" name="date_from" id="date_from" value="<?= $date_from ?>">
                    </div>

                    <div class="form-group">
                        <label for="date_to">Līdz datumam:</label>
                        <input type="date" name="date_to" id="date_to" value="<?= $date_to ?>">
                    </div>

                    <button type="submit" class="btn">Filtrēt</button>
                    <a href="shelf_history.php" class="btn btn-secondary">Atiestatīt</a>
                </form>

                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (empty($changes)): ?>
                    <p class="no-data">Nav atrasts neviens ieraksts.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Datums</th>
                                    <th>Produkts</th>
                                    <th>Vecā vieta</th>
                                    <th>Jaunā vieta</th>
                                    <th>Mainīja</th>
                                    <th>Piezīmes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($changes as $change): ?>
                                    <tr>
                                        <td><?= date('d.m.Y H:i', strtotime($change['created_at'])) ?></td>
                                        <td><?= htmlspecialchars($change['product_name']) ?></td>
                                        <td><?= htmlspecialchars($change['old_location'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($change['new_location']) ?></td>
                                        <td><?= htmlspecialchars($change['changed_by_username']) ?></td>
                                        <td><?= htmlspecialchars($change['notes'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html> 