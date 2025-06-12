<?php
include 'config.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrator', 'warehouse_worker'])) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize input
    $name = trim($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? intval($_POST['category_id']) : null;
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $company_id = !empty($_POST['company_id']) ? intval($_POST['company_id']) : null;
    $description = trim($_POST['description'] ?? '');
    $shelf_location = trim($_POST['shelf_location'] ?? '');

    // Validate input
    if (empty($name)) {
        $errors[] = "Produkta nosaukums ir obligāts";
    } elseif (strlen($name) < 2 || strlen($name) > 200) {
        $errors[] = "Produkta nosaukumam jābūt no 2 līdz 200 simboliem";
    }

    if (empty($category_id)) {
        $errors[] = "Kategorija ir obligāta";
    }

    if ($price <= 0) {
        $errors[] = "Cenai jābūt lielākai par 0";
    }

    if ($quantity < 0) {
        $errors[] = "Daudzumam jābūt 0 vai vairāk";
    }

    // If no errors, proceed with database insertion
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO products (name, category_id, company_id, price, quantity, shelf_location, description, created_by) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $name,
                $category_id,
                $company_id,
                $price,
                $quantity,
                $shelf_location,
                $description,
                $_SESSION['user_id']
            ]);

            $success = "Produkts veiksmīgi pievienots!";
            
            // Clear form data after successful submission
            $name = $category_id = $price = $quantity = $company_id = $description = $shelf_location = '';
            
        } catch (PDOException $e) {
            $errors[] = "Kļūda pievienojot produktu: " . $e->getMessage();
        }
    }
}

// Fetch categories for dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Kļūda ielādējot kategorijas: " . $e->getMessage();
    $categories = [];
}

// Fetch companies for dropdown
try {
    $stmt = $pdo->query("SELECT id, name FROM companies ORDER BY name");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Kļūda ielādējot uzņēmumus: " . $e->getMessage();
    $companies = [];
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Pievienot produktu</title>
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
                <a href="add_product.php" class="nav-item active">➕ Pievienot produktu</a>
                <a href="add_user.php" class="nav-item">➕ Pievienot lietotāju</a>
                <a href="users.php" class="nav-item">👥 Lietotāji</a>
                <a href="reports.php" class="nav-item">📊 Izveidot atskaiti</a>
                <a href="logout.php" class="nav-item">🚪 Iziet</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Pievienot jaunu produktu</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                    (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                </div>
            </div>
            
            <div class="content">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="name">Produkta nosaukums:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Kategorija:</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Izvēlieties kategoriju</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo ($category_id ?? '') == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Cena (EUR):</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo htmlspecialchars($price ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Daudzums:</label>
                        <input type="number" id="quantity" name="quantity" min="0" value="<?php echo htmlspecialchars($quantity ?? '0'); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_id">Uzņēmums:</label>
                        <select id="company_id" name="company_id">
                            <option value="">Izvēlieties uzņēmumu</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>" <?php echo ($company_id ?? '') == $company['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Apraksts:</label>
                        <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="shelf_location">Noliktavas vieta:</label>
                        <input type="text" id="shelf_location" name="shelf_location" value="<?php echo htmlspecialchars($shelf_location ?? ''); ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Pievienot produktu</button>
                        <a href="products.php" class="btn btn-secondary">Atcelt</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 