<?php
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Get report ID
$report_id = $_GET['id'] ?? null;
$summary_only = isset($_GET['summary']) && $_GET['summary'] == 1;

if (!$report_id) {
    header('Location: shelf_reports.php');
    exit();
}

try {
    // Get report details
    $stmt = $pdo->prepare("
        SELECT sr.*, u.username as created_by_username 
        FROM shelf_reports sr 
        LEFT JOIN users u ON sr.created_by = u.id 
        WHERE sr.id = ?
    ");
    $stmt->execute([$report_id]);
    $report = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$report) {
        header('Location: shelf_reports.php');
        exit();
    }

    // Get report items with product details
    $query = "
        SELECT sri.*, p.name as product_name, c.name as category_name, co.name as company_name
        FROM shelf_report_items sri
        JOIN products p ON sri.product_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN companies co ON p.company_id = co.id
        WHERE sri.report_id = ?
        ORDER BY sri.shelf_location, p.name
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$report_id]);
    $report_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate summary statistics
    $summary = [
        'total_products' => count($report_items),
        'total_quantity' => array_sum(array_column($report_items, 'quantity')),
        'locations' => []
    ];

    foreach ($report_items as $item) {
        $loc = $item['shelf_location'];
        if (!isset($summary['locations'][$loc])) {
            $summary['locations'][$loc] = [
                'product_count' => 0,
                'total_quantity' => 0
            ];
        }
        $summary['locations'][$loc]['product_count']++;
        $summary['locations'][$loc]['total_quantity'] += $item['quantity'];
    }

} catch (PDOException $e) {
    $error = "Kļūda ielādējot datus: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atskaite: <?= htmlspecialchars($report['name']) ?> - STASH</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Atskaite: <?= htmlspecialchars($report['name']) ?></h1>
                <div class="user-info">
                    Lietotājs: <strong><?= htmlspecialchars(getCurrentUsername()) ?></strong>
                    (<?= htmlspecialchars(getCurrentUserRole()) ?>)
                </div>
            </div>
            
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <div class="report-info">
                    <p>
                        <strong>Veids:</strong> 
                        <?= $report['type'] === 'current' ? 'Pašreizējais stāvoklis' : 'Izmaiņu vēsture' ?>
                    </p>
                    <p>
                        <strong>Izveidoja:</strong> 
                        <?= htmlspecialchars($report['created_by_username']) ?>
                    </p>
                    <p>
                        <strong>Izveidots:</strong> 
                        <?= date('d.m.Y H:i', strtotime($report['created_at'])) ?>
                    </p>
                    <?php if ($report['date_from'] || $report['date_to']): ?>
                        <p>
                            <strong>Periods:</strong>
                            <?= $report['date_from'] ? date('d.m.Y', strtotime($report['date_from'])) : '-' ?>
                            līdz
                            <?= $report['date_to'] ? date('d.m.Y', strtotime($report['date_to'])) : '-' ?>
                        </p>
                    <?php endif; ?>
                </div>

                <?php if (empty($report_items)): ?>
                    <p class="no-data">Nav atrasts neviens ieraksts.</p>
                <?php else: ?>
                    <?php if ($summary_only): ?>
                        <!-- Summary view -->
                        <div class="report-summary">
                            <h2>Kopsavilkums</h2>
                            <div class="summary-stats">
                                <p><strong>Kopējais produktu skaits:</strong> <?= $summary['total_products'] ?></p>
                                <p><strong>Kopējais daudzums:</strong> <?= $summary['total_quantity'] ?></p>
                            </div>
                            <div class="table-container">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Plaukta vieta</th>
                                            <th>Produktu skaits</th>
                                            <th>Kopējais daudzums</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($summary['locations'] as $location => $stats): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($location) ?></td>
                                                <td><?= $stats['product_count'] ?></td>
                                                <td><?= $stats['total_quantity'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Detailed view -->
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produkts</th>
                                        <th>Kategorija</th>
                                        <th>Uzņēmums</th>
                                        <th>Daudzums</th>
                                        <th>Plaukta vieta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($report_items as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['product_name']) ?></td>
                                            <td><?= htmlspecialchars($item['category_name'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($item['company_name'] ?? '-') ?></td>
                                            <td><?= $item['quantity'] ?></td>
                                            <td><?= htmlspecialchars($item['shelf_location']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <div class="actions">
                    <a href="shelf_reports.php" class="btn btn-secondary">Atpakaļ uz atskaitēm</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 