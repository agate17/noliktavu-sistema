<?php
// dashboard.php - Main dashboard
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Check table structure to determine which query to use
try {
    $stmt = $pdo->query("SHOW COLUMNS FROM products LIKE 'category'");
    $hasDirectCategory = $stmt->rowCount() > 0;
    
    if ($hasDirectCategory) {
        // If products table has direct 'category' field
        $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    } else {
        // If products table uses category_id with categories table
        $stmt = $pdo->query("
            SELECT p.*, c.name as category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            ORDER BY p.id DESC
        ");
    }
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    // Fallback to simple query if there's an error
    $stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
    $products = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Produkti</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <h2>📦 STASH</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item active">
                    🏠 Sākums
                </a>
                
                <?php if ($role == 'administrator'): ?>
                    <a href="add_product.php" class="nav-item">
                        ➕ Pievienot produktu
                    </a>
                    <a href="add_user.php" class="nav-item">
                        ➕ Pievienot lietotāju
                    </a>
                    <a href="users.php" class="nav-item">
                        👥 Lietotāji
                    </a>
                    <a href="reports.php" class="nav-item">
                        📊 Izveidot atskaiti
                    </a>
                <?php elseif ($role == 'warehouse_worker'): ?>
                    <a href="orders.php" class="nav-item">
                        🚚 Veikt pasūtījumu
                    </a>
                    <a href="reports.php" class="nav-item">
                        📊 Izveidot atskaiti
                    </a>
                <?php elseif (in_array($role, ['shelf_organizer', 'plauktu_kartotajs'])): ?>
                    <a href="shelf_management.php" class="nav-item">
                        📋 Plauktu pārvaldība
                    </a>
                    <a href="shelf_reports.php" class="nav-item">
                        📊 Plauktu atskaites
                    </a>
                <?php endif; ?>
                
                <a href="logout.php" class="nav-item">
                    🚪 Iziet
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Produkti</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($username); ?></strong> 
                    (<?php echo ucfirst(str_replace('_', ' ', $role)); ?>)
                </div>
            </div>
            
            <div class="content">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
                <?php endif; ?>
                
                <?php if ($role == 'administrator'): ?>
                    <div class="actions">
                        <a href="add_product.php" class="btn btn-success">➕ Pievienot produktu</a>
                    </div>
                <?php endif; ?>
                
                <?php if (in_array($role, ['shelf_organizer', 'plauktu_kartotajs'])): ?>
                    <div class="dashboard-grid">
                        <!-- Quick Actions -->
                        <div class="dashboard-card">
                            <h3>Ātrās darbības</h3>
                            <div class="card-content">
                                <a href="shelf_management.php" class="btn btn-primary">Pārvaldīt plauktus</a>
                                <a href="shelf_reports.php" class="btn btn-secondary">Skatīt atskaites</a>
                            </div>
                        </div>

                        <!-- Shelf Status -->
                        <div class="dashboard-card">
                            <h3>Plaukta statuss</h3>
                            <div class="card-content">
                                <div class="shelf-status">
                                    <div class="status-item">
                                        <span class="status-label">Aktīvie plaukti:</span>
                                        <span class="status-value">0</span>
                                    </div>
                                    <div class="status-item">
                                        <span class="status-label">Produkti bez vietas:</span>
                                        <span class="status-value">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="table-container">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Produkts</th>
                                <th>Kategorija</th>
                                <th>Cena</th>
                                <th>Firmas ID</th>
                                <th>Daudzums</th>
                                <th>Plaukta vieta</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php 
                                        if ($hasDirectCategory) {
                                            echo htmlspecialchars($product['category'] ?? 'Nav kategorijas');
                                        } else {
                                            echo htmlspecialchars($product['category_name'] ?? 'Nav kategorijas');
                                        }
                                    ?></td>
                                    <td><?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['company_id']); ?></td>
                                    <td><?php echo $product['quantity']; ?></td>
                                    <td><?php echo htmlspecialchars($product['shelf_location'] ?? 'Nav piešķirts'); ?></td>
                                    <td class="actions-cell">
                                        <?php if ($role == 'administrator'): ?>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Rediģēt</a>
                                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-delete" onclick="return confirm('Vai tiešām vēlaties dzēst šo produktu?')">Dzēst</a>
                                        <?php elseif (in_array($role, ['shelf_organizer', 'plauktu_kartotajs'])): ?>
                                            <a href="shelf_management.php?product_id=<?php echo $product['id']; ?>" class="btn btn-primary">Pārvaldīt plauktu</a>
                                        <?php else: ?>
                                            <span class="btn btn-disabled">Dzēst</span>
                                            <span class="btn btn-disabled">Rediģēt</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>