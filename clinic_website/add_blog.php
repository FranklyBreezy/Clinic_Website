<?php
session_start();
include('db.php');
include('header.php');

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="container"><p>You need to be an admin to view this page.</p></div>';
    include('includes/footer.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $author_id = $_SESSION['user_id']; // Admin who creates the blog post
    
    $sql = "INSERT INTO blog_posts (title, content, author_id, status) 
            VALUES ('$title', '$content', '$author_id', 'draft')";
    
    if ($conn->query($sql) === TRUE) {
        echo '<div class="alert alert-success">Blog post added successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error adding blog post: ' . $conn->error . '</div>';
    }
}
?>

<div class="container">
    <h2>Add New Blog Post</h2>
    <form method="POST">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea name="content" id="content" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Add Blog Post</button>
    </form>
</div>

<?php include('footer.php'); ?>
