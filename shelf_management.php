<?php
include 'config.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrator', 'shelf_organizer'])) {
    header('Location: dashboard.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Plauktu pārvaldība</title>
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
                <a href="shelf_management.php" class="nav-item active">📋 Plauktu pārvaldība</a>
                <a href="shelf_reports.php" class="nav-item">📊 Plauktu atskaites</a>
                <a href="logout.php" class="nav-item">🚪 Iziet</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Plauktu pārvaldība</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                    (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                </div>
            </div>
            
            <div class="content">
                <div class="dashboard-grid">
                    <!-- Product Placement Section -->
                    <div class="dashboard-card">
                        <h3>Produktu izvietošana</h3>
                        <div class="card-content">
                            <form method="POST" class="form" onsubmit="return false;">
                                <div class="form-group">
                                    <label for="product_id">Produkts:</label>
                                    <select id="product_id" name="product_id" required>
                                        <option value="">Izvēlieties produktu</option>
                                        <!-- Products will be loaded dynamically -->
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="shelf_location">Plaukta vieta:</label>
                                    <input type="text" id="shelf_location" name="shelf_location" 
                                           placeholder="Piemēram: A-01-02" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="shelf_notes">Piezīmes:</label>
                                    <textarea id="shelf_notes" name="shelf_notes" rows="2" 
                                              placeholder="Piezīmes par izvietojumu"></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary" disabled>Atjaunināt izvietojumu</button>
                            </form>
                        </div>
                    </div>

                    <!-- Shelf Status Section -->
                    <div class="dashboard-card">
                        <h3>Plaukta statuss</h3>
                        <div class="card-content">
                            <div class="shelf-status">
                                <div class="status-item">
                                    <span class="status-label">Aktīvie plaukti:</span>
                                    <span class="status-value">0</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label">Produkti bez vietas:</span>
                                    <span class="status-value">0</span>
                                </div>
                                <div class="status-item">
                                    <span class="status-label">Pēdējā atjaunināšana:</span>
                                    <span class="status-value">-</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Updates Section -->
                    <div class="dashboard-card">
                        <h3>Pēdējās izmaiņas</h3>
                        <div class="card-content">
                            <div class="recent-updates">
                                <p class="text-muted">Nav veiktu izmaiņu</p>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications Section -->
                    <div class="dashboard-card">
                        <h3>Paziņojumi</h3>
                        <div class="card-content">
                            <form method="POST" class="form" onsubmit="return false;">
                                <div class="form-group">
                                    <label for="notification_type">Paziņojuma tips:</label>
                                    <select id="notification_type" name="notification_type" required>
                                        <option value="">Izvēlieties tipu</option>
                                        <option value="stock_low">Zems krājums</option>
                                        <option value="shelf_full">Plaukts pilns</option>
                                        <option value="reorganization">Pārorganizācija</option>
                                        <option value="other">Cits</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="notification_message">Ziņojums:</label>
                                    <textarea id="notification_message" name="notification_message" 
                                              rows="3" required></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-warning" disabled>Nosūtīt paziņojumu</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 