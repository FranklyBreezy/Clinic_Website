<?php
session_start();
include('db.php');
include('header.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch products, appointments, etc. from the database
?>
<div class="container">
    <h2>Welcome to your Dashboard</h2>
    <p>What would you like to do today?</p>
    <a href="products.php" class="btn btn-primary">Order Products</a>
    <a href="appointments.php" class="btn btn-secondary">Schedule Appointment</a>
    <a href="orders.php" class="btn btn-info">Order History</a>
    <a href="blogs.php" class="btn btn-dark">View Blogs</a>
</div>

<?php include('footer.php'); ?>
