<?php
session_start();
include('db.php');
include('header.php');

// Check if the user is an admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Fetch all products with stock greater than 0
$sql = "SELECT * FROM products WHERE stock_quantity > 0";
$result = $conn->query($sql);

echo '<div class="container">';
echo '<h2>Products</h2>';

if ($is_admin) {
    echo '<a href="add_product.php" class="btn btn-primary mb-3">Add New Product</a>'; // Admin-only Add Product button
}

if ($result->num_rows > 0) {
    echo '<table class="table">';
    echo '<thead><tr><th>Name</th><th>Description</th><th>Price</th><th>Stock</th><th>Image</th><th>Action</th></tr></thead><tbody>';
    
    while ($row = $result->fetch_assoc()) {
        echo '<tr>';
        
        // Product Name
        echo '<td>' . htmlspecialchars($row['name']) . '</td>';
        
        // Product Description
        echo '<td>' . htmlspecialchars($row['description']) . '</td>';
        
        // Product Price
        echo '<td>$' . number_format($row['price'], 2) . '</td>';
        
        // Product Stock Quantity
        echo '<td>' . $row['stock_quantity'] . '</td>';
        
        // Display Product Image
        if (!empty($row['image_url'])) {
            echo '<td><img src="' . htmlspecialchars($row['image_url']) . '" alt="' . htmlspecialchars($row['name']) . '" class="img-fluid" style="max-width: 100px; height: auto;"></td>';
        } else {
            echo '<td>No image available</td>';
        }
        
        // Action Buttons: "Order" for all users, "Edit" for admins
        echo '<td><a href="order_product.php?product_id=' . $row['product_id'] . '" class="btn btn-primary">Order</a> ';
        
        if ($is_admin) {
            echo '<a href="edit_product.php?product_id=' . $row['product_id'] . '" class="btn btn-warning">Edit</a>';
        }
        
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    echo '<p>No products available at the moment.</p>';
}

echo '</div>';
include('footer.php');
?>
