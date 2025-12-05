<?php
session_name("admin_session");
session_start();
require '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch all items
$result = $conn->query("SELECT * FROM items ORDER BY date_lost DESC");

// Function to get image URL for admin page
function getImageUrl($imageName) {
    $filePath = __DIR__ . '/../' . $imageName;
    if ($imageName && file_exists($filePath)) {
        return '../' . $imageName;
    }
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Lost Items Report - Admin Panel</title>
<style>
:root {
    --primary: #fb1f1f;
}

/* Make footer stick to bottom */
html, body {
    height: 100%;
    margin: 0;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f4f4f9;
    display: flex;
    flex-direction: column;
    color: #333;
}

main {
    flex: 1;
}

/* Container */
.container {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    padding: 25px 35px;
    border-radius: 12px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

/* Heading */
h1 {
    text-align: center;
    color: var(--primary);
    font-size: 1.8em;
    margin-bottom: 15px;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}
th, td {
    padding: 10px;
    border-bottom: 1px solid #ccc;
    text-align: left;
}
th {
    background: var(--primary);
    color: #fff;
}
tr:hover {
    background: #f9f9f9;
}
img {
    width: 60px;
    height: 60px;
    border-radius: 5px;
    object-fit: cover;
}

/* Action buttons */
.action-btns a {
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 5px;
    color: #fff;
    margin-right: 5px;
    font-size: 0.9em;
}
.edit {
    background-color: #3498db;
}
.delete {
    background-color: #e74c3c;
}
.delete:hover {
    background-color: #c0392b;
}

/* Delete all button */
.delete-all {
    display: block;
    background-color: #e74c3c;
    color: #fff;
    text-align: center;
    padding: 10px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-weight: bold;
    width: 200px;
    margin: 20px auto;
    transition: background 0.3s;
}
.delete-all:hover {
    background-color: #c0392b;
}

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 20px;
    }
    table {
        font-size: 0.9em;
    }
    img {
        width: 50px;
        height: 50px;
    }
}
</style>
</head>
<body>

<?php include 'nav.php'; ?>

<main>
    <div class="container">
        <h1>📊 Lost Items Report</h1>

        <!-- Delete All Items Button -->
        <form method="get" action="delete_item.php" onsubmit="return confirm('Are you sure you want to delete all items?');">
            <input type="hidden" name="delete_all" value="1">
            <button type="submit" class="delete-all">Delete All Items</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Date Lost</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['student_id']) ?></td>
                    <td><?= htmlspecialchars($row['title']) ?></td>
                    <td><?= nl2br(htmlspecialchars($row['description'])) ?></td>
                    <td><?= htmlspecialchars($row['location']) ?></td>
                    <td><?= htmlspecialchars($row['date_lost']) ?></td>
                    <td>
                        <?php $imgUrl = getImageUrl($row['image']); ?>
                        <?php if ($imgUrl): ?>
                            <img src="<?= htmlspecialchars($imgUrl) ?>" alt="Item Image">
                        <?php else: ?>
                            No Image
                        <?php endif; ?>
                    </td>
                    <td class="action-btns">
                        <a href="update_item.php?id=<?= $row['id'] ?>" class="edit">Edit</a>
                        <a href="delete_item.php?id=<?= $row['id'] ?>" class="delete" onclick="return confirm('Delete this item?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
