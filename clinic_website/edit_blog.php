<?php
session_start();
include('db.php');
include('header.php');

// Check if the user is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo '<div class="container"><p>You need to be an admin to view this page.</p></div>';
    include('footer.php');
    exit();
}

// Check if post_id is provided in the URL
if (!isset($_GET['post_id']) || empty($_GET['post_id'])) {
    echo '<div class="container"><p>Invalid blog post ID.</p></div>';
    include('footer.php');
    exit();
}

// Get the blog post ID from the URL
$post_id = $_GET['post_id'];

// Fetch the blog post from the database
$sql = "SELECT * FROM blog_posts WHERE post_id = $post_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo '<div class="container"><p>Blog post not found.</p></div>';
    include('footer.php');
    exit();
}

$post = $result->fetch_assoc();

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $status = $_POST['status'];

    // Update the blog post in the database
    $update_sql = "UPDATE blog_posts SET title = ?, content = ?, status = ? WHERE post_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('sssi', $title, $content, $status, $post_id);
    
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Blog post updated successfully!</div>';
        // Optionally, redirect after successful update
        // header('Location: blogs.php');
        // exit();
    } else {
        echo '<div class="alert alert-danger">Error updating blog post: ' . $conn->error . '</div>';
    }
}

?>

<div class="container">
    <h2>Edit Blog Post</h2>
    <form method="POST">
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" name="title" id="title" class="form-control" value="<?php echo htmlspecialchars($post['title']); ?>" required>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea name="content" id="content" class="form-control" rows="5" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        </div>
        <div class="form-group">
            <label for="status">Status</label>
            <select name="status" id="status" class="form-control" required>
                <option value="draft" <?php echo ($post['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                <option value="published" <?php echo ($post['status'] == 'published') ? 'selected' : ''; ?>>Published</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Update Blog Post</button>
    </form>
</div>

<?php include('footer.php'); ?>
