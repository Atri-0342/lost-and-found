<?php
// ==========================================
// USER REGISTRATION - LOST & FOUND SYSTEM
// Fully Secure (Client + Server)
// No custom sanitize functions
// ==========================================

require 'db.php';
session_name("student_session");
session_start();

$error = '';
$success = '';

// 🧠 SERVER-SIDE VALIDATION
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name       = trim($_POST['name'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $college_id = trim($_POST['college_id'] ?? '');

    if ($name && $email && $password && $college_id) {

        // Validate name
        if (!preg_match("/^[A-Za-z\s]+$/", $name)) {
            $error = "⚠ Name can only contain alphabets and spaces.";
        }
        // Validate email format
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "⚠ Please enter a valid email address.";
        }
        // Validate password strength
        elseif (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
            $error = "⚠ Password must have at least 8 characters, including uppercase, lowercase, number, and special character.";
        }
        else {
            // Check if email or college_id already exists
            $stmt = $conn->prepare("SELECT 1 FROM users WHERE email = :email OR college_id = :college_id LIMIT 1");
            $stmt->execute([
                ':email' => $email,
                ':college_id' => $college_id
            ]);

            if ($stmt->rowCount() > 0) {
                $error = "⚠ Email or College ID already registered.";
            } else {
                // Hash password securely
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert safely using prepared statement
                $stmt = $conn->prepare("
                    INSERT INTO users (name, email, password, college_id)
                    VALUES (:name, :email, :password, :college_id)
                ");
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':password', $hashed_password, PDO::PARAM_STR);
                $stmt->bindParam(':college_id', $college_id, PDO::PARAM_STR);

                if ($stmt->execute()) {
                    $success = "✅ Registration successful! You can now <a href='user_login.php'>login</a>.";
                } else {
                    $error = "❌ Registration failed. Please try again.";
                }
            }
        }
    } else {
        $error = "⚠ All fields are required.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Registration - Lost & Found</title>
<style>
body {
    font-family: 'Poppins', sans-serif;
    background: linear-gradient(135deg, #f2f6ff, #dbe9ff);
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
}
.registration-box {
    background: #fff;
    padding: 40px 35px;
    border-radius: 20px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    width: 90%;
    max-width: 420px;
}
.registration-box h2 {
    text-align: center;
    color: #007BFF;
    margin-bottom: 20px;
}
input[type="text"],
input[type="email"],
input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 8px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    transition: border-color 0.3s;
}
input:focus {
    border-color: #007BFF;
    outline: none;
}
button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border: none;
    border-radius: 8px;
    background: #007BFF;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}
button:hover {
    background: #0056b3;
}
.success, .error {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
}
.success {
    background: #d4edda;
    color: #155724;
}
.error {
    background: #ffd6d6;
    color: #d8000c;
}
a { color: #007BFF; text-decoration: none; }
a:hover { text-decoration: underline; }
@media (max-width: 600px) {
    .registration-box { padding: 25px 20px; }
}
</style>
<script>
// ✅ CLIENT-SIDE VALIDATION + SANITIZATION
function validateForm() {
    const form = document.forms["regForm"];
    const name = form["name"].value.trim();
    const email = form["email"].value.trim();
    const password = form["password"].value;
    const college_id = form["college_id"].value.trim();

    // Patterns
    const namePattern = /^[A-Za-z\s]+$/;
    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

    // Sanitize (strip HTML)
    form["name"].value = name.replace(/<[^>]*>?/gm, '');
    form["email"].value = email.replace(/<[^>]*>?/gm, '');
    form["college_id"].value = college_id.replace(/<[^>]*>?/gm, '');

    // Validate
    if (!namePattern.test(name)) {
        alert("Name can only contain alphabets and spaces.");
        return false;
    }
    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }
    if (!passwordPattern.test(password)) {
        alert("Password must include:\n• At least 8 characters\n• One uppercase letter\n• One lowercase letter\n• One number\n• One special character");
        return false;
    }
    if (college_id.length < 2) {
        alert("Please enter a valid College ID.");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<div class="registration-box">
    <h2>User Registration</h2>

    <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($success): ?>
        <p class="success"><?= $success ?></p>
    <?php endif; ?>

    <form name="regForm" method="POST" action="" onsubmit="return validateForm()">
        <input type="text" name="name" placeholder="Full Name" required>
        <input type="email" name="email" placeholder="Email Address" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="text" name="college_id" placeholder="College ID" required>
        <button type="submit">Register</button>
    </form>

    <p style="text-align:center; margin-top:15px;">
        Already have an account? <a href="user_login.php">Login here</a>
    </p>
</div>
</body>
</html>
