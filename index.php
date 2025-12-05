<?php

session_name("student_session");
session_start();
require 'db.php';

// Detect login
$is_logged_in = isset($_SESSION['user_id']);
$user_id    = $_SESSION['user_id'] ?? null;
$user_name  = $_SESSION['user_name'] ?? 'Guest';
$college_id = $_SESSION['college_id'] ?? null;

// =============================
//  SEARCH FEATURE (SECURE INPUT)
// =============================
$search = trim($_GET['search'] ?? '');
$search = filter_var($search, FILTER_SANITIZE_STRING);
$params = [];

// Base SQL query
$sql = "SELECT items.*, users.name FROM items 
        JOIN users ON items.student_id = users.college_id";

// Apply search condition if given
if (!empty($search)) {
    $sql .= " WHERE items.title LIKE :s 
              OR items.description LIKE :s 
              OR items.location LIKE :s 
              OR items.date_lost LIKE :s";
    $params[':s'] = "%$search%";
}

$sql .= " ORDER BY items.date_lost DESC";

// Execute safely
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red;text-align:center;'>Error loading items. Please try again later.</p>");
}

// =============================
//  DASHBOARD STATS
// =============================
$total_items = $conn->query("SELECT COUNT(*) AS total FROM items")->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Stats for logged-in user
$my_items = 0;
if ($is_logged_in && $college_id) {
    $my_items_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM items WHERE student_id = ?");
    $my_items_stmt->execute([$college_id]);
    $my_items = $my_items_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

// Monthly stats
$month = date('m');
$year  = date('Y');
$monthly_items_stmt = $conn->prepare("SELECT COUNT(*) AS total FROM items WHERE MONTH(date_lost) = ? AND YEAR(date_lost) = ?");
$monthly_items_stmt->execute([$month, $year]);
$monthly_items = $monthly_items_stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Dashboard - Lost & Found</title>
<style>
/* ===== GENERAL ===== */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    background: #f4f4f9;
    color: #333;
}
main {
    padding-bottom: 40px;
}

/* ===== NAVIGATION ===== */
nav {
    background: #007BFF;
    color: #fff;
    padding: 14px 20px;
    text-align: center;
}
nav a {
    color: #fff;
    text-decoration: none;
    margin: 0 15px;
    transition: color 0.3s;
}
nav a:hover {
    text-decoration: underline;
}

/* ===== WELCOME ===== */
.welcome {
    text-align: center;
    padding: 25px 20px 10px 20px;
    font-size: 1.8em;
}

/* ===== DASHBOARD ===== */
.dashboard {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
    padding: 20px;
}
.card {
    flex: 1 1 200px;
    max-width: 250px;
    background: #fff;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.2s, box-shadow 0.2s;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
}
.card h3 {
    color: #555;
    margin: 10px 0;
}
.card h2 {
    color: #007BFF;
    font-size: 2em;
}

/* ===== SEARCH ===== */
.search-form {
    text-align: center;
    margin: 20px 0;
}
.search-form input[type="text"] {
    padding: 10px;
    width: 250px;
    border-radius: 5px;
    border: 1px solid #ccc;
    transition: border 0.3s;
}
.search-form input[type="text"]:focus {
    border-color: #007BFF;
    outline: none;
}
.search-form button {
    padding: 10px 15px;
    border: none;
    border-radius: 5px;
    background: #007BFF;
    color: #fff;
    cursor: pointer;
    transition: background 0.3s;
}
.search-form button:hover {
    background: #0056b3;
}

/* ===== TABLE ===== */
.items-table {
    width: 95%;
    margin: 0 auto 30px;
    border-collapse: collapse;
}
.items-table th, .items-table td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
    vertical-align: top;
}
.items-table th {
    background-color: #007BFF;
    color: white;
}
.items-table tr:nth-child(even) {
    background-color: #f9f9f9;
}
.items-table img {
    max-width: 80px;
    height: auto;
    border-radius: 5px;
}
.items-table a {
    color: #007BFF;
    text-decoration: none;
    margin-right: 5px;
}
.items-table a:hover {
    text-decoration: underline;
}

/* ===== NO DATA ===== */
.no-data {
    text-align: center;
    margin-top: 20px;
    font-size: 1.2em;
    color: #777;
}

/* ===== MESSAGES ===== */
.success {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 8px;
    margin: 10px auto;
    width: fit-content;
}

/* ===== RESPONSIVENESS ===== */
@media (max-width: 768px) {
    .dashboard {
        flex-direction: column;
        align-items: center;
    }
    .items-table {
        font-size: 0.9em;
        overflow-x: auto;
        display: block;
    }
    nav {
        font-size: 0.9em;
    }
}
</style>
</head>
<body>

<?php include "nav.php"; ?>

<main>
    <section class="welcome">
        Hello, <?php echo htmlspecialchars($user_name); ?> 👋
    </section>

    <section class="dashboard">
        <div class="card">
            <h3>Current Time</h3>
            <h2 id="time"></h2>
        </div>
        <div class="card">
            <h3>Total Lost Items</h3>
            <h2><?php echo $total_items; ?></h2>
        </div>
        <?php if ($is_logged_in): ?>
        <div class="card">
            <h3>My Reported Items</h3>
            <h2><?php echo $my_items; ?></h2>
        </div>
        <?php endif; ?>
        <div class="card">
            <h3>Monthly Active Items</h3>
            <h2><?php echo $monthly_items; ?></h2>
        </div>
    </section>

    <section>
        <form method="GET" action="index.php" class="search-form" onsubmit="return validateSearch();">
            <input type="text" name="search" id="search" placeholder="Search lost items..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </section>

    <?php
    if (isset($_SESSION['success'])) {
        echo "<p class='success'>" . htmlspecialchars($_SESSION['success']) . "</p>";
        unset($_SESSION['success']);
    }
    ?>

    <section>
        <?php if ($items): ?>
        <h2 style="text-align:center; margin-bottom:15px;">Lost Items Reported</h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Location</th>
                    <th>Date Lost</th>
                    <th>Reported By</th>
                    <th>Message</th>
                    <th>Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['title']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($item['description'])); ?></td>
                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                    <td><?php echo htmlspecialchars($item['date_lost']); ?></td>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo nl2br(htmlspecialchars($item['message'])); ?></td>
                    <td>
                        <?php if (!empty($item['image']) && file_exists($item['image'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="Item Image">
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($is_logged_in && $college_id == $item['student_id']): ?>
                            <a href="update_item.php?id=<?php echo $item['id']; ?>">Edit</a>
                            <a href="delete_item.php?id=<?php echo $item['id']; ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p class="no-data">No lost items found<?php echo $search ? " for \"$search\"" : ""; ?>.</p>
        <?php endif; ?>
    </section>
</main>
<?php include "footer.php"?>
<script>
// ========== CLOCK ==========
function updateTime() {
    const now = new Date();
    document.getElementById('time').textContent = now.toLocaleTimeString();
}
setInterval(updateTime, 1000);
updateTime();

// ========== SEARCH VALIDATION ==========
function validateSearch() {
    const input = document.getElementById('search').value.trim();
    if (input.length > 0 && input.length < 2) {
        alert("Please enter at least 2 characters to search.");
        return false;
    }
    return true;
}
</script>

</body>
</html>
