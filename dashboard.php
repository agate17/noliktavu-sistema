<?php
// dashboard.php - Main dashboard
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Get products
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
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
                <?php elseif ($role == 'shelf_organizer'): ?>
                    <a href="organize.php" class="nav-item">
                        📦 Izvietot preces
                    </a>
                    <a href="reports.php" class="nav-item">
                        📋 Sagatavot atskaiti
                    </a>
                    <a href="data_entry.php" class="nav-item">
                        📝 Datu ievade
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
                
                <div class="table-container">
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Produkts</th>
                                <th>Kategorija</th>
                                <th>Cena</th>
                                <th>Firmas ID</th>
                                <th>Daudzums</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo htmlspecialchars($product['category']); ?></td>
                                    <td><?php echo number_format($product['price'], 2); ?></td>
                                    <td><?php echo htmlspecialchars($product['company_id']); ?></td>
                                    <td><?php echo $product['quantity']; ?></td>
                                    <td class="actions-cell">
                                        <?php if ($role == 'administrator'): ?>
                                            <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Rediģēt</a>
                                            <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-delete" onclick="return confirm('Vai tiešām vēlaties dzēst šo produktu?')">Dzēst</a>
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