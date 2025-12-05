<?php
session_name("student_session");
session_start();
require 'db.php';

if (!isset($_SESSION['college_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']) ?? null;
if (!$id) { echo "Invalid ID."; exit; }

// Fetch item first
$stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND student_id = ?");
$stmt->execute([$id, $_SESSION['college_id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) { echo "Item not found or access denied."; exit; }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $delete_msg = trim($_POST['delete_message'] ?? '');

    // Save deleted item info & message in deleted_items table
    $stmtSave = $conn->prepare("INSERT INTO deleted_items (student_id, title, message, image) VALUES (?, ?, ?, ?)");
    $stmtSave->execute([$_SESSION['college_id'], $item['title'], $delete_msg, $item['image']]);

    // Delete the item from items table
    $stmtDel = $conn->prepare("DELETE FROM items WHERE id = ? AND student_id = ?");
    $deleted = $stmtDel->execute([$id, $_SESSION['college_id']]);

    if ($deleted) {
        if (!empty($item['image']) && file_exists($item['image'])) {
            unlink($item['image']);
        }
        $_SESSION['success'] = "Item deleted successfully.";
        header("Location: index.php");
        exit;
    } else {
        $error = "Failed to delete item.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Delete Item</title>
<style>
body {
    font-family: Poppins, sans-serif;
    background: #f8f9fa;
    margin: 0;
    padding: 0;
    color: #333;
}

.container {
    max-width: 500px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}

h2 {
    text-align: center;
    color: #dc3545; /* Red for attention */
    margin-bottom: 25px;
}

p {
    margin-bottom: 15px;
}

textarea {
    width: 100%;
    padding: 12px 15px;
    border: 1px solid #ccc;
    border-radius: 8px;
    margin-bottom: 15px;
    resize: none;
    font-size: 14px;
    box-sizing: border-box;
}

.btn {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

.btn.delete {
    background: #dc3545; /* Red */
    color: #fff;
}
.btn.delete:hover {
    background: #b02a37;
}

.btn.cancel {
    background: #6c757d; /* Grey */
    color: #fff;
    margin-left: 10px;
}
.btn.cancel:hover {
    background: #495057;
}

img {
    max-width: 100px;
    border-radius: 8px;
    margin-top: 5px;
}

.error-msg {
    background: #ffd6d6;
    color: #d8000c;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
}

.success-msg {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
}
</style>
</head>
<body>
<div class="container">
    <h2>Delete Item</h2>

    <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>
    <?php if (!empty($_SESSION['success'])) { 
        echo "<p class='success-msg'>".$_SESSION['success']."</p>";
        unset($_SESSION['success']);
    } ?>

    <p>Are you sure you want to delete this item?</p>
    <p><strong>Title:</strong> <?= htmlspecialchars($item['title']) ?></p>
    <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
        <p><img src="<?= htmlspecialchars($item['image']) ?>" alt="Item Image"></p>
    <?php endif; ?>

    <form method="POST">
        <label for="delete_message">Optional: Reason for deletion</label>
        <textarea name="delete_message" id="delete_message" placeholder="Enter reason for deleting this item..."></textarea>
        <button type="submit" class="btn delete">Delete Item</button>
        <a href="index.php" class="btn cancel">Cancel</a>
    </form>
</div>
</body>
</html>
