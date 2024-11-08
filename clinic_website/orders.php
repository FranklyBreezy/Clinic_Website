<?php
session_start();
include('db.php');
include('header.php');

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if the user is an admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Handle order status update (for admin users)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    if ($is_admin) {
        $order_id = (int)$_POST['order_id'];
        $status = $_POST['status'];

        // Update order status in the orders table
        $update_sql = "UPDATE orders SET status = ? WHERE order_id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('si', $status, $order_id);

        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Order status updated successfully.</div>';
        } else {
            echo '<div class="alert alert-danger">Error updating order status: ' . $conn->error . '</div>';
        }
    } else {
        echo '<div class="alert alert-danger">You do not have permission to update the order status.</div>';
    }
}

// Handle order cancellation (for regular users and admins)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_order_id = (int)$_POST['cancel_order_id'];
    
    // Check if the user is allowed to cancel the order (either the order owner or an admin)
    $cancel_sql = "SELECT user_id, status FROM orders WHERE order_id = ?";
    $stmt = $conn->prepare($cancel_sql);
    $stmt->bind_param('i', $cancel_order_id);
    $stmt->execute();
    $cancel_result = $stmt->get_result();

    if ($cancel_result->num_rows > 0) {
        $cancel_row = $cancel_result->fetch_assoc();
        
        // Ensure the logged-in user is either the owner or an admin
        if ($cancel_row['user_id'] == $user_id || $is_admin) {
            if ($cancel_row['status'] == 'cancelled' || $cancel_row['status'] == 'delivered') {
                echo '<div class="alert alert-warning">This order cannot be cancelled because it is already cancelled or delivered.</div>';
            } else {
                // Cancel the order and update stock if necessary
                $conn->begin_transaction();
                try {
                    // Update order status to cancelled
                    $update_sql = "UPDATE orders SET status = 'cancelled' WHERE order_id = ?";
                    $stmt = $conn->prepare($update_sql);
                    $stmt->bind_param('i', $cancel_order_id);
                    $stmt->execute();
                    
                    // Fetch order items and restore stock
                    $items_sql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
                    $items_stmt = $conn->prepare($items_sql);
                    $items_stmt->bind_param('i', $cancel_order_id);
                    $items_stmt->execute();
                    $items_result = $items_stmt->get_result();
                    
                    while ($item = $items_result->fetch_assoc()) {
                        $product_id = $item['product_id'];
                        $quantity = $item['quantity'];
                        
                        // Restore stock
                        $restore_stock_sql = "UPDATE products SET stock_quantity = stock_quantity + ? WHERE product_id = ?";
                        $restore_stmt = $conn->prepare($restore_stock_sql);
                        $restore_stmt->bind_param('ii', $quantity, $product_id);
                        $restore_stmt->execute();
                    }
                    
                    // Commit transaction
                    $conn->commit();
                    echo '<div class="alert alert-warning">Order has been cancelled and stock has been updated.</div>';
                } catch (Exception $e) {
                    $conn->rollback();
                    echo '<div class="alert alert-danger">Error cancelling order: ' . $e->getMessage() . '</div>';
                }
            }
        } else {
            echo '<div class="alert alert-danger">You are not allowed to cancel this order.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Order not found.</div>';
    }
}

// Query to fetch orders based on user role
if ($is_admin) {
    // Admin view: fetch all orders
    $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, oi.quantity, p.name AS product_name, p.image_url, u.first_name, u.last_name 
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            JOIN users u ON o.user_id = u.user_id
            ORDER BY o.order_date DESC";
} else {
    // Regular user view: fetch only user's orders
    $sql = "SELECT o.order_id, o.order_date, o.total_amount, o.status, oi.quantity, p.name AS product_name, p.image_url 
            FROM orders o
            JOIN order_items oi ON o.order_id = oi.order_id
            JOIN products p ON oi.product_id = p.product_id
            WHERE o.user_id = $user_id
            ORDER BY o.order_date DESC";
}

$result = $conn->query($sql);

echo '<div class="container">';
if ($is_admin) {
    echo '<h2>All Orders</h2>';
} else {
    echo '<h2>Your Order History</h2>';
}

if ($result->num_rows > 0) {
    echo '<table class="table">';
    echo '<thead><tr>';
    
    if ($is_admin) {
        echo '<th>Customer</th>'; // Show customer name for admins
    }
    
    echo '<th>Order Date</th><th>Product</th><th>Image</th><th>Quantity</th><th>Total Amount</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
    
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        
        if ($is_admin) {
            // Display customer name for admins
            echo '<td>' . htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']) . '</td>';
        }
        
        // Display order details
        echo '<td>' . $row['order_date'] . '</td>';
        echo '<td>' . htmlspecialchars($row['product_name']) . '</td>';
        
        // Display product image
        if (!empty($row['image_url'])) {
            echo '<td><img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['product_name']) . '" class="img-fluid" style="max-width: 100px; height: auto;"></td>';
        } else {
            echo '<td>No image available</td>';
        }

        echo '<td>' . $row['quantity'] . '</td>';
        echo '<td>$' . number_format($row['total_amount'], 2) . '</td>';
        
        // Display order status and actions for admins
        $order_status = ucfirst($row['status']); // Capitalize the status for display
        echo '<td>' . $order_status . '</td>';
        echo '<td>';
        
        // Admins can update status
        if ($is_admin) {
            echo '<form method="POST" action="orders.php">';
            echo '<input type="hidden" name="order_id" value="' . $row['order_id'] . '">';
            echo '<select name="status" class="form-control">';
            echo '<option value="pending" ' . ($row['status'] == 'pending' ? 'selected' : '') . '>Pending</option>';
            echo '<option value="shipped" ' . ($row['status'] == 'shipped' ? 'selected' : '') . '>Shipped</option>';
            echo '<option value="delivered" ' . ($row['status'] == 'delivered' ? 'selected' : '') . '>Delivered</option>';
            echo '</select>';
            echo '<button type="submit" class="btn btn-success mt-2">Update Status</button>';
            echo '</form>';
        }
        
        // Allow cancellation only if the order is not already cancelled or delivered
        if ($row['status'] != 'cancelled' && $row['status'] != 'delivered') {
            echo '<form method="POST" action="orders.php" onsubmit="return confirm(\'Are you sure you want to cancel this order?\');">';
            echo '<input type="hidden" name="cancel_order_id" value="' . $row['order_id'] . '">';
            echo '<button type="submit" class="btn btn-danger mt-2">Cancel Order</button>';
            echo '</form>';
        } else {
            echo '<button class="btn btn-secondary mt-2" disabled>Order Cancelled/Delivered</button>';
        }
        
        echo '</td>';
        echo '</tr>';
    }
    
    echo '</tbody></table>';
} else {
    echo '<p>No orders found.</p>';
}
echo '</div>';

include('footer.php');
?>
