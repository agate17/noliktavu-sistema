<?php
// add_user.php - Add new user (Administrator only)
include 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'administrator') {
    header('Location: dashboard.php');
    exit;
}

// Fetch all users from the database
try {
    $query = "SELECT * FROM users ORDER BY created_at DESC";
    $stmt = $pdo->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

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
                <a href="add_user.php" class="nav-item ">➕ Pievienot lietotāju</a>
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
            
            <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Lietotājvārds</th>
                        <th><i class="fas fa-id-card"></i> Pilns vārds</th>
                        <th><i class="fas fa-envelope"></i> E-pasts</th>
                        <th><i class="fas fa-user-tag"></i> Loma</th>
                        <th><i class="fas fa-toggle-on"></i> Statuss</th>
                        <th><i class="fas fa-calendar-alt"></i> Izveidots</th>
                        <th><i class="fas fa-cogs"></i> Darbības</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $usr): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usr['username']); ?></td>
                        <td><?php echo htmlspecialchars($usr['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($usr['email'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($usr['role']); ?></td>
                        <td><?php echo $usr['is_active'] ? '<i class="fas fa-check text-success"></i> Aktīvs' : '<i class="fas fa-times text-danger"></i> Neaktīvs'; ?></td>
                        <td><?php echo date('d.m.Y', strtotime($usr['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-success btn-sm" onclick="editUser(<?php echo $usr['id']; ?>)">
                                <i class="fas fa-edit"></i> Rediģēt
                            </button>
                            <?php if ($usr['id'] != $_SESSION['user_id']): ?>
                            <button class="btn btn-danger btn-sm" onclick="deleteUser(<?php echo $usr['id']; ?>)">
                                <i class="fas fa-trash"></i> Dzēst
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        </main>
    </div>

    <script>
        function editUser(userId) {
            window.location.href = `edit_user.php?id=${userId}`;
        }

        function deleteUser(userId) {
            if (confirm('Vai tiešām vēlaties dzēst šo lietotāju?')) {
                fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Lietotājs veiksmīgi dzēsts!');
                        window.location.reload();
                    } else {
                        alert('Kļūda dzēšot lietotāju: ' + data.message);
                    }
                })
                .catch(error => {
                    alert('Kļūda: ' + error);
                });
            }
        }
    </script>
</body>
</html>