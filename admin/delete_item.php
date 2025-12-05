<?php
session_name("admin_session");

session_start();
require '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// DELETE SINGLE ITEM
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Delete image if exists
    $result = $conn->query("SELECT image FROM items WHERE id='$id'");
    $item = $result->fetch();
    if ($item && !empty($item['image'])) {
        $imagePath = "../uploads/" . $item['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete item from database
    $conn->query("DELETE FROM items WHERE id='$id'");
}

// DELETE ALL ITEMS
if (isset($_GET['delete_all'])) {
    // Delete all images
    $result = $conn->query("SELECT image FROM items");
    while ($row = $result->fetch()) {
        if (!empty($row['image'])) {
            $path = "../uploads/" . $row['image'];
            if (file_exists($path)) unlink($path);
        }
    }
    // Delete all items
    $conn->query("DELETE FROM items");
}

// Redirect back to report page
header("Location: report.php");
exit;
?>
