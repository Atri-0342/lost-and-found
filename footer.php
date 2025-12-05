<style>
footer {
    background: #007BFF;
    color: #fff;
    padding: 20px 0;
    text-align: center;
    margin-top: 40px;
}

.footer-content {
    max-width: 900px;
    margin: 0 auto;
    padding: 0 15px;
}

.footer-content p {
    margin-bottom: 10px;
    font-size: 14px;
}

footer nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

footer nav ul li {
    display: inline-block;
    margin: 0 10px;
}

footer nav ul li a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
}

footer nav ul li a:hover {
    text-decoration: underline;
}
@media (max-width: 600px) {
    footer nav ul li {
        display: block;
        margin: 8px 0;
    }
}
</style>
<footer>
    <section class="footer-content">
        <p>&copy; <?= date('Y'); ?> Lost & Found Portal. All rights reserved.</p>
        <nav aria-label="Footer Navigation">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="report_lost.php">Report Lost Item</a></li>
                <li><a href="contact.php">Contact</a></li>
            </ul>
        </nav>
    </section>
</footer>


