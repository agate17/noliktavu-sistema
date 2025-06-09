<?php
// add_user.php - Add new user (Administrator only)
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'administrator') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    if ($stmt->execute([$username, $password, $role])) {
        header('Location: users.php?success=Lietotājs pievienots veiksmīgi');
        exit;
    } else {
        $error = "Kļūda pievienojot lietotāju";
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Pievienot lietotāju</title>
    <link rel="stylesheet" href="style.css">
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
                <a href="add_user.php" class="nav-item active">➕ Pievienot lietotāju</a>
                <a href="users.php" class="nav-item">👥 Lietotāji</a>
                <a href="reports.php" class="nav-item">📊 Izveidot atskaiti</a>
                <a href="logout.php" class="nav-item">🚪 Iziet</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Pievienot jaunu lietotāju</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (Administrator)
                </div>
            </div>
            
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="username">Lietotājvārds:</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Parole:</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Loma:</label>
                        <select id="role" name="role" required>
                            <option value="">Izvēlieties lomu</option>
                            <option value="administrator">Administrators</option>
                            <option value="warehouse_worker">Noliktavas darbinieks</option>
                            <option value="shelf_organizer">Plauktu kārtotājs</option>
                        </select>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Pievienot lietotāju</button>
                        <a href="users.php" class="btn btn-secondary">Atcelt</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>