<?php
session_name("student_session");
session_start();
require 'db.php'; // your PDO connection

function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$error = '';
$success = '';

if (!isset($_SESSION['college_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        $error = "⚠ Please fill in all required fields.";
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (student_id, subject, message, created_at) VALUES (?, ?, ?, NOW())");
        $inserted = $stmt->execute([$_SESSION['college_id'], $subject, $message]);

        if ($inserted) {
            $success = "✅ Your feedback has been submitted successfully!";
        } else {
            $error = "❌ Something went wrong. Please try again later.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Submit Feedback - Lost & Found Portal</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
:root {
    --primary: #007BFF;
    --primary-dark: #0056b3;
    --success-bg: #e8ffea;
    --error-bg: #ffe0e0;
    --success-text: #155724;
    --error-text: #b30000;
    --shadow: rgba(0, 0, 0, 0.08);
}

body {
    margin: 0;
    font-family: 'Poppins', sans-serif;
    background: #f4f6fa;
    color: #333;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

header, footer {
    background: var(--primary);
    color: #fff;
    text-align: center;
    padding: 15px;
}

main {
    flex: 1;
    padding: 20px;
}

section.feedback-section {
    max-width: 650px;
    margin: 40px auto;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 10px 25px var(--shadow);
    padding: 35px;
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to { opacity: 1; transform: translateY(0); }
}

h1 {
    text-align: center;
    color: var(--primary);
    font-size: 1.8em;
}

form {
    margin-top: 20px;
}

label {
    display: block;
    margin-top: 15px;
    font-weight: 500;
}

input,
textarea {
    width: 100%;
    padding: 12px 15px;
    margin-top: 8px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 15px;
    transition: border-color 0.3s, box-shadow 0.3s;
}

input:focus,
textarea:focus {
    border-color: var(--primary);
    box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
    outline: none;
}

button {
    margin-top: 20px;
    padding: 12px;
    width: 100%;
    border: none;
    border-radius: 8px;
    background: var(--primary);
    color: white;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.3s ease;
}

button:hover {
    background: var(--primary-dark);
}

.error-msg,
.success-msg {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: 500;
    text-align: center;
}

.error-msg {
    background: var(--error-bg);
    color: var(--error-text);
    border-left: 5px solid var(--error-text);
}

.success-msg {
    background: var(--success-bg);
    color: var(--success-text);
    border-left: 5px solid var(--success-text);
}

footer a {
    color: #fff;
    text-decoration: underline;
}
footer a:hover {
    color: #dceeff;
}
</style>
</head>
<body>

    <?php include "nav.php"; ?>

<main>
    <section class="feedback-section">
        <article>
            <h1>💬 Submit Your Feedback</h1>

            <?php if ($error): ?>
                <div class="error-msg"><?= e($error) ?></div>
            <?php elseif ($success): ?>
                <div class="success-msg"><?= e($success) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="subject">Subject</label>
                <input type="text" name="subject" id="subject" placeholder="Enter subject" value="<?= e($_POST['subject'] ?? '') ?>" required>

                <label for="message">Message / Issue</label>
                <textarea name="message" id="message" placeholder="Describe your feedback or issue..." rows="6" required><?= e($_POST['message'] ?? '') ?></textarea>

                <button type="submit">Submit Feedback</button>
            </form>
        </article>
    </section>
</main>
    <?php include "footer.php"; ?>

</body>
</html>
