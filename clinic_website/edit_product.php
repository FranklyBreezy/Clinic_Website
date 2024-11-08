<?php
session_start();
include('db.php');
include('header.php');

// Check if the user is an admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="container"><p>You need to be an admin to access this page.</p></div>';
    include('footer.php');
    exit();
}

// Check if post_id is provided
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];

    // Fetch the product details from the database
    $sql = "SELECT * FROM products WHERE product_id = $product_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Handle form submission to update product
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock_quantity = $_POST['stock_quantity'];
            $image_url = $_POST['image_url'];
            $category = $_POST['category'];

            // Update the product in the database
            $update_sql = "UPDATE products SET 
                            name = '$name', description = '$description', price = '$price', 
                            stock_quantity = '$stock_quantity', image_url = '$image_url', category = '$category'
                            WHERE product_id = $product_id";

            if ($conn->query($update_sql) === TRUE) {
                echo '<div class="container"><p>Product updated successfully!</p><a href="products.php" class="btn btn-primary">Back to Products</a></div>';
            } else {
                echo '<div class="container"><p>Error: ' . $conn->error . '</p></div>';
            }
        } else {
            // Display the form to edit the product
            echo '<div class="container">
                    <h2>Edit Product</h2>
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="name">Product Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="' . htmlspecialchars($product['name']) . '" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" class="form-control" required>' . htmlspecialchars($product['description']) . '</textarea>
                        </div>
                        <div class="form-group">
                            <label for="price">Price</label>
                            <input type="number" name="price" id="price" class="form-control" value="' . $product['price'] . '" step="0.01" required>
                        </div>
                        <div class="form-group">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" value="' . $product['stock_quantity'] . '" required>
                        </div>
                        <div class="form-group">
                            <label for="image_url">Image URL</label>
                            <input type="text" name="image_url" id="image_url" class="form-control" value="' . htmlspecialchars($product['image_url']) . '" required>
                        </div>
                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" name="category" id="category" class="form-control" value="' . htmlspecialchars($product['category']) . '" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Product</button>
                    </form>
                  </div>';
        }
    } else {
        echo '<div class="container"><p>Product not found.</p></div>';
    }
} else {
    echo '<div class="container"><p>No product specified.</p></div>';
}

include('footer.php');
?>
