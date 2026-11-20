<?php
include '../components/connect.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../admin/admin_login.php");
    exit;
}

$message = "";

// Add Discount
if (isset($_POST['add_discount'])) {
    $discount_name = $_POST['discount_name'];
    $discount_percentage = $_POST['discount_percentage'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("INSERT INTO discounts (discount_name, discount_percentage, description, start_date, end_date, category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$discount_name, $discount_percentage, $description, $start_date, $end_date, $category]);

    $message = "Discount added successfully!";
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to prevent form resubmission
    exit;
}

// Edit Discount
if (isset($_POST['edit_discount'])) {
    $id = $_POST['discount_id'];
    $discount_name = $_POST['discount_name'];
    $discount_percentage = $_POST['discount_percentage'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $category = $_POST['category'];

    $stmt = $conn->prepare("UPDATE discounts SET discount_name = ?, discount_percentage = ?, description = ?, start_date = ?, end_date = ?, category = ? WHERE id = ?");
    $stmt->execute([$discount_name, $discount_percentage, $description, $start_date, $end_date, $category, $id]);

    $message = "Discount updated successfully!";
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to prevent form resubmission
    exit;
}

// Delete Discount
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM discounts WHERE id = ?");
    $stmt->execute([$id]);

    $message = "Discount deleted successfully!";
    header("Location: " . $_SERVER['PHP_SELF']); // Redirect to update the list without resubmission
    exit;
}

// Fetch Discounts
$discounts = $conn->query("SELECT * FROM discounts ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Discount Management</title>
    <link rel="stylesheet" href="../css/adminstyle.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <style>
        /* Styles remain the same as before */
        .container {
            width: 90%;
            max-width: 800px;
            padding: 20px;
            background-color: #1f1f1f;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            color: #ffffff;
            margin: 50px auto;
            text-align: center;
        }
        h2, h3, p, label, input, textarea, select {
            color: #ffffff;
        }
        input, textarea, select, button {
            display: block;
            width: 100%;
            margin: 10px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #333;
            background-color: #2a2a2a;
            color: #ffffff;
        }
        button {
            background-color: #ff4d4d;
            color: #ffffff;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background-color: #e60000;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #444;
            text-align: center;
            color: #ffffff;
        }
        th {
            background-color: #333;
        }
        td {
            background-color: #1f1f1f;
        }
        a {
            color: #ff4d4d;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php include '../components/admin_header.php'; ?>

<div class="container">
    <h2>Discount Management</h2>
    <?php if (!empty($message)) { echo "<p>$message</p>"; } ?>

    <!-- Add Discount Form -->
    <form method="POST" action="">
        <h3>Add New Discount</h3>
        <input type="text" name="discount_name" placeholder="Discount Name" required>
        <input type="number" step="0.01" name="discount_percentage" placeholder="Discount Percentage (%)" required>
        <textarea name="description" placeholder="Description (optional)"></textarea>
        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>
        <label for="category">Category:</label>
        <select name="category" required>
            <option value="Laptop">Laptop</option>
            <option value="Console">Console</option>
            <option value="Lifestyle">Lifestyle</option>
            <option value="Headphones">Headphones</option>
            <option value="Charger">Charger</option>
            <option value="Accessories">Accessories</option>
        </select>
        <button type="submit" name="add_discount">Add Discount</button>
    </form>

    <!-- Discount List -->
    <h3>Existing Discounts</h3>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Percentage</th>
                <th>Description</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Category</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($discounts as $discount) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($discount['discount_name']); ?></td>
                    <td><?php echo htmlspecialchars($discount['discount_percentage']); ?>%</td>
                    <td><?php echo htmlspecialchars($discount['description']); ?></td>
                    <td><?php echo htmlspecialchars($discount['start_date']); ?></td>
                    <td><?php echo htmlspecialchars($discount['end_date']); ?></td>
                    <td><?php echo htmlspecialchars($discount['category']); ?></td>
                    <td>
                        <form method="POST" action="">
                            <input type="hidden" name="discount_id" value="<?php echo $discount['id']; ?>">
                            <input type="text" name="discount_name" value="<?php echo htmlspecialchars($discount['discount_name']); ?>" required>
                            <input type="number" step="0.01" name="discount_percentage" value="<?php echo htmlspecialchars($discount['discount_percentage']); ?>" required>
                            <textarea name="description"><?php echo htmlspecialchars($discount['description']); ?></textarea>
                            <input type="date" name="start_date" value="<?php echo htmlspecialchars($discount['start_date']); ?>" required>
                            <input type="date" name="end_date" value="<?php echo htmlspecialchars($discount['end_date']); ?>" required>
                            <label for="category">Category:</label>
                            <select name="category" required>
                                <option value="Laptop" <?php if ($discount['category'] == 'Laptop') echo 'selected'; ?>>Laptop</option>
                                <option value="Console" <?php if ($discount['category'] == 'Console') echo 'selected'; ?>>Console</option>
                                <option value="Lifestyle" <?php if ($discount['category'] == 'Lifestyle') echo 'selected'; ?>>Lifestyle</option>
                                <option value="Headphones" <?php if ($discount['category'] == 'Headphones') echo 'selected'; ?>>Headphones</option>
                                <option value="Charger" <?php if ($discount['category'] == 'Charger') echo 'selected'; ?>>Charger</option>
                                <option value="Accessories" <?php if ($discount['category'] == 'Accessories') echo 'selected'; ?>>Accessories</option>
                            </select>
                            <button type="submit" name="edit_discount">Update</button>
                        </form>
                        <a href="?delete=<?php echo $discount['id']; ?>" onclick="return confirm('Are you sure?');">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
