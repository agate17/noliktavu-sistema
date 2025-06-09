<?php
include 'config.php';

// Check if user is logged in and is an administrator
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'administrator') {
    header('Location: dashboard.php');
    exit;
}

// Check if user ID is provided
if (!isset($_GET['id'])) {
    header('Location: users.php');
    exit;
}

$user_id = $_GET['id'];

// Fetch user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header('Location: users.php');
        exit;
    }
} catch (PDOException $e) {
    die("Kļūda: " . $e->getMessage());
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    try {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
        $result = $stmt->execute([$username, $full_name, $email, $role, $is_active, $user_id]);

        if ($result) {
            header('Location: users.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = "Kļūda: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Rediģēt lietotāju</title>
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
                <a href="users.php" class="nav-item active">👥 Lietotāji</a>
                <a href="reports.php" class="nav-item">📊 Izveidot atskaiti</a>
                <a href="logout.php" class="nav-item">🚪 Iziet</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Rediģēt lietotāju</h1>
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
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Pilns vārds:</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">E-pasts:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                    </div>

                    <div class="form-group">
                        <label for="role">Loma:</label>
                        <select id="role" name="role" required>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Lietotājs</option>
                            <option value="administrator" <?php echo $user['role'] === 'administrator' ? 'selected' : ''; ?>>Administrators</option>
                            <option value="warehouse_worker" <?php echo $user['role'] === 'warehouse_worker' ? 'selected' : ''; ?>>Noliktavas darbinieks</option>
                            <option value="shelf_organizer" <?php echo $user['role'] === 'shelf_organizer' ? 'selected' : ''; ?>>Plauktu kārtotājs</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?>>
                            Aktīvs
                        </label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Saglabāt izmaiņas</button>
                        <a href="users.php" class="btn btn-secondary">Atcelt</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 