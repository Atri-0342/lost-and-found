<?php
session_name("student_session");
session_start();
require 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['user_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Contact Us - Lost & Found Portal</title>
<style>
body {
    font-family: 'Segoe UI', Arial, sans-serif;
    margin: 0;
    background-color: #f4f4f9;
    color: #333;
}

/* Navigation */
header nav {
    background: #007BFF;
    color: white;
    padding: 12px 20px;
    text-align: center;
}
header nav a {
    color: white;
    text-decoration: none;
    margin: 0 15px;
}
header nav a:hover {
    text-decoration: underline;
}

/* Container */
main {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    border-radius: 10px;
    padding: 30px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}
main h2 {
    text-align: center;
    color: #007BFF;
}

/* Info Section */
section.contact-info {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    margin-top: 25px;
}
article.info-box {
    flex: 1 1 250px;
    margin: 10px;
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    transition: transform 0.3s;
}
article.info-box:hover {
    transform: translateY(-4px);
}
article.info-box h3 {
    color: #007BFF;
    margin-bottom: 10px;
}

/* FAQ Section */
section.faq-section {
    margin-top: 40px;
}
.faq {
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
    overflow: hidden;
    border-left: 4px solid #007BFF;
}
.faq button {
    background: none;
    border: none;
    width: 100%;
    text-align: left;
    font-size: 1.1em;
    padding: 15px;
    cursor: pointer;
    font-weight: bold;
    color: #007BFF;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.faq button:hover {
    background-color: #eef5ff;
}
.faq-content {
    padding: 0 15px 15px 15px;
    display: none;
    animation: fadeIn 0.3s ease-in-out;
}
@keyframes fadeIn {
    from {opacity: 0;}
    to {opacity: 1;}
}

/* Map */
section.map {
    margin-top: 40px;
    text-align: center;
}
section.map iframe {
    width: 100%;
    height: 300px;
    border: 0;
    border-radius: 10px;
}

/* Contact Form */
section.contact-form {
    margin-top: 40px;
}
section.contact-form label {
    display: block;
    margin-top: 10px;
}
section.contact-form input,
section.contact-form textarea {
    width: 100%;
    padding: 10px;
    margin-top: 5px;
    border: 1px solid #ccc;
    border-radius: 5px;
}
section.contact-form button {
    margin-top: 15px;
    padding: 10px 20px;
    border: none;
    background: #007BFF;
    color: white;
    border-radius: 5px;
    cursor: pointer;
}
section.contact-form button:hover {
    background: #0056b3;
}

/* Responsive */
@media (max-width: 768px) {
    section.contact-info {
        flex-direction: column;
        align-items: center;
    }
    article.info-box {
        width: 90%;
    }
}
</style>
</head>
<body>

    <?php include "nav.php"; ?>

<main>
    <header>
        <h2>📞 Contact the Lost & Found Association</h2>
        <p style="text-align:center;">Have a question or found something? We’re here to help!</p>
    </header>

    <section class="contact-info">
        <article class="info-box">
            <h3>Association Office</h3>
            <p><strong>Address:</strong> Heritage Institute Of Technology, Kolkata</p>
            <p><strong>Email:</strong> lostfound@hitk.edu</p>
            <p><strong>Phone:</strong> +91 1234567890</p>
        </article>

        <article class="info-box">
            <h3>Working Hours</h3>
            <p>Monday – Friday: 9 AM – 6 PM</p>
            <p>Saturday: 10 AM – 4 PM</p>
            <p>Sunday: Closed</p>
        </article>

        <article class="info-box">
            <h3>Student Coordinator</h3>
            <p><strong>Name:</strong> Raja Sen</p>
            <p><strong>Email:</strong> Raja.sen@hitk.edu</p>
            <p><strong>Phone:</strong> +91 1234567890</p>
        </article>
    </section>

    <section class="faq-section" aria-labelledby="faq-heading">
        <h2 id="faq-heading">❓ Frequently Asked Questions</h2>

        <article class="faq">
            <button>How can I report a lost item? <span>➕</span></button>
            <div class="faq-content">
                <p>Login to your account and click on “Report Lost Item”. Fill in the details and submit it.</p>
            </div>
        </article>

        <article class="faq">
            <button>Can I edit my submitted report? <span>➕</span></button>
            <div class="faq-content">
                <p>Yes, you can edit or delete your report anytime from your dashboard.</p>
            </div>
        </article>

        <article class="faq">
            <button>How long are found items kept? <span>➕</span></button>
            <div class="faq-content">
                <p>Items are stored for 3 months before being handed over to the authorities.</p>
            </div>
        </article>

        <article class="faq">
            <button>Where is the Lost & Found office located? <span>➕</span></button>
            <div class="faq-content">
                <p>The Lost & Found office is located inside the Student Affairs Building, Ground Floor, Block C.</p>
            </div>
        </article>
    </section>

    <section class="map" aria-label="Office Location">
        <h2>📍 Our Location</h2>
        <iframe 
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3683.911957451366!2d88.3674309740993!3d22.56111583229424!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3a02770146f7d0df%3A0x6e5f278f6f52ee7e!2sKolkata%20University!5e0!3m2!1sen!2sin!4v1730845031652!5m2!1sen!2sin"
            allowfullscreen
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade">
        </iframe>
    </section>
</main>

    <?php include "footer.php"; ?>

<script>
// FAQ Toggle
document.querySelectorAll('.faq button').forEach(button => {
    button.addEventListener('click', () => {
        const faq = button.parentElement;
        const content = button.nextElementSibling;
        const open = content.style.display === 'block';
        document.querySelectorAll('.faq-content').forEach(c => c.style.display = 'none');
        document.querySelectorAll('.faq button span').forEach(s => s.textContent = '➕');
        if (!open) {
            content.style.display = 'block';
            button.querySelector('span').textContent = '➖';
        }
    });
});
</script>

</body>
</html>
