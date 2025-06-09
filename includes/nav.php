<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="logo">
        <h2>📦 STASH</h2>
    </div>
    
    <nav class="nav-menu">
        <a href="dashboard.php" class="nav-item <?= $current_page === 'dashboard.php' ? 'active' : '' ?>">
            🏠 Sākums
        </a>
        
        <?php if (hasRole(['administrator'])): ?>
            <a href="add_product.php" class="nav-item <?= $current_page === 'add_product.php' ? 'active' : '' ?>">
                ➕ Pievienot produktu
            </a>
            <a href="add_user.php" class="nav-item <?= $current_page === 'add_user.php' ? 'active' : '' ?>">
                ➕ Pievienot lietotāju
            </a>
            <a href="users.php" class="nav-item <?= $current_page === 'users.php' ? 'active' : '' ?>">
                👥 Lietotāji
            </a>
            <a href="reports.php" class="nav-item <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                📊 Izveidot atskaiti
            </a>
        <?php endif; ?>
        
        <?php if (hasRole(['warehouse_worker'])): ?>
            <a href="orders.php" class="nav-item <?= $current_page === 'orders.php' ? 'active' : '' ?>">
                🚚 Veikt pasūtījumu
            </a>
            <a href="reports.php" class="nav-item <?= $current_page === 'reports.php' ? 'active' : '' ?>">
                📊 Izveidot atskaiti
            </a>
        <?php endif; ?>
        
        <?php if (hasRole(['plauktu_kartotajs'])): ?>
            <a href="shelf_management.php" class="nav-item <?= $current_page === 'shelf_management.php' ? 'active' : '' ?>">
                📋 Plauktu pārvaldība
            </a>
            <a href="shelf_history.php" class="nav-item <?= $current_page === 'shelf_history.php' ? 'active' : '' ?>">
                📊 Plauktu vēsture
            </a>
            <a href="shelf_reports.php" class="nav-item <?= $current_page === 'shelf_reports.php' ? 'active' : '' ?>">
                📊 Plauktu atskaite
            </a>
        <?php endif; ?>
        
        <a href="logout.php" class="nav-item">
            🚪 Iziet
        </a>
    </nav>
</aside> 