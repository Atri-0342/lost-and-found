<?php
session_name("student_session");
session_start();
require 'db.php';

if (!isset($_SESSION['college_id'])) {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']) ?? null;
if (!$id) {
    echo "Invalid request.";
    exit;
}

$stmt = $conn->prepare("SELECT * FROM items WHERE id = ? AND student_id = ?");
$stmt->execute([$id, $_SESSION['college_id']]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$item) {
    echo "Item not found or access denied.";
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD']=='POST') {
    $title=$_POST['title'] ?? '';
    $description=$_POST['description'] ?? '';
    $location=$_POST['location'] ?? '';
    $date_lost=$_POST['date_lost'] ?? '';
    $message=$_POST['message'] ?? '';
    $image_path=$item['image'];

    if (!empty($_FILES['image']['name'])) {
        $img_name = basename($_FILES['image']['name']);
        $target_dir = "uploads/";
        $target_file = $target_dir . time() . "_" . $img_name;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file;
        }
    }

    $stmt=$conn->prepare("UPDATE items SET title = ?, description = ?, location = ?, date_lost = ?, message = ?, image = ? WHERE id = ? AND student_id = ?");
    $updated=$stmt->execute([$title, $description, $location, $date_lost, $message, $image_path, $id, $_SESSION['college_id']]);

    if ($updated) {
        $_SESSION['success'] = "Item updated successfully.";
        header("Location: index.php");
        exit;
    } else {
        $error = "Failed to update item.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Update Item</title>
    <style>
    body {
        margin:0;
        font-family: 'Poppins', sans-serif;
        background: #f8f9fa;
        color:#333;
    }
    .container {
        max-width:600px;
        margin:50px auto;
        background:#fff;
        padding:30px 40px;
        border-radius:15px;
        box-shadow:0 10px 25px rgba(0,0,0,0.1);
    }
    h2 {
        text-align:center;
        color: #4677e2ff;
        margin-bottom:30px;
    }
    .form-box {
        display:flex;
        flex-direction:column;
    }
    .form-box input[type="text"],
    .form-box input[type="date"],
    .form-box input[type="file"],
    .form-box textarea {
        padding:12px 15px;
        margin:8px 0 15px 0;
        border:1px solid #ccc;
        border-radius:8px;
        font-size:14px;
        width:100%;
        box-sizing:border-box;
        transition:border 0.3s;
    }
    .form-box input[type="text"]:focus,
    .form-box input[type="date"]:focus,
    .form-box input[type="file"]:focus,
    .form-box textarea:focus {
        border-color:#28a745;
        outline:none;
    }
    textarea {
        min-height:80px;
        resize:vertical;
    }
    .btn {
        padding:12px 20px;
        border:none;
        border-radius:8px;
        cursor:pointer;
        font-size:16px;
        transition:background 0.3s;
    }
    .btn.update {
        background-color: #3a82edff;
        color:#fff;
    }
    .btn.update:hover {
        background-color: #3b74efff;
    }
    .error-msg {
        background: #ffd6d6;
        color: #d8000c;
        padding:10px;
        border-radius:8px;
        margin-bottom:15px;
    }
    .success-msg {
        background: #d4edda;
        color: #155724;
        padding:10px;
        border-radius:8px;
        margin-bottom:15px;
    }
    .container img {
        border-radius:8px;
        margin-top:5px;
        max-width:100px;
    }
    @media(max-width:640px) {
        .container {
            margin:20px;
            padding:20px;
        }
    }
    </style>
</head>
<body>
<div class="container">
    <h2>Update Lost Item</h2>

    <?php if (!empty($error)) echo "<p class='error-msg'>$error</p>"; ?>
    <?php if (!empty($_SESSION['success'])) { 
        echo "<p class='success-msg'>".$_SESSION['success']."</p>";
        unset($_SESSION['success']);
    } ?>

    <form method="POST" enctype="multipart/form-data" class="form-box">
        Title:<input type="text" name="title" value="<?= htmlspecialchars($item['title']) ?>" required><br>
        Description:<textarea name="description" required><?= htmlspecialchars($item['description']) ?></textarea><br>
        Location:<input type="text" name="location" value="<?= htmlspecialchars($item['location']) ?>" required><br>
        Date Lost:<input type="date" name="date_lost" value="<?= htmlspecialchars($item['date_lost']) ?>" required><br>
        Message:<textarea name="message"><?= htmlspecialchars($item['message']) ?></textarea><br>
        Image:<input type="file" name="image"><br>
        <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
            <p>Current Image: <img src="<?= htmlspecialchars($item['image']) ?>" alt="Current Image"></p>
        <?php endif; ?>
        <button type="submit" class="btn update">Update Item</button>
    </form>
</div>
</body>
</html>
