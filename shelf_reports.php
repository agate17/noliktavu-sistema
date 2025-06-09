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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

                <!-- Report Generation Form -->
                <div class="report-section">
                    <h2>Ģenerēt jaunu atskaiti</h2>
                    <form id="report-form" class="report-form">
                        <div class="form-group">
                            <label for="report_name">Atskaites nosaukums:</label>
                            <input type="text" id="report_name" name="report_name" required 
                                   placeholder="Ievadiet atskaites nosaukumu">
                        </div>
                        <div class="form-group">
                            <label for="report_type">Atskaites veids:</label>
                            <select id="report_type" name="report_type" required>
                                <option value="current">Pašreizējais plauktu stāvoklis</option>
                                <option value="changes">Plauktu izmaiņu vēsture</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_from">No datuma:</label>
                            <input type="date" id="date_from" name="date_from">
                        </div>
                        <div class="form-group">
                            <label for="date_to">Līdz datumam:</label>
                            <input type="date" id="date_to" name="date_to">
                        </div>
                        <button type="submit" class="btn">Ģenerēt atskaiti</button>
                    </form>
                </div>

                <!-- Reports List -->
                <div class="reports-list">
                    <h2>Esošās atskaites</h2>
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
            </div>
        </main>
    </div>

    <script>
    $(document).ready(function() {
        // Handle report form submission
        $('#report-form').submit(function(e) {
            e.preventDefault();
            const formData = $(this).serialize();
            
            $.ajax({
                url: 'generate_shelf_report.php',
                method: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        alert('Atskaite veiksmīgi ģenerēta!');
                        location.reload();
                    } else {
                        alert('Kļūda: ' + response.error);
                    }
                },
                error: function() {
                    alert('Kļūda ģenerējot atskaiti');
                }
            });
        });

        // Handle delete button click
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