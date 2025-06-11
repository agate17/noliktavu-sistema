<?php
// order_edit.php - Edit single order
session_start();
require 'config.php';

// Access control
define('ALLOWED_ROLES', ['administrator','warehouse_worker']);
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ALLOWED_ROLES)) {
    header('Location: dashboard.php'); exit;
}

// Get order ID
$orderId = intval($_GET['id'] ?? 0);
if ($orderId <= 0) {
    header('Location: orders.php'); exit;
}

$message = '';
$error = '';

// Handle update submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update customer info
        $customer_name = trim($_POST['customer_name']);
        $customer_email = trim($_POST['customer_email']);
        $customer_phone = trim($_POST['customer_phone']);
        $status = $_POST['status'];

        // Basic validation
        if (!$customer_name || !filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Lūdzu, ievadiet derīgus klienta datus.');
        }
        // Update order
        $stmt = $pdo->prepare("UPDATE orders SET customer_name=?, customer_email=?, customer_phone=?, status=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$customer_name,$customer_email,$customer_phone,$status,$orderId]);
        $message = 'Pasūtījums atjaunināts.';
    } catch(Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch order and items
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id=?");
$stmt->execute([$orderId]);
$order = $stmt->fetch();
if (!$order) {
    header('Location: orders.php'); exit;
}

$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id=?");
$stmt->execute([$orderId]);
$items = $stmt->fetchAll();

// Status options
$status_labels = ['pending'=>'Gaidošs','processing'=>'Apstrādē','fulfilled'=>'Izpildīts','cancelled'=>'Atcelts'];
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Rediģēt pasūtījumu #<?= $orderId ?></title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
<div class="container">
  <aside class="sidebar">
    <div class="logo"><h2>📦 STASH</h2></div>
    <nav class="nav-menu">
      <a href="dashboard.php" class="nav-item">🏠 Sākums</a>
      <a href="orders.php" class="nav-item active">🚚 Pasūtījumi</a>
      <a href="logout.php" class="nav-item">🚪 Iziet</a>
    </nav>
  </aside>
  <main class="main-content">
    <div class="header">
      <h1>Rediģēt pasūtījumu #<?= $orderId ?></h1>
      <div class="user-info">Lietotājs: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong></div>
    </div>
    <div class="content">
      <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
      <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

      <form method="POST" class="form-group">
        <fieldset>
          <legend>Klienta informācija</legend>
          <label>Vārds:<input type="text" name="customer_name" value="<?= htmlspecialchars($order['customer_name']) ?>" required></label>
          <label>E-pasts:<input type="email" name="customer_email" value="<?= htmlspecialchars($order['customer_email']) ?>" required></label>
          <label>Telefons:<input type="text" name="customer_phone" value="<?= htmlspecialchars($order['customer_phone']) ?>"></label>
        </fieldset>
        <fieldset>
          <legend>Pasūtījuma statuss</legend>
          <select name="status">
            <?php foreach($status_labels as $key=>$label): ?>
              <option value="<?= $key ?>" <?= $order['status']==$key?'selected':'' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
        </fieldset>
        <button type="submit" class="btn btn-primary">Saglabāt izmaiņas</button>
        <a href="orders.php" class="btn btn-secondary">Atpakaļ uz pasūtījumiem</a>
      </form>

      <h2>Pasūtījuma produkti</h2>
      <div class="table-container">
        <table class="products-table">
          <thead><tr><th>Produkts</th><th>Daudzums</th><th>Cena</th><th>Kopā</th></tr></thead>
          <tbody>
            <?php foreach($items as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['product_name']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td>€<?= number_format($item['unit_price'],2) ?></td>
              <td>€<?= number_format($item['line_total'],2) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>
</body>
</html>
