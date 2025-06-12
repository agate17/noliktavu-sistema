<?php
include 'config.php';
include 'auth.php';

// Check if user is logged in and has appropriate permissions
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'administrator') {
    header('Location: dashboard.php');
    exit;
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Get form data
        $name = $_POST['name'];
        $category_id = $_POST['category_id'];
        $company_id = $_POST['company_id'];
        $price = $_POST['price'];
        $quantity = $_POST['quantity'];
        $shelf_location = $_POST['shelf_location'];
        $description = $_POST['description'];

        // Update product
        $stmt = $pdo->prepare("
            UPDATE products 
            SET name = :name,
                category_id = :category_id,
                company_id = :company_id,
                price = :price,
                quantity = :quantity,
                shelf_location = :shelf_location,
                description = :description,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = :id
        ");

        $stmt->execute([
            ':name' => $name,
            ':category_id' => $category_id ?: null,
            ':company_id' => $company_id ?: null,
            ':price' => $price,
            ':quantity' => $quantity,
            ':shelf_location' => $shelf_location,
            ':description' => $description,
            ':id' => $product_id
        ]);

        $pdo->commit();
        header('Location: dashboard.php?success=1');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Kļūda: " . $e->getMessage();
    }
}

// Get product data
try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, co.name as company_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        LEFT JOIN companies co ON p.company_id = co.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        header('Location: dashboard.php');
        exit;
    }

    // Get all categories for dropdown
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

    // Get all companies for dropdown
    $companies = $pdo->query("SELECT * FROM companies ORDER BY name")->fetchAll();

} catch (PDOException $e) {
    $error = "Kļūda ielādējot datus: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Rediģēt produktu</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php include 'includes/nav.php'; ?>
        
        <main class="main-content">
            <div class="header">
                <h1>Rediģēt produktu</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> 
                    (<?php echo htmlspecialchars($_SESSION['role']); ?>)
                </div>
            </div>
            
            <div class="content">
                <?php if (isset($error)): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST" class="form">
                    <div class="form-group">
                        <label for="name">Produkta nosaukums:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id">Kategorija:</label>
                        <select id="category_id" name="category_id">
                            <option value="">Izvēlieties kategoriju</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="company_id">Uzņēmums:</label>
                        <select id="company_id" name="company_id">
                            <option value="">Izvēlieties uzņēmumu</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo $company['id']; ?>" <?php echo $company['id'] == $product['company_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($company['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="price">Cena (EUR):</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo $product['price']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="quantity">Daudzums:</label>
                        <input type="number" id="quantity" name="quantity" min="0" value="<?php echo $product['quantity']; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="shelf_location">Plaukta vieta:</label>
                        <input type="text" id="shelf_location" name="shelf_location" value="<?php echo htmlspecialchars($product['shelf_location'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Apraksts:</label>
                        <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-success">Saglabāt izmaiņas</button>
                        <a href="dashboard.php" class="btn btn-secondary">Atcelt</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html> 