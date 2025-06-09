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
            // Check if username exists
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $existingUser = $stmt->fetch();

            if ($existingUser) {
                $error = "Lietotājvārds jau eksistē!";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $role = 'worker'; // default role
                $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashedPassword, $role]);

                $success = "Konts izveidots! Tagad vari pieteikties.";
            }
        } elseif ($action === 'login') {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Nepareizs lietotājvārds vai parole";
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
