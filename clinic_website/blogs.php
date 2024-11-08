<?php
session_start();
include('db.php');
include('header.php');

// Fetch all blog posts
$sql = "SELECT * FROM blog_posts ORDER BY published_date DESC";
$result = $conn->query($sql);

echo '<div class="container"><h2>Blog Posts</h2>';
if ($result->num_rows > 0) {
    echo '<table class="table">';
    echo '<thead><tr><th>Title</th><th>Author</th><th>Status</th><th>Content</th><th>Actions</th></tr></thead><tbody>';
    
    while ($row = $result->fetch_assoc()) {
        // Fetch the author details
        $author_sql = "SELECT first_name, last_name FROM users WHERE user_id = " . $row['author_id'];
        $author_result = $conn->query($author_sql);
        $author = $author_result->fetch_assoc();
        
        // Display the blog post in the table
        echo '<tr>';
        // Link the title to the view_blog.php page
        echo '<td><a href="view_blog.php?post_id=' . $row['post_id'] . '">' . htmlspecialchars($row['title']) . '</a></td>';
        echo '<td>' . htmlspecialchars($author['first_name']) . ' ' . htmlspecialchars($author['last_name']) . '</td>';
        echo '<td>' . ucfirst($row['status']) . '</td>';
        // Show a snippet of content (but the full content will be on view_blog.php)
        echo '<td>' . htmlspecialchars(substr($row['content'], 0, 100)) . '...</td>';  // Truncate content to 100 characters

        // Show "Edit" and "Delete" buttons only to admins
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            echo '<td><a href="edit_blog.php?post_id=' . $row['post_id'] . '" class="btn btn-warning">Edit</a></td>';
        } else {
            echo '<td></td>';  // No action buttons for non-admins
        }

        echo '</tr>';
    }

    echo '</tbody></table>';
} else {
    echo '<p>No blog posts available.</p>';
}

// Show "Add New Blog Post" button only to admins
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    echo '<a href="add_blog.php" class="btn btn-primary">Add New Blog Post</a>';
}

echo '</div>';

include('footer.php');
?>
