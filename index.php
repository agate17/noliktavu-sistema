<?php
// index.php - Main entry point
include 'config.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Basic validation
    if (strlen($username) < 3 || strlen($username) > 20 || !preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error = "Lietotājvārdam jābūt no 3 līdz 20 simboliem (tikai burti, cipari un _)";
    } elseif (strlen($password) < 6) {
        $error = "Parolei jābūt vismaz 6 simbolus garai";
    } else {
        if ($action === 'register') {
            $confirm_password = $_POST['confirm_password'];
            
            if ($password !== $confirm_password) {
                $error = "Paroles nesakrīt!";
            } else {
                try {
                    // Check if username exists
                    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                    $stmt->execute([$username]);
                    if ($stmt->rowCount() > 0) {
                        $error = "Lietotājvārds jau eksistē!";
                    } else {
                        // Get worker role ID
                        $stmt = $pdo->prepare("SELECT id FROM roles WHERE role_name = 'worker'");
                        $stmt->execute();
                        $role = $stmt->fetch();
                        
                        if (!$role) {
                            throw new Exception("Worker role not found in database");
                        }
                        
                        // Insert new user with worker role
                        $stmt = $pdo->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
                        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), $role['id']]);
                        
                        $_SESSION['success'] = "Reģistrācija veiksmīga! Lūdzu, pieteikties.";
                        header("Location: index.php");
                        exit;
                    }
                } catch (Exception $e) {
                    $error = "Kļūda reģistrācijas laikā: " . $e->getMessage();
                }
            }
        } elseif ($action === 'login') {
            try {
                $stmt = $pdo->prepare("
                    SELECT u.*, r.role_name 
                    FROM users u 
                    JOIN roles r ON u.role_id = r.id 
                    WHERE u.username = ?
                ");
                $stmt->execute([$username]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role_name'];
                    
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "Nepareizs lietotājvārds vai parole!";
                }
            } catch (PDOException $e) {
                $error = "Kļūda pieteikšanās laikā: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Pieteikšanās / Reģistrācija</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .toggle-link {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
            margin-top: 10px;
        }
        .alert {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
    </style>
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>📦 STASH</h1>
            <p>Noliktavas pārvaldības sistēma</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST" class="login-form" id="loginForm">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="username">Lietotājvārds:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Parole:</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Pieteikties</button>
        </form>

        <!-- Registration Form -->
        <form method="POST" class="login-form" id="registerForm" style="display: none;">
            <input type="hidden" name="action" value="register">
            <div class="form-group">
                <label for="username">Izvēlies lietotājvārdu:</label>
                <input type="text" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Izvēlies paroli:</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Atkārtojiet paroli:</label>
                <input type="password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-secondary">Reģistrēties</button>
        </form>

        <div class="toggle-link" onclick="toggleForms()">Vai tev nav konta? Reģistrējies šeit</div>

        <div class="demo-accounts">
            <h3>Demo konti:</h3>
            <p><strong>Administrators:</strong> admin / admin123</p>
            <p><strong>Noliktavas darbinieks:</strong> worker / admin123</p>
            <p><strong>Plauktu kārtotājs:</strong> organizer / admin123</p>
        </div>
    </div>

    <script>
        function toggleForms() {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            loginForm.style.display = loginForm.style.display === 'none' ? 'block' : 'none';
            registerForm.style.display = registerForm.style.display === 'none' ? 'block' : 'none';

            const toggle = document.querySelector('.toggle-link');
            toggle.textContent = toggle.textContent.includes('Reģistrējies') ?
                'Tev jau ir konts? Pieslēdzies' : 'Vai tev nav konta? Reģistrējies šeit';
        }
    </script>
</body>
</html>
