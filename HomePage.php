<?php
// HomePage.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Clinic Home</title>
    <link rel="stylesheet" href="Hc.css">
    <style></style>
</head>
<body>
    
    <h1>Welcome to Our Dental Clinic 🦷</h1>

    <!-- Admin button -->
    <a href="login.php">
        <button class="btn admin-btn">🔑 Admin Login</button>
  </a>
    <nav>
        <a href="HomePage.php">HOME</a>
        <a href="#">ABOUT US</a>
        <a href="#">SERVICES</a>
        <a href="#">CONTACT US</a>
    </nav>

    <div class="container">
        <h1>Welcome In Your Clinic</h1>
        <p>Visit my clinic every 6 months for a professional cleaning and check-up.
        Your oral health is an important part of your overall health, and regular visits allow me to detect and treat issues early.</p>
        
        <h2>Dear patients:</h2>
        <p>Votre texte de paragrapheTo keep your smile healthy and beautiful,            I encourage you to:

        Brush your teeth twice a day with fluoride toothpaste.
        Floss daily to clean between your teeth.
        Limit sugar and acidic drinks, and drink plenty of water.
        Don’t ignore sensitivity or pain—small problems can become serious if untreated.</p>

        <h2>Clinique 1</h2>
        <p>Marrakech Guéliz, Rue Numbre01</p>
        <P><small>Imeuble reda01 etage01 Nb001 Tele:050606000</small></P>
        <a href="book.php?clinic_id=1"><button>Get an Appointment in Clinique 1</button></a>

        <h2>Clinique 2</h2>
        <p>Marrakech Massira, Rue Numbre02</p>
        <P><small>Imeuble Reda02 Etage02 Nb002
                Tele:0505050500</small></P>
       <a href="book.php?clinic_id=2"><button>Get an Appointment in Clinique 2</button></a>

        <h2>Clinique 3</h2>
        <p>Marrakech Dawdiyat, Rue Numbre03</p>
        <p><small>Imeuble Reda03 Etage03 Nb003
                Tele:0504040400</small></p>
        <a href="book.php?clinic_id=3"><button>Get an Appointment in Clinique 3</button></a>
    </div>
</body>
</html>
