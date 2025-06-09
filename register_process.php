<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    if ($password !== $confirm) {
        header("Location: register.php?error=Passwords+do+not+match");
        exit();
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        header("Location: register.php?error=Username+already+taken");
        exit();
    }

    $stmt->close();

    // Hash password and insert user
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Default role_id = 1 (adjust as needed)
    $default_role = 1;

    $stmt = $conn->prepare("INSERT INTO users (username, password, role_id) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $username, $hashed_password, $default_role);

    if ($stmt->execute()) {
        header("Location: register.php?success=Registration+successful!+Please+login");
    } else {
        header("Location: register.php?error=Registration+failed");
    }

    $stmt->close();
    $conn->close();
}
