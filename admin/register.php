<?php
session_name("admin_session");
session_start();
require '../db.php'; // PDO connection

function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = trim($_POST['name']);
    $email    = trim($_POST['email']);
    $emp_id   = trim($_POST['emp_id']);
    $password = trim($_POST['password']);
    $confirm  = trim($_POST['confirm']);

    if ($name && $email && $emp_id && $password && $confirm) {
        if ($password !== $confirm) {
            $error = "❌ Passwords do not match.";
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&])[A-Za-z\d@$!%*#?&]{8,}$/', $password)) {
            $error = "⚠ Password must be at least 8 characters long and include uppercase, lowercase, number, and special character.";
        } else {
            $stmt = $conn->prepare("SELECT id FROM admin WHERE email=:email OR emp_id=:emp_id LIMIT 1");
            $stmt->execute([':email'=>$email, ':emp_id'=>$emp_id]);

            if ($stmt->rowCount() > 0) {
                $error = "⚠ Email or Employee ID already registered.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("
                    INSERT INTO admin (name, email, emp_id, password)
                    VALUES (:name, :email, :emp_id, :pass)
                ");
                $stmt->execute([
                    ':name'   => $name,
                    ':email'  => $email,
                    ':emp_id' => $emp_id,
                    ':pass'   => $hashed
                ]);
                $success = "✅ Registration successful. You can now login.";
            }
        }
    } else {
        $error = "⚠ Please fill in all required fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Register - Lost & Found Portal</title>
<style>
:root {
    --primary: #007BFF;
    --primary-dark: #0056b3;
    --error-bg: #ffd6d6;
    --error-text: #d8000c;
    --success-bg: #d4edda;
    --success-text: #155724;
}

body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    display: flex;
    min-height: 100vh;
    background: #f0f4f8;
    color: #333;
}

main {
    display: flex;
    flex: 1;
}

aside {
    flex: 1;
    background: #ffffffff;
    display: flex;
    justify-content: center;
    align-items: center;
}

aside img {
    max-width: 80%;
    height: auto;
}

section.register-section {
    flex: 1;
    background: var(--primary);
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 40px 0;
    color: #fff;
}

article.register-box {
    background: #fff;
    color: #333;
    padding: 40px;
    border-radius: 15px;
    width: 85%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}

article.register-box h1 {
    text-align: center;
    color: var(--primary);
    margin-bottom: 20px;
}

form label {
    display: block;
    margin-top: 10px;
    font-weight: 500;
}

form input {
    width: 100%;
    padding: 12px 15px;
    margin: 8px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 15px;
}

form button {
    width: 100%;
    padding: 12px;
    margin-top: 15px;
    border: none;
    border-radius: 8px;
    background: var(--primary);
    color: #fff;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s;
}

form button:hover {
    background: var(--primary-dark);
}

a {
    color: var(--primary);
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}

.error {
    background: var(--error-bg);
    color: var(--error-text);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
}
.success {
    background: var(--success-bg);
    color: var(--success-text);
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
}
.field-error {
    color: var(--error-text);
    font-size: 13px;
    margin-top: -8px;
    margin-bottom: 8px;
    display: none;
}
</style>

<script>
function validateRegisterForm() {
    let valid = true;
    document.querySelectorAll('.field-error').forEach(el => el.style.display = 'none');

    const form = document.forms["registerForm"];
    const name = form["name"].value.trim();
    const email = form["email"].value.trim();
    const emp_id = form["emp_id"].value.trim();
    const password = form["password"].value.trim();
    const confirm = form["confirm"].value.trim();

    const namePattern = /^[A-Za-z\s]+$/;
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/;
    const passPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*#?&]).{8,}$/;

    if (name === "" || !namePattern.test(name)) {
        showError("name-error", "Please enter a valid name (letters and spaces only).");
        valid = false;
    }
    if (email === "" || !emailPattern.test(email)) {
        showError("email-error", "Enter a valid email address.");
        valid = false;
    }
    if (emp_id === "") {
        showError("emp-error", "Enter your Employee ID.");
        valid = false;
    }
    if (!passPattern.test(password)) {
        showError("pass-error", "Password must include uppercase, lowercase, number, and special character (min 8 chars).");
        valid = false;
    }
    if (confirm !== password) {
        showError("confirm-error", "Passwords do not match.");
        valid = false;
    }

    return valid;
}

function showError(id, message) {
    const el = document.getElementById(id);
    el.textContent = message;
    el.style.display = 'block';
}
</script>
</head>

<body>
<header>
    <h1 style="display:none;">Admin Registration</h1>
</header>

<main>
    <aside>
        <img src="image.png" alt="Admin registration illustration">
    </aside>

    <section class="register-section" aria-labelledby="register-title">
        <article class="register-box">
            <h1 id="register-title">Admin Register</h1>

            <?php if ($error): ?><p class="error"><?= e($error) ?></p><?php endif; ?>
            <?php if ($success): ?><p class="success"><?= e($success) ?></p><?php endif; ?>

            <form name="registerForm" method="post" action="" onsubmit="return validateRegisterForm()" novalidate>
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" placeholder="Full Name" required>
                <div id="name-error" class="field-error"></div>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Email Address" required>
                <div id="email-error" class="field-error"></div>

                <label for="emp_id">Employee ID</label>
                <input type="text" id="emp_id" name="emp_id" placeholder="Employee ID" required>
                <div id="emp-error" class="field-error"></div>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Password" required minlength="8">
                <div id="pass-error" class="field-error"></div>

                <label for="confirm">Confirm Password</label>
                <input type="password" id="confirm" name="confirm" placeholder="Confirm Password" required>
                <div id="confirm-error" class="field-error"></div>

                <button type="submit">Register</button>
            </form>

            <p style="margin-top:10px; text-align:center;">
                Already have an account?
                <a href="login.php">Login Here</a>
            </p>
        </article>
    </section>
</main>
</body>
</html>
