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
    <title>STASH - Plauktu atskaites</title>
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
                <a href="shelf_management.php" class="nav-item">📋 Plauktu pārvaldība</a>
                <a href="shelf_reports.php" class="nav-item active">📊 Plauktu atskaites</a>
                <a href="logout.php" class="nav-item">🚪 Iziet</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Plauktu atskaites</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                    (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                </div>
            </div>
            
            <div class="content">
                <div class="dashboard-grid">
                    <!-- Report Generation Section -->
                    <div class="dashboard-card">
                        <h3>Izveidot atskaiti</h3>
                        <div class="card-content">
                            <form method="POST" class="form" onsubmit="return false;">
                                <div class="form-group">
                                    <label for="report_type">Atskaites tips:</label>
                                    <select id="report_type" name="report_type" required>
                                        <option value="">Izvēlieties atskaites tipu</option>
                                        <option value="shelf_content">Plaukta saturs</option>
                                        <option value="empty_shelves">Tukšie plaukti</option>
                                        <option value="full_shelves">Pilnie plaukti</option>
                                        <option value="product_locations">Produktu izvietojumi</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="date_range">Datuma diapazons:</label>
                                    <div class="date-range">
                                        <input type="date" id="date_from" name="date_from" required>
                                        <span>līdz</span>
                                        <input type="date" id="date_to" name="date_to" required>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="shelf_section">Plaukta sekcija (neobligāts):</label>
                                    <input type="text" id="shelf_section" name="shelf_section" 
                                           placeholder="Piemēram: A vai B">
                                </div>
                                
                                <button type="submit" class="btn btn-primary" disabled>Ģenerēt atskaiti</button>
                            </form>
                        </div>
                    </div>

                    <!-- Recent Reports Section -->
                    <div class="dashboard-card">
                        <h3>Pēdējās atskaites</h3>
                        <div class="card-content">
                            <div class="recent-reports">
                                <p class="text-muted">Nav ģenerētu atskaišu</p>
                            </div>
                        </div>
                    </div>

                    <!-- Report Templates Section -->
                    <div class="dashboard-card">
                        <h3>Atskaišu veidnes</h3>
                        <div class="card-content">
                            <div class="report-templates">
                                <div class="template-item">
                                    <h4>Ikdienas plaukta pārskats</h4>
                                    <p>Kopējs pārskats par visiem plauktiem</p>
                                    <button class="btn btn-secondary" disabled>Izmantot veidni</button>
                                </div>
                                <div class="template-item">
                                    <h4>Nedēļas inventāra atskaite</h4>
                                    <p>Detalizēta atskaite par plauktu saturu</p>
                                    <button class="btn btn-secondary" disabled>Izmantot veidni</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html> 