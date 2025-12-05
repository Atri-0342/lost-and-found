<?php
session_name("student_session");
session_start();
require 'db.php'; // PDO connection

$error = '';

// ✅ Generate CAPTCHA initially or on refresh
if (!isset($_SESSION['captcha_text']) || isset($_GET['refresh'])) {
    $_SESSION['captcha_text'] = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
    if (isset($_GET['refresh'])) {
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?')); // clean URL reload
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $captcha  = trim($_POST['captcha'] ?? '');

    // ✅ Check if all fields are filled
    if ($email && $password && $captcha) {

        // ✅ CAPTCHA check
        if (strcasecmp($captcha, $_SESSION['captcha_text']) !== 0) {
            $error = "⚠ Incorrect CAPTCHA.";
        } else {
            // ✅ Lookup user securely
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // ✅ Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['college_id'] = $user['college_id'];

                unset($_SESSION['captcha_text']);
                header("Location: index.php");
                exit;
            } else {
                $error = "❌ Invalid email or password.";
            }
        }
    } else {
        $error = "⚠ Please fill all fields including CAPTCHA.";
    }

    // Always regenerate CAPTCHA after failed attempt
    $_SESSION['captcha_text'] = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Login - Lost & Found Portal</title>
<style>
body {
    margin: 0;
    font-family: Poppins, sans-serif;
    display: flex;
    height: 100vh;
}
main {
    display: flex;
    flex: 1;
}
section {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
}
.left-side {
    background: #ffffffff;
}
.left-side img {
    max-width: 80%;
}
.right-side {
    background: #28a745;
    color: #fff;
}
article.login-box {
    background: #fff;
    color: #333;
    padding: 40px;
    border-radius: 15px;
    width: 80%;
    max-width: 400px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
h1 {
    margin-bottom: 10px;
    color: #28a745;
}
p.subtitle {
    margin-bottom: 20px;
}
input {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border-radius: 8px;
    border: 1px solid #ccc;
}
button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border: none;
    border-radius: 8px;
    background: #28a745;
    color: #fff;
    font-size: 16px;
    cursor: pointer;
}
button:hover {
    background: #1e7e34;
}
a {
    color: #28a745;
    text-decoration: none;
}
a:hover {
    text-decoration: underline;
}
.error {
    background: #ffd6d6;
    color: #d8000c;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
    text-align: center;
}
.captcha-box {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 9px 0;
}

.captcha-text {
    font-weight: bold;
    font-size: 20px;
    letter-spacing: 5px;
    background: #f0f0f0;
    padding: 7px 16px;
    border-radius: 8px;
    user-select: none;
    flex-grow: 1;
    margin-top:7px;
    text-align: center;
}

.refresh-btn {
    background: #dc3545; /* red */
    border: none;
    color: #fff;
    font-size: 15px;
    padding: 10px 10px;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.refresh-btn:hover {
    background: #b02a37; /* dark red */
}

footer {
    text-align: center;
    padding: 10px;
    font-size: 14px;
    color: #555;
}
</style>
<script>
// ✅ Simple JS validation for client-side security
function validateForm() {
    const email = document.forms["loginForm"]["email"].value.trim();
    const password = document.forms["loginForm"]["password"].value.trim();
    const captcha = document.forms["loginForm"]["captcha"].value.trim();

    const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (!emailPattern.test(email)) {
        alert("Please enter a valid email address.");
        return false;
    }
    if (password.length < 6) {
        alert("Password must be at least 6 characters.");
        return false;
    }
    if (captcha.length < 1) {
        alert("Please enter CAPTCHA.");
        return false;
    }
    return true;
}

function refreshCaptcha() {
    window.location.href = window.location.pathname + '?refresh=' + new Date().getTime();
}
</script>
</head>
<body>

<main>
    <section class="left-side">
        <img src="image.png" alt="Login Illustration">
    </section>

    <section class="right-side">
        <article class="login-box">
            <header>
                <h1>Hello!</h1>
                <p class="subtitle">Sign in to get started</p>
            </header>

            <?php if (!empty($error)): ?>
                <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>

            <form name="loginForm" method="POST" onsubmit="return validateForm()">
                <label for="email">Email Address</label>
                <input type="email" name="email" id="email" placeholder="📧 Email Address" required>

                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="🔒 Password" required>

                <div class="captcha-box">
    <div class="captcha-text"><?= htmlspecialchars($_SESSION['captcha_text'], ENT_QUOTES, 'UTF-8'); ?></div>
    <button type="button" class="refresh-btn" onclick="refreshCaptcha()" title="Refresh CAPTCHA">↻</button>
</div>


                <label for="captcha">Enter CAPTCHA</label>
                <input type="text" name="captcha" id="captcha" placeholder="Enter CAPTCHA" required>

                <button type="submit">Login</button>
            </form>

            <footer>
                <p>Don't have an account? <a href="register.php">Register here</a></p>
                <p>Forgot Password? <a href="#">Click here</a></p>
            </footer>
        </article>
    </section>
</main>

</body>
</html>
