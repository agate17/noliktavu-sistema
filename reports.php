<?php
// reports.php - Report generation for warehouse workers
session_start();
require 'config.php';

// Access control
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrator', 'warehouse_worker'])) {
    header('Location: dashboard.php');
    exit;
}

// Read filter inputs
$startDate = $_GET['start_date'] ?? '';
$endDate   = $_GET['end_date'] ?? '';

// Build WHERE clause
$where = [];
$params = [];
if ($startDate) {
    $where[] = "o.created_at >= ?";
    $params[] = $startDate . ' 00:00:00';
}
if ($endDate) {
    $where[] = "o.created_at <= ?";
    $params[] = $endDate . ' 23:59:59';
}
$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Fetch summary metrics
$sqlSummary = "
SELECT
    COUNT(*) as total_orders,
    COALESCE(SUM(o.total_amount),0) as total_revenue,
    SUM(CASE WHEN o.status='pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN o.status='processing' THEN 1 ELSE 0 END) as processing_count,
    SUM(CASE WHEN o.status='fulfilled' THEN 1 ELSE 0 END) as fulfilled_count,
    SUM(CASE WHEN o.status='cancelled' THEN 1 ELSE 0 END) as cancelled_count
FROM orders o
" . $whereSql;
$stmt = $pdo->prepare($sqlSummary);
$stmt->execute($params);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch top-selling products
$sqlTop = "
SELECT
    oi.product_name,
    SUM(oi.quantity) as total_sold,
    SUM(oi.line_total) as revenue
FROM order_items oi
JOIN orders o ON oi.order_id = o.id
" . $whereSql . "
GROUP BY oi.product_id, oi.product_name
ORDER BY total_sold DESC
LIMIT 10";
$stmt2 = $pdo->prepare($sqlTop);
$stmt2->execute($params);
$topProducts = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// CSV export
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="warehouse_report.csv"');
    $out = fopen('php://output', 'w');
    // Summary
    fputcsv($out, ['Metric', 'Value']);
    fputcsv($out, ['Total Orders', $summary['total_orders']]);
    fputcsv($out, ['Total Revenue', $summary['total_revenue']]);
    fputcsv($out, ['Pending Orders', $summary['pending_count']]);
    fputcsv($out, ['Processing Orders', $summary['processing_count']]);
    fputcsv($out, ['Fulfilled Orders', $summary['fulfilled_count']]);
    fputcsv($out, ['Cancelled Orders', $summary['cancelled_count']]);
    fputcsv($out, []);
    // Top products
    fputcsv($out, ['Product', 'Quantity Sold', 'Revenue']);
    foreach ($topProducts as $prod) {
        fputcsv($out, [$prod['product_name'], $prod['total_sold'], $prod['revenue']]);
    }
    fclose($out);
    exit;
}
?>
<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>STASH - Atskaites</title>
    <link rel="stylesheet" href="css/style.css" />
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo"><h2>STASH</h2></div>
            <nav>
                <ul class="nav-menu">
                    <li><a href="dashboard.php" class="nav-item">🏠 Sākums</a></li>
                    <?php if ($_SESSION['role'] == 'warehouse_worker'): ?>
                        <li><a href="orders.php" class="nav-item">🚚 Veikt pasūtījumu</a></li>
                    <?php endif; ?>
                    <li><a href="reports.php" class="nav-item active">📊 Atskaites</a></li>
                    <li><a href="logout.php" class="nav-item">🚪 Iziet</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header class="header">
                <h1>Atskaites</h1>
                <div class="user-info">
                    Lietotājs: <strong><?= htmlspecialchars($_SESSION['username']) ?></strong>
                    (<?= ucfirst(str_replace('_', ' ', $_SESSION['role'])) ?>)
                </div>
            </header>
            <section class="content">
                <form method="GET" class="actions">
                    <div class="form-group">
                        <label for="start_date">Sākuma datums:</label>
                        <input type="date" id="start_date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" />
                    </div>
                    <div class="form-group">
                        <label for="end_date">Beigu datums:</label>
                        <input type="date" id="end_date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" />
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Filtrēt</button>
                    <button type="submit" name="export" value="csv" class="btn btn-secondary btn-sm">Eksportēt CSV</button>

                </form>

                <div class="report-section">
                    <div class="table-container">
                        <table class="table metrics">
                            <tbody>
                                <tr>
                                    <th>Kopā pasūtījumi</th>
                                    <td><?= $summary['total_orders'] ?></td>
                                </tr>
                                <tr>
                                    <th>Kopējie ieņēmumi (€)</th>
                                    <td><?= number_format($summary['total_revenue'], 2) ?></td>
                                </tr>
                                <tr>
                                    <th>Gaidošie</th>
                                    <td><?= $summary['pending_count'] ?></td>
                                </tr>
                                <tr>
                                    <th>Apstrādē</th>
                                    <td><?= $summary['processing_count'] ?></td>
                                </tr>
                                <tr>
                                    <th>Izpildīti</th>
                                    <td><?= $summary['fulfilled_count'] ?></td>
                                </tr>
                                <tr>
                                    <th>Atcelti</th>
                                    <td><?= $summary['cancelled_count'] ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-section">
                    <h2>Top 10 pārdotākie produkti</h2>
                    <?php if ($topProducts): ?>
                        <div class="table-container">
                            <table class="products-table">
                                <thead>
                                    <tr>
                                        <th>Produkts</th>
                                        <th>Daudzums</th>
                                        <th>Ieņēmumi (€)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($topProducts as $prod): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($prod['product_name']) ?></td>
                                            <td><?= $prod['total_sold'] ?></td>
                                            <td><?= number_format($prod['revenue'], 2) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p>Nav datu atbilstoši jūsu filtram.</p>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
