<?php
require_once 'config.php';
require_once 'auth.php';

// Check if user is logged in and has appropriate permissions
if (!isLoggedIn() || !hasRole(['administrator', 'plauktu_kartotajs'])) {
    header('Location: login.php');
    exit();
}

// Get all products with their current shelf locations
try {
    $query = "SELECT p.*, c.name as category_name, co.name as company_name 
              FROM products p 
              LEFT JOIN categories c ON p.category_id = c.id 
              LEFT JOIN companies co ON p.company_id = co.id";
    
    // Add filter if shelf location is selected
    if (isset($_GET['shelf_location']) && !empty($_GET['shelf_location'])) {
        $query .= " WHERE p.shelf_location = :shelf_location";
    }
    
    $query .= " ORDER BY p.shelf_location, p.name";
    
    $stmt = $pdo->prepare($query);
    
    // Bind the shelf location parameter if it exists
    if (isset($_GET['shelf_location']) && !empty($_GET['shelf_location'])) {
        $stmt->bindParam(':shelf_location', $_GET['shelf_location']);
    }
    
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get all unique shelf locations for the filter
    $locations = $pdo->query("SELECT DISTINCT shelf_location FROM products WHERE shelf_location IS NOT NULL ORDER BY shelf_location")->fetchAll(PDO::FETCH_COLUMN);

    // Get shelf utilization statistics
    $shelfStats = $pdo->query("
        SELECT 
            shelf_location,
            COUNT(*) as product_count,
            SUM(quantity) as total_items
        FROM products 
        WHERE shelf_location IS NOT NULL 
        GROUP BY shelf_location
        ORDER BY shelf_location
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error = "Kļūda ielādējot datus: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plauktu pārvaldība - STASH</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Plauktu pārvaldība</h1>
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
                    <h2>Ģenerēt atskaiti</h2>
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

                <!-- Shelf Statistics -->
                <div class="shelf-stats">
                    <h2>Plauktu statistika</h2>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Plaukta vieta</th>
                                    <th>Produktu skaits</th>
                                    <th>Kopējais daudzums</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($shelfStats as $stat): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($stat['shelf_location']) ?></td>
                                        <td><?= $stat['product_count'] ?></td>
                                        <td><?= $stat['total_items'] ?></td>
                                        <td>
                                            <?php
                                            $status = 'available';
                                            if ($stat['product_count'] > 20) {
                                                $status = 'full';
                                            } elseif ($stat['product_count'] > 10) {
                                                $status = 'warning';
                                            }
                                            ?>
                                            <span class="status-badge status-<?= $status ?>">
                                                <?= $status === 'full' ? 'Pilns' : ($status === 'warning' ? 'Gandrīz pilns' : 'Pieejams') ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Filter form -->
                <form method="GET" class="filter-form">
                    <div class="form-group">
                        <label for="shelf_location">Plaukta vieta:</label>
                        <select name="shelf_location" id="shelf_location">
                            <option value="">Visas vietas</option>
                            <?php foreach ($locations as $location): ?>
                                <option value="<?= htmlspecialchars($location) ?>" <?= isset($_GET['shelf_location']) && $_GET['shelf_location'] === $location ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($location) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">Filtrēt</button>
                    <a href="shelf_management.php" class="btn btn-secondary">Atiestatīt</a>
                </form>

                <!-- Bulk Actions -->
                <div class="bulk-actions">
                    <h3>Masveida darbības</h3>
                    <form id="bulk-update-form" class="bulk-form">
                        <div class="form-group">
                            <label for="bulk_shelf_location">Jauna plaukta vieta:</label>
                            <input type="text" id="bulk_shelf_location" name="bulk_shelf_location" placeholder="Ievadiet plaukta vietu">
                        </div>
                        <button type="submit" class="btn" id="bulk-update-btn" disabled>Atjaunot izvēlētās vietas</button>
                    </form>
                </div>

                <?php if (empty($products)): ?>
                    <p class="no-data">Nav atrasts neviens produkts.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Produkts</th>
                                    <th>Kategorija</th>
                                    <th>Uzņēmums</th>
                                    <th>Daudzums</th>
                                    <th>Plaukta vieta</th>
                                    <th>Darbības</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td>
                                            <input type="checkbox" class="product-select" data-product-id="<?= $product['id'] ?>">
                                        </td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= htmlspecialchars($product['category_name'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($product['company_name'] ?? '-') ?></td>
                                        <td><?= $product['quantity'] ?></td>
                                        <td>
                                            <span class="current-location"><?= htmlspecialchars($product['shelf_location'] ?? '-') ?></span>
                                            <form class="location-form" style="display: none;">
                                                <input type="text" name="shelf_location" value="<?= htmlspecialchars($product['shelf_location'] ?? '') ?>" 
                                                       placeholder="Ievadiet plaukta vietu" required>
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <button type="submit" class="btn btn-sm">Saglabāt</button>
                                                <button type="button" class="btn btn-sm btn-secondary cancel-edit">Atcelt</button>
                                            </form>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm edit-location">Rediģēt</button>
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

    <script>
    $(document).ready(function() {
        // Handle select all checkbox
        $('#select-all').change(function() {
            $('.product-select').prop('checked', $(this).prop('checked'));
            updateBulkUpdateButton();
        });

        // Handle individual checkboxes
        $('.product-select').change(function() {
            updateBulkUpdateButton();
        });

        // Update bulk update button state
        function updateBulkUpdateButton() {
            const checkedCount = $('.product-select:checked').length;
            $('#bulk-update-btn').prop('disabled', checkedCount === 0);
        }

        // Handle bulk update form submission
        $('#bulk-update-form').submit(function(e) {
            e.preventDefault();
            const newLocation = $('#bulk_shelf_location').val();
            const selectedProducts = $('.product-select:checked').map(function() {
                return $(this).data('product-id');
            }).get();

            if (selectedProducts.length === 0) {
                alert('Lūdzu, izvēlieties vismaz vienu produktu');
                return;
            }

            if (!newLocation) {
                alert('Lūdzu, ievadiet jaunu plaukta vietu');
                return;
            }

            // Update each selected product
            let completed = 0;
            selectedProducts.forEach(function(productId) {
                $.ajax({
                    url: 'update_shelf_location.php',
                    method: 'POST',
                    data: {
                        product_id: productId,
                        shelf_location: newLocation,
                        notes: 'Masveida plaukta vietas maiņa'
                    },
                    success: function(response) {
                        if (response.success) {
                            completed++;
                            if (completed === selectedProducts.length) {
                                location.reload();
                            }
                        } else {
                            alert('Kļūda: ' + response.error);
                        }
                    },
                    error: function() {
                        alert('Kļūda saglabājot izmaiņas');
                    }
                });
            });
        });

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
                        window.location.href = 'shelf_reports.php';
                    } else {
                        alert('Kļūda: ' + response.error);
                    }
                },
                error: function() {
                    alert('Kļūda ģenerējot atskaiti');
                }
            });
        });

        // Handle edit button click
        $('.edit-location').click(function() {
            const row = $(this).closest('tr');
            row.find('.current-location').hide();
            row.find('.location-form').show();
            $(this).hide();
        });

        // Handle cancel button click
        $('.cancel-edit').click(function() {
            const row = $(this).closest('tr');
            row.find('.current-location').show();
            row.find('.location-form').hide();
            row.find('.edit-location').show();
        });

        // Handle form submission
        $('.location-form').submit(function(e) {
            e.preventDefault();
            const form = $(this);
            const productId = form.find('input[name="product_id"]').val();
            const newLocation = form.find('input[name="shelf_location"]').val();
            const row = form.closest('tr');
            const currentLocation = row.find('.current-location').text();

            $.ajax({
                url: 'update_shelf_location.php',
                method: 'POST',
                data: {
                    product_id: productId,
                    shelf_location: newLocation,
                    notes: 'Plaukta vietas maiņa'
                },
                success: function(response) {
                    if (response.success) {
                        row.find('.current-location').text(newLocation || '-').show();
                        form.hide();
                        row.find('.edit-location').show();
                        // Refresh the page to update the filter options
                        location.reload();
                    } else {
                        alert('Kļūda: ' + response.error);
                    }
                },
                error: function() {
                    alert('Kļūda saglabājot izmaiņas');
                }
            });
        });
    });
    </script>
</body>
</html> 