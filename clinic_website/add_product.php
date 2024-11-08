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

// Handle form submission to add a new product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stock_quantity = $_POST['stock_quantity'];
    $image_url = $_POST['image_url'];
    $category = $_POST['category'];

    // Insert the new product into the database
    $sql = "INSERT INTO products (name, description, price, stock_quantity, image_url, category) 
            VALUES ('$name', '$description', '$price', '$stock_quantity', '$image_url', '$category')";

    if ($conn->query($sql) === TRUE) {
        echo '<div class="container"><p>New product added successfully!</p><a href="products.php" class="btn btn-primary">Back to Products</a></div>';
    } else {
        echo '<div class="container"><p>Error: ' . $conn->error . '</p></div>';
    }
} else {
    // Display the form to add a new product
    echo '<div class="container">
            <h2>Add New Product</h2>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" required></textarea>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" name="price" id="price" class="form-control" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="stock_quantity">Stock Quantity</label>
                    <input type="number" name="stock_quantity" id="stock_quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="image_url">Image URL</label>
                    <input type="text" name="image_url" id="image_url" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" name="category" id="category" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary">Add Product</button>
            </form>
          </div>';
}

include('footer.php');
?>
