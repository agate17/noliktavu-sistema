<?php
include 'config.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrator', 'warehouse_worker'])) {
    header('Location: dashboard.php');
    exit;
}

// Form submission handling is temporarily disabled
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Pievienot produktu</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <h2>📦 STASH</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item">🏠 Sākums</a>
                <a href="add_product.php" class="nav-item active">➕ Pievienot produktu</a>
                <a href="add_user.php" class="nav-item">➕ Pievienot lietotāju</a>
                <a href="users.php" class="nav-item">👥 Lietotāji</a>
                <a href="reports.php" class="nav-item">📊 Izveidot atskaiti</a>
                <a href="logout.php" class="nav-item">🚪 Iziet</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Pievienot jaunu produktu</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                    (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                </div>
            </div>
            
            <div class="content">
                <p style="color: red;"> NEPICIEŠAMS PIESTRADAT PIE INFO PIEVIENOŠANAS DB - paslaik ir tiri tikai no dizaina aspekta</p>
                <form method="POST" class="form" onsubmit="return false;">
                    <div class="form-group">
                        <label for="name">Produkta nosaukums:</label>
                        <input type="text" id="name" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category">Kategorija:</label>
                        <select id="category" name="category" required>
                            <option value="">Izvēlieties kategoriju</option>
                            <option value="electronics">Elektronika</option>
                            <option value="furniture">Mēbeles</option>
                            <option value="clothing">Apģērbs</option>
                            <option value="food">Pārtika</option>
                            <option value="other">Cits</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Cena (EUR):</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Daudzums:</label>
                        <input type="number" id="quantity" name="quantity" min="0" value="0" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_id">Uzņēmuma ID:</label>
                        <input type="text" id="company_id" name="company_id">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Apraksts:</label>
                        <textarea id="description" name="description" rows="3"></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="location">Noliktavas vieta:</label>
                        <input type="text" id="location" name="location">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success" disabled>Pievienot produktu</button>
                        <a href="products.php" class="btn btn-secondary">Atcelt</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 