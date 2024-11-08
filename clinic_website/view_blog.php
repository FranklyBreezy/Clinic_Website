<?php
session_start();
include('db.php');
include('header.php');

// Check if post_id is provided in the URL
if (isset($_GET['post_id'])) {
    $post_id = $_GET['post_id'];

    // Fetch the selected blog post from the database
    $sql = "SELECT * FROM blog_posts WHERE post_id = $post_id";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $author_sql = "SELECT first_name, last_name FROM users WHERE user_id = " . $row['author_id'];
        $author_result = $conn->query($author_sql);
        $author = $author_result->fetch_assoc();

        echo '<div class="container">';
        echo '<h2>' . htmlspecialchars($row['title']) . '</h2>';
        echo '<p><strong>Author:</strong> ' . htmlspecialchars($author['first_name']) . ' ' . htmlspecialchars($author['last_name']) . '</p>';
        echo '<p><strong>Status:</strong> ' . ucfirst($row['status']) . '</p>';
        echo '<p><strong>Published on:</strong> ' . date("F j, Y, g:i a", strtotime($row['published_date'])) . '</p>';
        echo '<hr>';
        echo '<p>' . nl2br(htmlspecialchars($row['content'])) . '</p>';  // Show full content
        echo '</div>';
    } else {
        echo '<p>Blog post not found.</p>';
    }
} else {
    echo '<p>No blog post specified.</p>';
}

include('footer.php');
?>
