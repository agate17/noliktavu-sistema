<?php
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

try {
    // Get all reports
    $query = "SELECT r.*, u.username as created_by_username 
              FROM shelf_reports r 
              LEFT JOIN users u ON r.created_by = u.id 
              ORDER BY r.created_at DESC";
    $stmt = $pdo->query($query);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Kļūda ielādējot atskaites: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plauktu atskaites - STASH</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Plauktu atskaites</h1>
                <div class="user-info">
                    Lietotājs: <strong><?= htmlspecialchars(getCurrentUsername()) ?></strong>
                    (<?= htmlspecialchars(getCurrentUserRole()) ?>)
                </div>
            </div>
            
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <?php if (empty($reports)): ?>
                    <p class="no-data">Nav atrasta neviena atskaite.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Atskaites nosaukums</th>
                                    <th>Veids</th>
                                    <th>Datums</th>
                                    <th>Izveidoja</th>
                                    <th>Darbības</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($report['name']) ?></td>
                                        <td>
                                            <?= $report['type'] === 'current' ? 
                                                'Pašreizējais stāvoklis' : 
                                                'Izmaiņu vēsture' ?>
                                        </td>
                                        <td>
                                            <?= date('d.m.Y H:i', strtotime($report['created_at'])) ?>
                                            <?php if ($report['date_from'] || $report['date_to']): ?>
                                                <br>
                                                <small>
                                                    <?php if ($report['date_from']): ?>
                                                        No: <?= date('d.m.Y', strtotime($report['date_from'])) ?>
                                                    <?php endif; ?>
                                                    <?php if ($report['date_to']): ?>
                                                        Līdz: <?= date('d.m.Y', strtotime($report['date_to'])) ?>
                                                    <?php endif; ?>
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= htmlspecialchars($report['created_by_username']) ?></td>
                                        <td>
                                            <a href="view_shelf_report.php?id=<?= $report['id'] ?>" 
                                               class="btn btn-sm">Skatīt</a>
                                            <?php if (isAdmin()): ?>
                                                <button class="btn btn-sm btn-danger delete-report" 
                                                        data-id="<?= $report['id'] ?>">Dzēst</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('.delete-report').click(function() {
            if (confirm('Vai tiešām vēlaties dzēst šo atskaiti?')) {
                const reportId = $(this).data('id');
                $.post('delete_shelf_report.php', { id: reportId }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Kļūda dzēšot atskaiti: ' + response.error);
                    }
                });
            }
        });
    });
    </script>
</body>
</html> 