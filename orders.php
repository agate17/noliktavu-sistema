<?php
// orders.php - Orders management for warehouse workers
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$role = $_SESSION['role'];
$username = $_SESSION['username'];

// Check if user has permission to access orders
if (!in_array($role, ['administrator', 'warehouse_worker'])) {
    header('Location: dashboard.php');
    exit;
}

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create_order') {
        $customer_name = trim($_POST['customer_name'] ?? '');
        $customer_email = trim($_POST['customer_email'] ?? '');
        $customer_phone = trim($_POST['customer_phone'] ?? '');
        $order_items = $_POST['order_items'] ?? [];
        
        if (empty($customer_name) || empty($customer_email) || empty($order_items)) {
            $error = "Lūdzu, aizpildiet visus obligātos laukus un pievienojiet vismaz vienu produktu.";
        } else {
            try {
                $pdo->beginTransaction();
                
                // Create order
                $stmt = $pdo->prepare("INSERT INTO orders (customer_name, customer_email, customer_phone, status, created_by, created_at) VALUES (?, ?, ?, 'pending', ?, NOW())");
                $stmt->execute([$customer_name, $customer_email, $customer_phone, $_SESSION['user_id']]);
                $order_id = $pdo->lastInsertId();
                
                // Add order items
                $total_amount = 0;
                foreach ($order_items as $item) {
                    if (!empty($item['product_id']) && !empty($item['quantity']) && $item['quantity'] > 0) {
                        // Get product info
                        $stmt = $pdo->prepare("SELECT name, price, quantity FROM products WHERE id = ?");
                        $stmt->execute([$item['product_id']]);
                        $product = $stmt->fetch();
                        
                        if ($product) {
                            $quantity = intval($item['quantity']);
                            $unit_price = $product['price'];
                            $line_total = $quantity * $unit_price;
                            $total_amount += $line_total;
                            
                            // Check if enough stock
                            if ($product['quantity'] < $quantity) {
                                throw new Exception("Produktam '{$product['name']}' nav pietiekami daudz krājuma. Pieejams: {$product['quantity']}, nepieciešams: {$quantity}");
                            }
                            
                            // Insert order item
                            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?, ?)");
                            $stmt->execute([$order_id, $item['product_id'], $product['name'], $quantity, $unit_price, $line_total]);
                        }
                    }
                }
                
                // Update order total
                $stmt = $pdo->prepare("UPDATE orders SET total_amount = ? WHERE id = ?");
                $stmt->execute([$total_amount, $order_id]);
                
                $pdo->commit();
                $message = "Pasūtījums #$order_id veiksmīgi izveidots!";
                
            } catch (Exception $e) {
                $pdo->rollback();
                $error = "Kļūda veidojot pasūtījumu: " . $e->getMessage();
            }
        }
    } elseif ($action === 'update_status') {
        $order_id = $_POST['order_id'] ?? '';
        $new_status = $_POST['new_status'] ?? '';
        
        if ($order_id && $new_status) {
            try {
                $stmt = $pdo->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?");
                $stmt->execute([$new_status, $order_id]);
                
                // If status is fulfilled, reduce product quantities
                if ($new_status === 'fulfilled') {
                    $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                    $stmt->execute([$order_id]);
                    $items = $stmt->fetchAll();
                    
                    foreach ($items as $item) {
                        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
                        $stmt->execute([$item['quantity'], $item['product_id']]);
                    }
                }
                
                $message = "Pasūtījuma statuss atjaunināts!";
            } catch (PDOException $e) {
                $error = "Kļūda atjauninot statusu: " . $e->getMessage();
            }
        }
    }
}

// Get all orders
try {
    $stmt = $pdo->query("
        SELECT o.*, u.username as created_by_name
        FROM orders o
        LEFT JOIN users u ON o.created_by = u.id
        ORDER BY o.created_at DESC
    ");
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
    $error = "Kļūda ielādējot pasūtījumus: " . $e->getMessage();
}

// Get all products for order creation
try {
    $stmt = $pdo->query("SELECT id, name, price, quantity FROM products WHERE quantity > 0 ORDER BY name");
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STASH - Pasūtījumi</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .order-form {
            background: #fff;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .order-items {
            margin-top: 1rem;
        }
        
        .order-item {
            display: grid;
            grid-template-columns: 1fr 100px 80px;
            gap: 1rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .order-item select,
        .order-item input {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .remove-item {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .add-item {
            background: #28a745;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 1rem;
        }
        
        .order-status {
            padding: 0.25rem 0.5rem;
            border-radius: 3px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-pending {
            background-color: #fff7e6;
            color: #fa8c16;
        }
        
        .status-processing {
            background-color: #e6f7ff;
            color: #1890ff;
        }
        
        .status-fulfilled {
            background-color: #e3fcef;
            color: #00a854;
        }
        
        .status-cancelled {
            background-color: #fff1f0;
            color: #f5222d;
        }
        
        .order-details {
            cursor: pointer;
        }
        
        .order-items-detail {
            display: none;
            margin-top: 1rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 4px;
        }
        
        .customer-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <h2>📦 STASH</h2>
            </div>
            
            <nav class="nav-menu">
                <a href="dashboard.php" class="nav-item">
                    🏠 Sākums
                </a>
                
                <?php if ($role == 'administrator'): ?>
                    <a href="add_product.php" class="nav-item">
                        ➕ Pievienot produktu
                    </a>
                    <a href="add_user.php" class="nav-item">
                        ➕ Pievienot lietotāju
                    </a>
                    <a href="users.php" class="nav-item">
                        👥 Lietotāji
                    </a>
                    <a href="orders.php" class="nav-item active">
                        🚚 Pasūtījumi
                    </a>
                    <a href="reports.php" class="nav-item">
                        📊 Izveidot atskaiti
                    </a>
                <?php elseif ($role == 'warehouse_worker'): ?>
                    <a href="orders.php" class="nav-item active">
                        🚚 Veikt pasūtījumu
                    </a>
                    <a href="reports.php" class="nav-item">
                        📊 Izveidot atskaiti
                    </a>
                <?php endif; ?>
                
                <a href="logout.php" class="nav-item">
                    🚪 Iziet
                </a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="header">
                <h1>Pasūtījumu pārvaldība</h1>
                <div class="user-info">
                    Lietotājs: <strong><?php echo htmlspecialchars($username); ?></strong> 
                    (<?php echo ucfirst(str_replace('_', ' ', $role)); ?>)
                </div>
            </div>
            
            <div class="content">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <!-- Create New Order Form -->
                <div class="order-form">
                    <h2>Izveidot jaunu pasūtījumu</h2>
                    
                    <form method="POST" id="orderForm">
                        <input type="hidden" name="action" value="create_order">
                        
                        <!-- Customer Information -->
                        <div class="customer-form">
                            <div class="form-group">
                                <label for="customer_name">Klienta vārds *</label>
                                <input type="text" name="customer_name" id="customer_name" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_email">E-pasts *</label>
                                <input type="email" name="customer_email" id="customer_email" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="customer_phone">Telefons</label>
                                <input type="tel" name="customer_phone" id="customer_phone">
                            </div>
                        </div>
                        
                        <!-- Order Items -->
                        <div class="order-items">
                            <h3>Pasūtījuma produkti</h3>
                            <div id="orderItems">
                                <div class="order-item">
                                    <div class="form-group">
                                        <label>Produkts</label>
                                        <select name="order_items[0][product_id]" required>
                                            <option value="">Izvēlieties produktu</option>
                                            <?php foreach ($products as $product): ?>
                                                <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['quantity']; ?>">
                                                    <?php echo htmlspecialchars($product['name']); ?> - €<?php echo number_format($product['price'], 2); ?> (Krājumā: <?php echo $product['quantity']; ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>Daudzums</label>
                                        <input type="number" name="order_items[0][quantity]" min="1" value="1" required>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="button" class="remove-item" onclick="removeOrderItem(this)">Noņemt</button>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="add-item" onclick="addOrderItem()">Pievienot produktu</button>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-success">Izveidot pasūtījumu</button>
                        </div>
                    </form>
                </div>
                
                <!-- Orders List -->
                <div class="table-container">
                    <h2>Visi pasūtījumi</h2>
                    <table class="products-table">
                        <thead>
                            <tr>
                                <th>Pasūtījuma ID</th>
                                <th>Klients</th>
                                <th>E-pasts</th>
                                <th>Telefons</th>
                                <th>Kopējā summa</th>
                                <th>Statuss</th>
                                <th>Izveidoja</th>
                                <th>Datums</th>
                                <th>Darbības</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr class="order-details" onclick="toggleOrderDetails(<?php echo $order['id']; ?>)">
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_email']); ?></td>
                                    <td><?php echo htmlspecialchars($order['customer_phone'] ?? 'Nav'); ?></td>
                                    <td>€<?php echo number_format($order['total_amount'], 2); ?></td>
                                    <td>
                                        <span class="order-status status-<?php echo $order['status']; ?>">
                                            <?php 
                                            $status_labels = [
                                                'pending' => 'Gaidošs',
                                                'processing' => 'Apstrādē',
                                                'fulfilled' => 'Izpildīts',
                                                'cancelled' => 'Atcelts'
                                            ];
                                            echo $status_labels[$order['status']] ?? $order['status'];
                                            ?>
                                        </span>
                                    </td>
<td><?php echo htmlspecialchars($order['created_by_name'] ?? 'Nav'); ?></td>
<td><?php echo date('d.m.Y H:i', strtotime($order['created_at'])); ?></td>
<td onclick="event.stopPropagation()">
    <!-- Status dropdown -->
    <form method="POST" style="display:inline;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
        <select name="new_status" onchange="this.form.submit()">
            <option value="">Mainīt statusu</option>
            <option value="pending"    <?php if($order['status']==='pending')    echo 'selected'; ?>>Gaidošs</option>
            <option value="processing" <?php if($order['status']==='processing') echo 'selected'; ?>>Apstrādē</option>
            <option value="fulfilled"  <?php if($order['status']==='fulfilled')  echo 'selected'; ?>>Izpildīts</option>
            <option value="cancelled"  <?php if($order['status']==='cancelled')  echo 'selected'; ?>>Atcelts</option>
        </select>
    </form>

    <!-- Edit link -->
    <a href="order_edit.php?id=<?php echo $order['id']; ?>" class="btn btn-secondary" style="margin-left:8px;">
        Rediģēt
    </a>
</td>


                                </tr>
                                <tr id="order-items-<?php echo $order['id']; ?>" class="order-items-detail">
                                    <td colspan="9">
                                        <h4>Pasūtījuma produkti:</h4>
                                        <?php
                                        try {
                                            $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                            $stmt->execute([$order['id']]);
                                            $items = $stmt->fetchAll();
                                            
                                            if ($items): ?>
                                                <table style="width: 100%; margin-top: 10px;">
                                                    <thead>
                                                        <tr>
                                                            <th>Produkts</th>
                                                            <th>Daudzums</th>
                                                            <th>Vienības cena</th>
                                                            <th>Kopā</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php foreach ($items as $item): ?>
                                                            <tr>
                                                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                                <td><?php echo $item['quantity']; ?></td>
                                                                <td>€<?php echo number_format($item['unit_price'], 2); ?></td>
                                                                <td>€<?php echo number_format($item['line_total'], 2); ?></td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </tbody>
                                                </table>
                                            <?php else: ?>
                                                <p>Nav produktu šajā pasūtījumā.</p>
                                            <?php endif;
                                        } catch (PDOException $e) {
                                            echo "<p>Kļūda ielādējot produktus: " . htmlspecialchars($e->getMessage()) . "</p>";
                                        }
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        let itemCount = 1;
        
        function addOrderItem() {
            const orderItems = document.getElementById('orderItems');
            const newItem = document.createElement('div');
            newItem.className = 'order-item';
            newItem.innerHTML = `
                <div class="form-group">
                    <label>Produkts</label>
                    <select name="order_items[${itemCount}][product_id]" required>
                        <option value="">Izvēlieties produktu</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['id']; ?>" data-price="<?php echo $product['price']; ?>" data-stock="<?php echo $product['quantity']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?> - €<?php echo number_format($product['price'], 2); ?> (Krājumā: <?php echo $product['quantity']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Daudzums</label>
                    <input type="number" name="order_items[${itemCount}][quantity]" min="1" value="1" required>
                </div>
                
                <div class="form-group">
                    <label>&nbsp;</label>
                    <button type="button" class="remove-item" onclick="removeOrderItem(this)">Noņemt</button>
                </div>
            `;
            orderItems.appendChild(newItem);
            itemCount++;
        }
        
        function removeOrderItem(button) {
            const orderItems = document.getElementById('orderItems');
            if (orderItems.children.length > 1) {
                button.closest('.order-item').remove();
            } else {
                alert('Jābūt vismaz vienam produktam pasūtījumā!');
            }
        }
        
        function toggleOrderDetails(orderId) {
            const detailsRow = document.getElementById('order-items-' + orderId);
            if (detailsRow.style.display === 'none' || detailsRow.style.display === '') {
                detailsRow.style.display = 'table-row';
            } else {
                detailsRow.style.display = 'none';
            }
        }
        
        // Validate stock when quantity changes
        document.addEventListener('change', function(e) {
            if (e.target.type === 'number' && e.target.name.includes('quantity')) {
                const orderItem = e.target.closest('.order-item');
                const productSelect = orderItem.querySelector('select');
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                
                if (selectedOption && selectedOption.dataset.stock) {
                    const maxStock = parseInt(selectedOption.dataset.stock);
                    const requestedQty = parseInt(e.target.value);
                    
                    if (requestedQty > maxStock) {
                        alert(`Nepietiekams krājums! Pieejams: ${maxStock}`);
                        e.target.value = maxStock;
                    }
                }
            }
        });
    </script>
</body>
</html>