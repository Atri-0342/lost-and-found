<?php
session_name("admin_session");
session_start();
require '../db.php'; // PDO connection

function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $emp_id = trim($_POST['emp_id'] ?? '');

    if (!$name || !$email || !$password || !$emp_id) {
        $error = "⚠ Please fill all fields.";
    } elseif (!preg_match("/^[A-Za-z\s]+$/", $name)) {
        $error = "⚠ Name can contain only letters and spaces.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "⚠ Invalid email format.";
    } elseif (strlen($password) < 8) {
        $error = "⚠ Password must be at least 8 characters long.";
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM admin WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $error = "⚠ Admin with this email already exists.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new admin
            $stmt = $conn->prepare("INSERT INTO admin (name, email, password, emp_id) VALUES (?, ?, ?, ?)");
            $inserted = $stmt->execute([$name, $email, $hashed_password, $emp_id]);

            if ($inserted) {
                $success = "✅ New admin added successfully!";
            } else {
                $error = "❌ Failed to add admin. Try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Admin - Lost & Found Portal</title>
<style>
:root {
    --primary: #fb1f1f;
    --primary-dark: #d11616;
    --text: #333;
    --bg: #f8f9fa;
}
body {
    margin: 0;
    font-family: "Poppins", sans-serif;
    background: var(--bg);
    color: var(--text);
}
header {
    background-color: var(--primary);
    color: #fff;
}
main {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    padding: 40px 20px;
}
section.form-section {
    background: #fff;
    padding: 30px;
    border-radius: 15px;
    max-width: 600px;
    width: 100%;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    border-top: 6px solid var(--primary);
}
h1 {
    color: var(--primary);
    text-align: center;
    margin-bottom: 25px;
}
label {
    display: block;
    margin-top: 15px;
    font-weight: 500;
}
input {
    width: 100%;
    padding: 12px 15px;
    margin: 8px 0;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
}
input:focus {
    border-color: var(--primary);
    outline: none;
}
button {
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    background: var(--primary);
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    width: 100%;
    margin-top: 15px;
    transition: background 0.3s;
}
button:hover {
    background: var(--primary-dark);
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
.field-error {
    color: #d8000c;
    background: #ffd6d6;
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 13px;
    margin-top: -5px;
    margin-bottom: 10px;
    display: none;
}
</style>
<script>
function showError(id, msg) {
    const el = document.getElementById(id);
    if (el) {
        el.textContent = msg;
        el.style.display = 'block';
    }
}

function validateAdminForm() {
    let valid = true;
    document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');

    const name = document.forms["adminForm"]["name"].value.trim();
    const email = document.forms["adminForm"]["email"].value.trim();
    const password = document.forms["adminForm"]["password"].value.trim();
    const emp_id = document.forms["adminForm"]["emp_id"].value.trim();

    const namePattern = /^[A-Za-z\s]+$/;
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/;

    if (name === "") {
        showError("name-error", "Please enter full name.");
        valid = false;
    } else if (!namePattern.test(name)) {
        showError("name-error", "Name can contain only letters and spaces.");
        valid = false;
    }

    if (email === "") {
        showError("email-error", "Please enter email address.");
        valid = false;
    } else if (!emailPattern.test(email)) {
        showError("email-error", "Enter a valid email address.");
        valid = false;
    }

    if (password.length < 8) {
        showError("password-error", "Password must be at least 8 characters long.");
        valid = false;
    }

    if (emp_id === "") {
        showError("emp-error", "Please enter employee ID.");
        valid = false;
    }

    return valid;
}
</script>
</head>
<body>

<header>
    <?php include 'nav.php'; ?>
</header>

<main>
    <section class="form-section" aria-labelledby="add-admin-title">
        <h1 id="add-admin-title">Add New Admin</h1>

        <?php if ($error): ?>
            <p class="error-msg"><?= e($error) ?></p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="success-msg"><?= e($success) ?></p>
        <?php endif; ?>

        <form name="adminForm" method="POST" onsubmit="return validateAdminForm()" novalidate>
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" placeholder="Enter full name" required>
            <div class="field-error" id="name-error"></div>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" placeholder="Enter email" required>
            <div class="field-error" id="email-error"></div>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" placeholder="Enter password" required>
            <div class="field-error" id="password-error"></div>

            <label for="emp_id">Employee ID</label>
            <input type="text" name="emp_id" id="emp_id" placeholder="Enter employee ID" required>
            <div class="field-error" id="emp-error"></div>

            <button type="submit">Add Admin</button>
        </form>
    </section>
</main>

<?php include "footer.php"; ?>

</body>
</html>
