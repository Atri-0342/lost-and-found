<style>
nav {
    background-color: #fb1f1fff;
    padding: 10px 20px;
    text-align: center; /* Center the links */
}
nav a {
    color: #fff;
    text-decoration: none;
    margin: 0 15px; /* Horizontal spacing between links */
    font-weight: bold;
    transition: background 0.3s, color 0.3s;
    padding: 8px 12px;
    border-radius: 5px;
    display: inline-block; /* Ensure proper spacing */
}
nav a:hover {
    background-color: #fff;
    color: #333;
}
</style>

<nav>
    <?php if (isset($_SESSION['admin_id'])): ?>
        <a href="index.php">Home</a>
        <a href="add_admin.php">Add Admin</a>
        <a href="feedback.php">Feedback</a>
        <a href="report.php">Reports</a>
        <a href="logout.php">Logout</a>
    <?php endif; ?>
</nav>
