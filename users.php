<?php
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'administrator') {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['action'] === 'edit') {
        $user_id = $_POST['user_id'];
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];
        $phone = $_POST['phone'];
        $role_id = $_POST['role_id'];
        $is_active = $_POST['is_active'];
        $new_password = $_POST['new_password'];

        try {
            $pdo->beginTransaction();

            // Update user details
            $sql = "UPDATE users SET 
                    username = :username,
                    full_name = :full_name,
                    email = :email,
                    phone = :phone,
                    role_id = :role_id,
                    is_active = :is_active";
            
            $params = [
                ':username' => $username,
                ':full_name' => $full_name,
                ':email' => $email,
                ':phone' => $phone,
                ':role_id' => $role_id,
                ':is_active' => $is_active,
                ':id' => $user_id
            ];

            // Add password update if provided
            if (!empty($new_password)) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = :id";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $pdo->commit();
            header('Location: users.php?success=1');
            exit;
        } catch (PDOException $e) {
            $pdo->rollBack();
            $error = "Kļūda: " . $e->getMessage();
        }
    } elseif ($action === 'delete') {
        $user_id = $_POST['user_id'];
        
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['success'] = "Lietotājs veiksmīgi dzēsts!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Kļūda dzēšot lietotāju: " . $e->getMessage();
        }
    }
    
    header("Location: users.php");
    exit;
}

// Get all roles for the dropdown
$roles = $pdo->query("SELECT * FROM roles ORDER BY role_name")->fetchAll();

// Initialize edit_user variable
$edit_user = null;

// Get user for editing if ID is provided
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.username, u.full_name, u.email, u.phone, u.is_active, u.created_at, r.role_name, u.role_id
        FROM users u 
        LEFT JOIN roles r ON u.role_id = r.id 
        WHERE u.id = ?
    ");
    $stmt->execute([$_GET['edit']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get all users
$stmt = $pdo->query("
    SELECT u.id, u.username, u.full_name, u.email, u.phone, u.is_active, u.created_at, r.role_name 
    FROM users u 
    LEFT JOIN roles r ON u.role_id = r.id 
    ORDER BY u.username
");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Lietotāju pārvaldība</title>
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
                <h1>Lietotāju pārvaldība</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> (Administrator)
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($edit_user): ?>
            <div class="form-section">
                <h2>Labot lietotāju</h2>
                <form method="POST" class="form">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="user_id" value="<?php echo $edit_user['id']; ?>">
                    
                    <div class="form-group">
                        <label for="username">Lietotājvārds:</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($edit_user['username']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="full_name">Pilns vārds:</label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($edit_user['full_name'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="email">E-pasts:</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($edit_user['email'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone">Tālrunis:</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($edit_user['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="role_id">Loma:</label>
                        <select id="role_id" name="role_id" required>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>" <?php echo $role['id'] == $edit_user['role_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="is_active">Statuss:</label>
                        <select id="is_active" name="is_active">
                            <option value="1" <?php echo $edit_user['is_active'] ? 'selected' : ''; ?>>Aktīvs</option>
                            <option value="0" <?php echo !$edit_user['is_active'] ? 'selected' : ''; ?>>Neaktīvs</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">Jauna parole (atstājiet tukšu, lai nemainītu):</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Saglabāt</button>
                        <a href="users.php" class="btn btn-secondary">Atcelt</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <div class="users-table">
                <table>
                    <thead>
                        <tr>
                            <th><i class="fas fa-user"></i> Lietotājvārds</th>
                            <th><i class="fas fa-id-card"></i> Pilns vārds</th>
                            <th><i class="fas fa-envelope"></i> E-pasts</th>
                            <th><i class="fas fa-phone"></i> Tālrunis</th>
                            <th><i class="fas fa-user-tag"></i> Loma</th>
                            <th><i class="fas fa-toggle-on"></i> Statuss</th>
                            <th><i class="fas fa-calendar-alt"></i> Izveidots</th>
                            <th><i class="fas fa-cogs"></i> Darbības</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($user['role_name']); ?></td>
                            <td><?php echo $user['is_active'] ? '<i class="fas fa-check text-success"></i> Aktīvs' : '<i class="fas fa-times text-danger"></i> Neaktīvs'; ?></td>
                            <td><?php echo date('d.m.Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <a href="?edit=<?php echo $user['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Labot
                                </a>
                                <?php if ($user['username'] !== 'admin'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-danger" onclick="return confirm('Vai tiešām vēlaties dzēst šo lietotāju?')">
                                        <i class="fas fa-trash"></i> Dzēst
                                    </button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>