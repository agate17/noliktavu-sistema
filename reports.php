<?php
include 'config.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrator', 'warehouse_worker'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Atskaites</title>
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
                <a href="add_product.php" class="nav-item">➕ Pievienot produktu</a>
                <a href="add_user.php" class="nav-item">➕ Pievienot lietotāju</a>
                <a href="users.php" class="nav-item">👥 Lietotāji</a>
                <a href="reports.php" class="nav-item active">📊 Izveidot atskaiti</a>
                <a href="logout.php" class="nav-item">🚪 Iziet</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Atskaišu veidošana</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                    (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                </div>
            </div>
            
            <div class="content">
                <div class="info-box">
                    <h3 style="color: red;">⚠️ work in progress</h3>
                    <p style="color: red;"> ja pareizi noprotu šī lapa rāda visas atskaites (aka working on it)</p>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 