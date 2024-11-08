<?php
session_start();
include('db.php');
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get product details
$product_id = $_GET['product_id'];
$sql = "SELECT * FROM products WHERE product_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);  // Binding the product_id to avoid SQL injection
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $quantity = $_POST['quantity'];
        
        if ($quantity <= $product['stock_quantity']) {
            // Begin a transaction to ensure all changes happen together
            $conn->begin_transaction();

            try {
                $user_id = $_SESSION['user_id'];
                $total_amount = $product['price'] * $quantity;

                // Insert the order into the orders table
                $order_sql = "INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')";
                $order_stmt = $conn->prepare($order_sql);
                $order_stmt->bind_param('id', $user_id, $total_amount);
                $order_stmt->execute();

                // Check if the order insert was successful
                if ($order_stmt->affected_rows > 0) {
                    $order_id = $order_stmt->insert_id; // Get the ID of the newly inserted order
                    
                    // Insert the order item (the product being ordered)
                    $order_item_sql = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                    $order_item_stmt = $conn->prepare($order_item_sql);
                    $order_item_stmt->bind_param('iiid', $order_id, $product_id, $quantity, $product['price']);
                    $order_item_stmt->execute();
                    
                    // Check if the order item insert was successful
                    if ($order_item_stmt->affected_rows > 0) {
                        // Reduce the stock quantity in the products table
                        $new_stock = $product['stock_quantity'] - $quantity;
                        $update_stock_sql = "UPDATE products SET stock_quantity = ? WHERE product_id = ?";
                        $update_stock_stmt = $conn->prepare($update_stock_sql);
                        $update_stock_stmt->bind_param('ii', $new_stock, $product_id);
                        $update_stock_stmt->execute();
                        
                        // Commit the transaction if everything was successful
                        $conn->commit();
                        
                        echo '<div class="alert alert-success">Order placed successfully!</div>';
                    } else {
                        throw new Exception("Failed to insert order item.");
                    }
                } else {
                    throw new Exception("Failed to insert order.");
                }
            } catch (Exception $e) {
                // If any error occurs, rollback the transaction
                $conn->rollback();
                echo '<div class="alert alert-danger">Error placing order: ' . $e->getMessage() . '</div>';
            }
        } else {
            echo '<div class="alert alert-warning">Not enough stock available.</div>';
        }
    }
    ?>
    <div class="container">
        <h2>Order Product: <?php echo htmlspecialchars($product['name']); ?></h2>
        <form method="POST">
            <label for="quantity">Quantity:</label>
            <input type="number" name="quantity" id="quantity" class="form-control" min="1" max="<?php echo $product['stock_quantity']; ?>" required>
            <button type="submit" class="btn btn-primary mt-3">Order</button>
        </form>
    </div>
    <?php
} else {
    echo '<div class="container"><p>Product not found.</p></div>';
}

include('footer.php');
?>
