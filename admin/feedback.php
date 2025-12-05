<?php
session_name("admin_session");
session_start();
require '../db.php';

// Redirect if not logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle single feedback delete
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = "Feedback deleted successfully!";
    header("Location: feedback.php");
    exit;
}

// Handle delete all feedback
if (isset($_GET['delete_all']) && $_GET['delete_all'] == 1) {
    $conn->exec("DELETE FROM feedback");
    $_SESSION['success'] = "All feedback deleted successfully!";
    header("Location: feedback.php");
    exit;
}

// Fetch all feedback
$stmt = $conn->query("
    SELECT feedback.*, users.name, users.college_id
    FROM feedback 
    JOIN users ON feedback.student_id = users.college_id 
    ORDER BY feedback.created_at DESC
");
$feedbacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback Management - Admin Panel</title>
<style>
:root {
    --primary: #009879;
}

/* Make footer stick to bottom */
html, body {
    height: 100%;
    margin: 0;
}

body {
    font-family: 'Poppins', sans-serif;
    background: #f4f6f8;
    color: #333;
    display: flex;
    flex-direction: column;
}

/* Main content expands naturally */
main {
    flex: 1;
}

/* Container */
.container {
    max-width: 1100px;
    margin: 40px auto;
    background: #fff;
    padding: 25px 35px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

/* Heading */
h1 {
    text-align: center;
    color: var(--primary);
    font-size: 2em;
    margin-bottom: 15px;
}

/* Success message */
.success-msg {
    background: #d4edda;
    color: #155724;
    padding: 10px 15px;
    border-radius: 8px;
    margin: 15px auto;
    width: fit-content;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* Delete All button */
.delete-all {
    display: block;
    background: #e74c3c;
    color: #fff;
    text-align: center;
    padding: 10px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    width: 220px;
    margin: 10px auto 20px;
    transition: background 0.3s;
}
.delete-all:hover {
    background: #c0392b;
}

/* Table styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    overflow-x: auto;
}
th, td {
    padding: 12px 10px;
    border-bottom: 1px solid #ddd;
    text-align: left;
}
th {
    background-color: var(--primary);
    color: #fff;
}
tr:nth-child(even) {
    background: #f9f9f9;
}
tr:hover {
    background: #eef9f5;
}

/* Action buttons */
.action-btns a {
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 5px;
    color: #fff;
    font-size: 0.9em;
    transition: background 0.3s;
}
.delete { background-color: #e74c3c; }
.delete:hover { background-color: #c0392b; }

/* No data message */
.no-data {
    text-align: center;
    font-size: 1.2em;
    color: #777;
    margin-top: 20px;
}

/* Footer handled separately via footer.php */

/* Responsive */
@media (max-width: 768px) {
    .container {
        padding: 20px;
    }
    table {
        font-size: 0.9em;
    }
}
</style>
</head>
<body>

<?php require 'nav.php'; ?>

<main>
    <div class="container">
        <h1>Student Feedback</h1>

        <!-- Success message -->
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success-msg"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
        <?php endif; ?>

        <!-- Delete All -->
        <?php if ($feedbacks): ?>
            <a href="feedback.php?delete_all=1" class="delete-all" onclick="return confirm('Are you sure you want to delete all feedback?');">
                Delete All Feedback
            </a>
        <?php endif; ?>

        <!-- Feedback Table -->
        <?php if ($feedbacks): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student Name</th>
                    <th>College ID</th>
                    <th>Subject</th>
                    <th>Message</th>
                    <th>Submitted At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $fb): ?>
                <tr>
                    <td><?= $fb['id'] ?></td>
                    <td><?= htmlspecialchars($fb['name']) ?></td>
                    <td><?= htmlspecialchars($fb['college_id']) ?></td>
                    <td><?= htmlspecialchars($fb['subject']) ?></td>
                    <td><?= nl2br(htmlspecialchars($fb['message'])) ?></td>
                    <td><?= htmlspecialchars(date("d M Y, h:i A", strtotime($fb['created_at']))) ?></td>
                    <td class="action-btns">
                        <a href="feedback.php?delete_id=<?= $fb['id'] ?>" class="delete" onclick="return confirm('Delete this feedback?');">
                            Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p class="no-data">No feedback submitted yet.</p>
        <?php endif; ?>
    </div>
</main>

<?php include "footer.php"; ?>

</body>
</html>
