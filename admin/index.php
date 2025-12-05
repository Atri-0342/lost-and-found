<?php
session_name("admin_session");
session_start();
require '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Secure output function
function e($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// Count total admins
$stmt = $conn->query("SELECT COUNT(*) as total FROM admin");
$admin_count = $stmt->fetch()['total'] ?? 0;

// Count monthly active lost items
$month = date('m');
$year  = date('Y');
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM items WHERE MONTH(date_lost)=? AND YEAR(date_lost)=?");
$stmt->execute([$month, $year]);
$lost_count = $stmt->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<style>
:root {
    --primary: #fb1f1f;
    --primary-dark: #c70e0e;
    --bg: #f4f4f9;
    --text-dark: #333;
    --text-light: #555;
    --card-bg: #fff;
}

/* Basic Layout */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    background: var(--bg);
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

/* Header */
header {
    background-color: var(--primary);
    color: #fff;
    text-align: center;
    padding: 1rem 0;
}

header h1 {
    margin: 0;
    font-size: 1.8rem;
}

/* Dashboard Section */
main {
    flex: 1;
    padding: 30px 20px;
}

.welcome {
    text-align: center;
    font-size: 1.6rem;
    color: var(--text-dark);
    margin-bottom: 25px;
}

/* Cards Layout */
.dashboard {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.card {
    flex: 1 1 250px;
    max-width: 280px;
    background: var(--card-bg);
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.15);
}

.card h3 {
    margin: 0;
    color: var(--text-light);
    font-weight: 500;
}

.card h2 {
    font-size: 2.2rem;
    color: var(--primary-dark);
    margin: 10px 0 0;
}

.time-box {
    font-size: 1.5rem;
    color: var(--text-dark);
    margin-top: 10px;
}

/* Footer */
footer {
    background: var(--primary);
    color: #fff;
    text-align: center;
    padding: 10px;
    font-size: 0.9rem;
}

/* Responsive */
@media (max-width: 768px) {
    .dashboard {
        flex-direction: column;
        align-items: center;
    }
}
</style>
</head>
<body>

<!-- Navigation Bar -->
<?php require 'nav.php'; ?>

<!-- Header -->
<header>
    <h1>Admin Dashboard</h1>
</header>

<!-- Main Dashboard -->
<main>
    <p class="welcome">Welcome, <?= e($_SESSION['admin_name']); ?> 👋</p>

    <section class="dashboard">
        <article class="card">
            <h3>Current Time</h3>
            <div class="time-box" id="time"></div>
        </article>

        <article class="card">
            <h3>Total Admins</h3>
            <h2><?= e($admin_count) ?></h2>
        </article>

        <article class="card">
            <h3>Monthly Active Lost Items</h3>
            <h2><?= e($lost_count) ?></h2>
        </article>
    </section>
</main>

<!-- Footer -->
<?php require 'footer.php'; ?>

<script>
function updateTime() {
    const now = new Date();
    document.getElementById('time').textContent = now.toLocaleTimeString();
}
setInterval(updateTime, 1000);
updateTime();
</script>

</body>
</html>
