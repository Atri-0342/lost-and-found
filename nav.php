<style>
/* Nav container */
body {
    margin: 0;
    font-family: Arial, sans-serif;
}

nav {
    background: #007BFF;
    padding: 15px 0;
    text-align: center;
}

/* Nav links */
nav a {
    color: #fff;
    text-decoration: none;
    margin: 0 20px;
    font-size: 16px;
    font-weight: bold;
    transition: color 0.3s, transform 0.2s;
}

/* Hover effect */
nav a:hover {
    color: #ffd700; /* gold color on hover */
    transform: scale(1.1);
}

/* Responsive for smaller screens */
@media (max-width: 600px) {
    nav a {
        display: block;
        margin: 10px 0;
    }
}
</style>

<nav>
    <a href="index.php">Home</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="report.php">Report Lost Item</a>
        <a href="feedback.php">Feedback</a>
        <a href="contact.php">Contact</a>
        <a href="user_logout.php">Logout</a>
    <?php else: ?>
        <a href="user_login.php">Login</a>
        <a href="register.php">Register</a>
        <a href="contact.php">Contact</a>
    <?php endif; ?>
</nav>
