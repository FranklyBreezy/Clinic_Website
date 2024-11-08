<?php
session_start();
include('db.php'); // Database connection

// Handle form submission for signup
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['signup'])) {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password
    $phone = $_POST['phone'];
    
    $sql = "INSERT INTO users (first_name, last_name, email, password_hash, phone) VALUES ('$first_name', '$last_name', '$email', '$password', '$phone')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Registration successful! You can now <a href='login.php'>login</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login / Signup</title>
    <!-- Link to Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <div class="row">
            <!-- Signup Form -->
            <div class="col-md-6 mb-4">
                <h2>Signup</h2>
                <form method="POST" action="">
                    <input type="text" name="first_name" class="form-control mb-3" placeholder="First Name" required><br>
                    <input type="text" name="last_name" class="form-control mb-3" placeholder="Last Name" required><br>
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required><br>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required><br>
                    <input type="text" name="phone" class="form-control mb-3" placeholder="Phone" required><br>
                    <button type="submit" name="signup" class="btn btn-primary">Sign Up</button>
                </form>
            </div>
            <!-- Login Form -->
            <div class="col-md-6 mb-4">
                <h2>Login</h2>
                <form method="POST" action="login_process.php">
                    <input type="email" name="email" class="form-control mb-3" placeholder="Email" required><br>
                    <input type="password" name="password" class="form-control mb-3" placeholder="Password" required><br>
                    <button type="submit" class="btn btn-primary">Login</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Link to Bootstrap JS and Bundle (includes Popper.js) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
