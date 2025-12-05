<?php
session_name("student_session");
session_start();
require 'db.php'; // Ensure $conn is defined

// ✅ Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: user_login.php");
    exit;
}

// ✅ Fetch user details
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT name, email, college_id FROM users WHERE id = :id LIMIT 1");
$stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found.");
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // ✅ Sanitize inputs
    $title       = htmlentities(trim($_POST['title'] ?? ''), ENT_QUOTES, 'UTF-8');
    $description = htmlentities(trim($_POST['description'] ?? ''), ENT_QUOTES, 'UTF-8');
    $location    = htmlentities(trim($_POST['location'] ?? ''), ENT_QUOTES, 'UTF-8');
    $date_lost   = $_POST['date_lost'] ?? '';
    $message     = htmlentities(trim($_POST['message'] ?? ''), ENT_QUOTES, 'UTF-8');

    // ✅ Validate
    if (empty($title) || empty($description) || empty($location) || empty($date_lost)) {
        $error = "⚠ Please fill all required fields.";
    } else {
        // ✅ Image upload
        $image_path = '';
        if (!empty($_FILES['image']['name'])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir, 0777, true);

            $tmp_name = $_FILES["image"]["tmp_name"];
            $image_type = mime_content_type($tmp_name);
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];

            if (!in_array($image_type, $allowed_types)) {
                $error = "❌ Only JPG, PNG, or GIF images are allowed.";
            } elseif ($_FILES["image"]["size"] > 2 * 1024 * 1024) {
                $error = "❌ File size must be under 2MB.";
            } else {
                $ext = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
                $filename = time() . "_" . bin2hex(random_bytes(4)) . "." . $ext;
                $image_path = $target_dir . $filename;

                if (!move_uploaded_file($tmp_name, $image_path)) {
                    $error = "❌ Failed to upload image.";
                }
            }
        }

        // ✅ Insert only if no error
        if (empty($error)) {
            $stmt = $conn->prepare("
                INSERT INTO items (student_id, name, email, title, description, location, date_lost, image, message)
                VALUES (:student_id, :name, :email, :title, :description, :location, :date_lost, :image, :message)
            ");

            $stmt->bindParam(':student_id', $user['college_id'], PDO::PARAM_STR);
            $stmt->bindParam(':name', $user['name'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $user['email'], PDO::PARAM_STR);
            $stmt->bindParam(':title', $title, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);
            $stmt->bindParam(':location', $location, PDO::PARAM_STR);
            $stmt->bindParam(':date_lost', $date_lost, PDO::PARAM_STR);
            $stmt->bindParam(':image', $image_path, PDO::PARAM_STR);
            $stmt->bindParam(':message', $message, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $_SESSION['success'] = "✅ Lost item reported successfully!";
                header("Location: index.php");
                exit;
            } else {
                if ($image_path && file_exists($image_path)) unlink($image_path);
                $error = "❌ Database error. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Report Lost Item</title>
<style>
body {
    font-family: Arial, sans-serif;
    background: #f4f4f9;
    margin: 0;
    padding: 0;
}
main {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 40px 15px;
}
article {
    background: #fff;
    max-width: 600px;
    width: 100%;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
header h1 {
    text-align: center;
    color: #007BFF;
    margin-bottom: 20px;
}
form label {
    display: block;
    margin-top: 15px;
    font-weight: bold;
}
input[type="text"],
input[type="date"],
textarea,
input[type="file"] {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
textarea {
    resize: vertical;
    min-height: 80px;
}
button {
    background: #007BFF;
    color: #fff;
    border: none;
    padding: 12px 20px;
    margin-top: 20px;
    cursor: pointer;
    border-radius: 6px;
    width: 100%;
    font-size: 16px;
}
button:hover {
    background: #0056b3;
}
.error, .success {
    padding: 10px;
    border-radius: 6px;
    margin-bottom: 15px;
}
.error {
    background: #ffd6d6;
    color: #d8000c;
}
.success {
    background: #d4edda;
    color: #155724;
}
footer {
    text-align: center;
    font-size: 14px;
    color: #777;
    margin-top: 20px;
}
</style>
</head>
<body>
    <?php include "nav.php" ?>

<main>
    <article>
        <header>
            <h1>Report Lost Item</h1>
        </header>

        <?php if ($error): ?>
            <section class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></section>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <section class="success"><?= htmlspecialchars($_SESSION['success'], ENT_QUOTES, 'UTF-8'); unset($_SESSION['success']); ?></section>
        <?php endif; ?>

        <section>
            <form method="POST" enctype="multipart/form-data">
                <label for="title">Title *</label>
                <input type="text" name="title" id="title" required>

                <label for="description">Description *</label>
                <textarea name="description" id="description" required></textarea>

                <label for="location">Location *</label>
                <input type="text" name="location" id="location" required>

                <label for="date_lost">Date Lost *</label>
                <input type="date" name="date_lost" id="date_lost" required>

                <label for="image">Upload Image (JPG, PNG, GIF only, max 2MB)</label>
                <input type="file" name="image" id="image" accept="image/*">

                <label for="message">Optional Message</label>
                <textarea name="message" id="message"></textarea>

                <button type="submit">Report</button>
            </form>
        </section>
    </article>
</main>
<?php include "footer.php" ?>
</body>
</html>
