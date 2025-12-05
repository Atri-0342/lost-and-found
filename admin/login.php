<?php
session_name("admin_session");
session_start();
require '../db.php';

// Helper for escaping HTML output
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$error = '';

// Generate or refresh CAPTCHA
if (!isset($_SESSION['captcha_text']) || !isset($_POST['captcha'])) {
    $_SESSION['captcha_text'] = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
}

if (isset($_GET['refresh'])) {
    $_SESSION['captcha_text'] = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
    exit;
}

// Handle login
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    $captcha  = trim($_POST['captcha']);

    if ($email && $password && $captcha) {
        if (strcasecmp($captcha, $_SESSION['captcha_text']) !== 0) {
            $error = "⚠ Incorrect CAPTCHA.";
        } else {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            if ($stmt->rowCount() === 1) {
                $admin = $stmt->fetch(PDO::FETCH_ASSOC);
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id']   = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_emp']  = $admin['emp_id'];
                    unset($_SESSION['captcha_text']);
                    header("Location: index.php");
                    exit;
                } else {
                    $error = "❌ Invalid password.";
                }
            } else {
                $error = "⚠ Admin not found.";
            }
        }
    } else {
        $error = "⚠ Fill all fields including CAPTCHA.";
    }

    // Always regenerate CAPTCHA after failed login
    $_SESSION['captcha_text'] = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login - Lost & Found Portal</title>
<style>
body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    display: flex;
    height: 100vh;
}
header {
    display: none;
}
main {
    display: flex;
    flex: 1;
}
section.left-side {
    flex: 1;
    background: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
}
section.left-side img {
    max-width: 80%;
}
section.right-side {
    flex: 1;
    background: #007BFF;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #fff;
}
article.login-box {
    background: #fff;
    color: #333;
    padding: 40px;
    border-radius: 15px;
    width: 80%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
}
article.login-box h2 {
    margin-bottom: 20px;
    color: #007BFF;
    text-align: center;
}
article.login-box input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
}
article.login-box button {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: none;
    border-radius: 8px;
    background: #007BFF;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
}
article.login-box button:hover {
    background: #0056b3;
}
article.login-box a {
    color: #007BFF;
    text-decoration: none;
}
.error {
    background: #ffd6d6;
    color: #d8000c;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}
.captcha-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 10px;
}
.captcha-text {
    font-weight: bold;
    font-size: 20px;
    letter-spacing: 5px;
    background: #f0f0f0;
    padding: 8px 12px;
    border-radius: 8px;
    user-select: none;
}
.refresh-btn {
    background: none;
    border: none;
    color: #007BFF;
    font-size: 18px;
    cursor: pointer;
}
.refresh-btn:hover {
    color: #0056b3;
}
footer {
    text-align: center;
    padding: 10px;
    background: #f8f9fa;
    color: #555;
    font-size: 14px;
}
</style>

<script>
function refreshCaptcha() {
    window.location.href = window.location.pathname + '?refresh=' + new Date().getTime();
}

// Client-side validation
function validateForm() {
    const email = document.forms["loginForm"]["email"].value.trim();
    const password = document.forms["loginForm"]["password"].value.trim();
    const captcha = document.forms["loginForm"]["captcha"].value.trim();
    const emailPattern = /^[^ ]+@[^ ]+\.[a-z]{2,}$/i;

    if (!email || !password || !captcha) {
        alert("⚠ Please fill in all fields including CAPTCHA.");
        return false;
    }
    if (!emailPattern.test(email)) {
        alert("⚠ Please enter a valid email address.");
        return false;
    }
    if (password.length < 8) {
        alert("⚠ Password must be at least 8 characters long.");
        return false;
    }
    return true;
}
</script>
</head>
<body>
<header>
    <h1 style="display:none;">Lost & Found Admin Portal</h1>
</header>

<main>
    <section class="left-side">
        <img src="image.png" alt="Admin Login Illustration">
    </section>

    <section class="right-side">
        <article class="login-box">
            <h2>Admin Login</h2>

            <?php if ($error): ?>
                <p class="error"><?= e($error) ?></p>
            <?php endif; ?>

            <form name="loginForm" method="post" onsubmit="return validateForm()">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="📧 Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="🔒 Enter your password" required>

                <div class="captcha-box">
                    <div class="captcha-text"><?= e($_SESSION['captcha_text']); ?></div>
                    <button type="button" class="refresh-btn" aria-label="Refresh CAPTCHA" onclick="refreshCaptcha()">↻</button>
                </div>

                <label for="captcha">Enter CAPTCHA</label>
                <input type="text" id="captcha" name="captcha" placeholder="Enter CAPTCHA" required>

                <button type="submit">Login</button>
            </form>

            <p style="margin-top:10px;">Don’t have an account? <a href="register.php">Register Here</a></p>
        </article>
    </section>
</main>
</body>
</html>
