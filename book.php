<?php
// book.php - Fichier Mouwahhad pour les 3 cliniques

session_start();
include("config.php"); 

// Vérification dial l'ID dial Clinique (Sécurité)
if (!isset($_GET['clinic_id']) || !in_array($_GET['clinic_id'], [1, 2, 3])) {
    header("Location: HomePage.php");
    exit();
}

$clinic_id = intval($_GET['clinic_id']);
$clinic_names = [1 => "Guéliz (Rue Numbre 01)", 2 => "Massira (Rue Numbre 02)", 3 => "Dawdiyat (Rue Numbre 03)"];
$clinic_name = "Clinique " . $clinic_id . " - " . $clinic_names[$clinic_id];

$error_message = ""; // Variable pour stocker les erreurs

// 1. GESTION DU POST (INSERTION)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire (Ajout time_slot)
    $username  = $_POST['username'];
    $lastname  = $_POST['lastname'];
    $email     = $_POST['email'];
    $tele      = $_POST['tele'];
    $problem   = $_POST['problem'];
    $date      = $_POST['date'];
    $time_slot = $_POST['time_slot']; 
    $appointment_datetime = $date . " " . $time_slot;

    // 2. VÉRIFICATION DE DISPONIBILITÉ 🚫
    $check_sql = $conn->prepare("SELECT id FROM appointments WHERE appointment_datetime = ? AND clinic_id = ? LIMIT 1");
    $check_sql->bind_param("si", $appointment_datetime, $clinic_id);
    $check_sql->execute();
    $check_sql->store_result();
    
    if ($check_sql->num_rows > 0) {
        $error_message = "❌ Error: This time slot is already booked for " . $clinic_name . ". Please choose another one.";
        $check_sql->close();
    } else {
        $check_sql->close();

        // 3. INSERTION DANS LA BASE DE DONNÉES (CORRECTE)
        $stmt = $conn->prepare("INSERT INTO appointments (username, lastname, email, tele, problem, appointment_datetime, clinic_id) 
                                VALUES (?, ?, ?, ?, ?, ?, ?)");

        if(!$stmt){
            // Had l'erreur katkoun ila kan chi mouchkil f structure dial l'DB
            die("❌ SQL Error: Cannot prepare statement. Check DB columns: " . $conn->error); 
        }

        $stmt->bind_param("ssssssi", $username, $lastname, $email, $tele, $problem, $appointment_datetime, $clinic_id);

        if($stmt->execute()){
            // STOCKAGE DE L'ID POUR LA PAGE DE CONFIRMATION
            $_SESSION['last_appointment_id'] = $conn->insert_id;
            header("location: confirmation.php");
            exit();
        } else {
            $error_message = "❌ Error during booking: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $clinic_name ?> - Book Appointment</title>
    <style>
        /* -------------------- Base & Background -------------------- */
        :root {
            --primary-blue: #007bff; /* أزرق أساسي للثقة و المهنية */
            --secondary-green: #28a745; /* أخضر للإجراءات الإيجابية */
            --white: #ffffff;
            --light-grey: #f8f9fa;
            --border-color: #ced4da;
            --shadow-color: rgba(0, 0, 0, 0.15);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--light-grey);
            /* Utilisation d'une image de fond ou couleur simple */
            /* background: url("denttt.jpg") no-repeat center center fixed; /* Ila kanti katsta3mel chi taswira */
            background-size: cover;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .main-wrapper {
            width: 90%;
            max-width: 600px;
            box-shadow: 0 10px 25px var(--shadow-color);
            border-radius: 15px;
            overflow: hidden;
        }

        /* -------------------- Header Banner -------------------- */
        .header-banner {
            background: linear-gradient(135deg, var(--primary-blue), #0056b3);
            color: var(--white);
            padding: 30px 20px;
            text-align: center;
        }

        .header-banner h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 500;
        }

        /* -------------------- Form Container -------------------- */
        .form-container {
            background: var(--white);
            padding: 30px;
            text-align: center;
        }

        .form-container h2 {
            color: var(--primary-blue);
            margin-bottom: 25px;
            font-size: 1.5rem;
            border-bottom: 2px solid var(--light-grey);
            padding-bottom: 10px;
        }

        .error-message {
            color: #dc3545;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        /* -------------------- Inputs & Labels -------------------- */
        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #495057;
        }

        input:not([type="submit"]), textarea, select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            box-sizing: border-box; /* مهم باش العرض يبقى مزيان */
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        /* -------------------- Date & Time Layout -------------------- */
        .date-time-group {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
        }

        .half-width {
            flex: 1; /* يقسم المساحة بالتساوي */
        }

        /* -------------------- Buttons -------------------- */
        .submit-btn, .back-btn {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn {
            background-color: var(--primary-blue);
            color: var(--white);
        }

        .submit-btn:hover {
            background-color: #0056b3;
        }

        .back-link {
            text-decoration: none;
            display: block;
            margin-top: 15px;
        }

        .back-btn {
            background-color: var(--secondary-green);
            color: var(--white);
        }

        .back-btn:hover {
            background-color: #1e7e34;
        }
        /* -------------------- Media Queries (Responsive) -------------------- */
        @media (max-width: 500px) {
            .date-time-group {
                flex-direction: column;
                gap: 0;
            }
            .main-wrapper {
                margin: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="header-banner">
            <h1>Welcome to <?= $clinic_name ?></h1>
        </div>
        
        <div class="form-container">
            <h2>Book Your Dental Appointment</h2>
            
            <?php if (!empty($error_message)) echo "<p class='error-message'>$error_message</p>"; ?>

            <form method="POST" action="book.php?clinic_id=<?= $clinic_id ?>">
                
                <div class="input-group">
                    <label for="username">First Name:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="input-group">
                    <label for="lastname">Last Name:</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>

                <div class="input-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="input-group">
                    <label for="tele">Telephone (10 digits):</label>
                    <input type="tel" id="tele" name="tele" required pattern="[0-9]{10}" placeholder="e.g., 06xxxxxxxx">
                </div>

                <div class="input-group">
                    <label for="problem">Your Concern / Problem:</label>
                    <textarea id="problem" name="problem" required rows="3"></textarea>
                </div>

                <div class="date-time-group">
                    <div class="input-group half-width">
                        <label for="date">Date:</label>
                        <input type="date" id="date" name="date" required min="<?= date('Y-m-d'); ?>">
                    </div>
                    
                    <div class="input-group half-width">
                        <label for="time_slot">Time Slot:</label>
                        <select id="time_slot" name="time_slot" required>
                            <option value="">-- Choose Time --</option>
                            <option value="09:00:00">09:00 - 10:00</option>
                            <option value="10:00:00">10:00 - 11:00</option>
                            <option value="11:00:00">11:00 - 12:00</option>
                            <option value="14:00:00">14:00 - 15:00</option>
                            <option value="15:00:00">15:00 - 16:00</option>
                            <option value="16:00:00">16:00 - 17:00</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="submit-btn">✅ Confirm Appointment</button>
            </form>

            <a href="HomePage.php" class="back-link">
                <button class="back-btn">⬅ Back to Homepage</button>
            </a>
        </div>
    </div>
</body>
</html>