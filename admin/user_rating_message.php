<?php
include '../components/connect.php';

session_start();

$admin_id = $_SESSION['admin_id'];

// Check for a delete request

if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    // Log the ID being deleted for debugging
    error_log("Attempting to delete comment with ID: " . $delete_id); // Logs to the server's error log

    // Validate that the comment exists before deleting
    $check_comment = $conn->prepare("SELECT * FROM `comments` WHERE id = ?");
    $check_comment->execute([$delete_id]);

    if ($check_comment->rowCount() > 0) {
        // Only delete the comment with the provided ID
        $delete_comment = $conn->prepare("DELETE FROM `comments` WHERE id = ?");
        $delete_comment->execute([$delete_id]);

        // Set a success message
        $_SESSION['message'] = 'Comment deleted successfully';
    } else {
        // Set an error message if the comment does not exist
        $_SESSION['error'] = 'Comment not found';
    }

    // Redirect back to the comments page
    header('location:user_rating_message.php');
    exit; // Prevent further code execution after redirect
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="../css/adminstyle.css">
    
    <style>
        body {
            background-color: #121212;
            color: #f1f1f1;
            font-size: 16px;
        }
        .heading {
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            color: #ffffff;
        }
        .comments {
            width: 90%;
            margin: 0 auto;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #1e1e1e;
            margin-bottom: 20px;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border: 1px solid #333;
        }
        th {
            background-color: #333;
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #2a2a2a;
        }
        .delete-btn {
            background-color: #e74c3c;
            color: #ffffff;
            padding: 10px 15px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .notification {
            text-align: center;
            color: #e74c3c;
        }
    </style>
</head>

<body>

<?php include '../components/admin_header.php'; ?>

<section class="comments">

    <h1 class="heading">User Comments</h1>

    <!-- Display messages for feedback -->
    <div class="notification">
        <?php
        if (isset($_SESSION['message'])) {
            echo $_SESSION['message'];
            unset($_SESSION['message']);
        }
        if (isset($_SESSION['error'])) {
            echo $_SESSION['error'];
            unset($_SESSION['error']);
        }
        ?>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>User Name</th>
                    <th>Product</th>
                    <th>Comment</th>
                    <th>Date</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Select all comments along with user and product names
                $select_comments = $conn->prepare("SELECT c.*, u.name AS user_name, p.name AS product_name FROM `comments` c JOIN `users` u ON c.user_id = u.id JOIN `products` p ON c.product_id = p.id");
                $select_comments->execute();
                
                // Check if any comments were retrieved
                if ($select_comments->rowCount() > 0) {
                    while ($fetch_comment = $select_comments->fetch(PDO::FETCH_ASSOC)) {
                ?>
                    <tr>
                        <td><?= htmlspecialchars($fetch_comment['user_id']); ?></td>
                        <td><?= htmlspecialchars($fetch_comment['user_name']); ?></td>
                        <td><?= htmlspecialchars($fetch_comment['product_name']); ?></td>
                        <td><?= htmlspecialchars($fetch_comment['comment']); ?></td>
                        <td><?= htmlspecialchars($fetch_comment['created_at']); ?></td>
                        <td>
                            <a href="user_rating_message.php?delete=<?= $fetch_comment['id']; ?>" onclick="return confirm('Are you sure you want to delete this comment?');" class="delete-btn">Delete</a>
                        </td>
                    </tr>
                <?php
                    }
                } else {
                    echo '<tr><td colspan="6" class="empty">No comments available</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>

</section>

<script src="../js/admin_script.js"></script>

</body>
</html>
