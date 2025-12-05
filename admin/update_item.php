<?php
session_name("admin_session");

session_start();
require '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Check if ID is given
if (!isset($_GET['id'])) {
    header("Location: report.php");
    exit;
}

$id = intval($_GET['id']);

// Fetch item data
$stmt = $conn->prepare("SELECT * FROM items WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    header("Location: report.php");
    exit;
}

// Handle form submission
if (isset($_POST['update'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $date_lost = $_POST['date_lost'];

    // Keep existing image by default
    $image = $item['image'];

    // Handle new image upload
    if (isset($_FILES['image']) && $_FILES['image']['name'] != '') {
        $targetDir = "../uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

        $filename = time() . "_" . basename($_FILES['image']['name']);
        $targetFile = $targetDir . $filename;

        // Delete old image if exists
        if (!empty($item['image']) && file_exists("../" . $item['image'])) {
            unlink("../" . $item['image']);
        }

        // Move uploaded file
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $image = "uploads/" . $filename; // store full relative path
        }
    }

    // Update database
    $stmt = $conn->prepare("UPDATE items SET title = :title, description = :description, location = :location, date_lost = :date_lost, image = :image WHERE id = :id");
    $stmt->execute([
        ':title' => $title,
        ':description' => $description,
        ':location' => $location,
        ':date_lost' => $date_lost,
        ':image' => $image,
        ':id' => $id
    ]);

    // Redirect back to report page
    header("Location: report.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Item</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    margin: 0; padding: 0;
}
.container {
    max-width: 600px;
    margin: 40px auto;
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}
h1 {
    text-align: center;
    color: #333;
}
form {
    display: flex;
    flex-direction: column;
}
label {
    margin-top: 10px;
    font-weight: bold;
}
input[type="text"],
input[type="date"],
textarea {
    padding: 8px;
    margin-top: 5px;
    border-radius: 5px;
    border: 1px solid #ccc;
    width: 100%;
}
textarea {
    resize: vertical;
}
button {
    margin-top: 20px;
    padding: 10px;
    background-color: #fb1f1fff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-weight: bold;
}
button:hover {
    background-color: #c00f0f;
}
img {
    margin-top: 10px;
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 5px;
}
</style>
</head>
<body>

<?php require 'nav.php'; ?>

<div class="container">
    <h1>Update Item</h1>
    <form method="post" enctype="multipart/form-data">
        <label>Title</label>
        <input type="text" name="title" value="<?= htmlspecialchars($item['title']); ?>" required>

        <label>Description</label>
        <textarea name="description" rows="4" required><?= htmlspecialchars($item['description']); ?></textarea>

        <label>Location</label>
        <input type="text" name="location" value="<?= htmlspecialchars($item['location']); ?>" required>

        <label>Date Lost</label>
        <input type="date" name="date_lost" value="<?= htmlspecialchars($item['date_lost']); ?>" required>

        <label>Image (optional)</label>
        <input type="file" name="image">
        <?php if ($item['image'] && file_exists("../" . $item['image'])): ?>
            <img src="../<?= htmlspecialchars($item['image']); ?>" alt="Item Image">
        <?php endif; ?>

        <button type="submit" name="update">Update Item</button>
    </form>
</div>

</body>
</html>
