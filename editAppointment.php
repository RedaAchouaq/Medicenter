<?php
session_start();
// Hami l'page dial l'admin
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("config.php"); 

// Vérification dial l'ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: ViewAppointments.php");
    exit();
}

$appointment_id = intval($_GET['id']);
$clinic_names = [1 => "Clinique 1 - Guéliz", 2 => "Clinique 2 - Massira", 3 => "Clinique 3 - Dawdiyat"];
$error_message = "";
$success_message = "";

// ------------------------------------------------------------------
// PARTIE 1: GESTION DU POST (MISE À JOUR DE LA BASE DE DONNÉES)
// ------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données
    $username  = $_POST['username'];
    $lastname  = $_POST['lastname'];
    $email     = $_POST['email'];
    $tele      = $_POST['tele'];
    $problem   = $_POST['problem'];
    $clinic_id = intval($_POST['clinic_id']); // Récupération de l'ID du clinique
    $date      = $_POST['date'];
    $time_slot = $_POST['time_slot'];
    $appointment_datetime = $date . " " . $time_slot;
    $status    = $_POST['status'] ?? 'Pending'; // Assurez-vous d'avoir une colonne 'status' dans votre DB

    // Requête de mise à jour (UPDATE)
    $stmt = $conn->prepare("UPDATE appointments 
                            SET username=?, lastname=?, email=?, tele=?, problem=?, appointment_datetime=?, clinic_id=? 
                            WHERE id=?");
    
    // Khassak tbeddel had bind_param ila zedti la colonne status
    $stmt->bind_param("ssssssii", $username, $lastname, $email, $tele, $problem, $appointment_datetime, $clinic_id, $appointment_id);

    if ($stmt->execute()) {
        $success_message = "✅ Appointment ID $appointment_id updated successfully!";
        // Hada bach n'choufou l'modifications dghia f l'formulaire
        // Ghadi n3awdou n'jebd les informations l'jdad men l'DB
    } else {
        $error_message = "❌ Error updating appointment: " . $stmt->error;
    }
    $stmt->close();
}

// ------------------------------------------------------------------
// PARTIE 2: RÉCUPÉRATION DES DONNÉES ACTUELLES (POUR AFFICHAGE DANS LE FORMULAIRE)
// ------------------------------------------------------------------
$stmt = $conn->prepare("SELECT id, username, lastname, email, tele, problem, appointment_datetime, clinic_id 
                        FROM appointments WHERE id = ?");
$stmt->bind_param("i", $appointment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Appointment not found.");
}
$appointment_data = $result->fetch_assoc();
$stmt->close();

// Préparation dial les données bach ybanou f l'HTML
$current_date = date("Y-m-d", strtotime($appointment_data['appointment_datetime']));
$current_time = date("H:i:s", strtotime($appointment_data['appointment_datetime']));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment #<?= $appointment_id ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; }
        .container { max-width: 600px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; margin-bottom: 25px; }
        
        /* Formulaire */
        .input-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        input[type="text"], input[type="email"], input[type="date"], select, textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;
        }
        .date-time-group { display: flex; gap: 10px; }
        .half-width { flex: 1; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; color: white; display: inline-block; margin-right: 10px; }
        .btn-update { background: #3498db; }
        .btn-back { background: #2ecc71; }
        
        .message-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .message-error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }

        /* Zid had l'class m3a les classes l'akhriin */
        .btn-treatment { 
            background: #9b59b6; /* Couleur violette */
            margin-right: 10px;
        }
        .btn-treatment:hover {
            background: #8e44ad; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>✏️ Edit Appointment #<?= $appointment_id ?></h2>
        
        <?php if (!empty($success_message)) echo "<div class='message-success'>$success_message</div>"; ?>
        <?php if (!empty($error_message)) echo "<div class='message-error'>$error_message</div>"; ?>
        
        <form method="POST" action="editAppointment.php?id=<?= $appointment_id ?>">
            
            <div class="input-group">
                <label>First Name:</label>
                <input type="text" name="username" value="<?= htmlspecialchars($appointment_data['username']); ?>" required>
            </div>
            
            <div class="input-group">
                <label>Last Name:</label>
                <input type="text" name="lastname" value="<?= htmlspecialchars($appointment_data['lastname']); ?>" required>
            </div>
            
            <div class="input-group">
                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($appointment_data['email']); ?>" required>
            </div>

            <div class="input-group">
                <label>Telephone:</label>
                <input type="text" name="tele" value="<?= htmlspecialchars($appointment_data['tele']); ?>" required>
            </div>
            
            <div class="input-group">
                <label>Your Problem:</label>
                <textarea name="problem" required><?= htmlspecialchars($appointment_data['problem']); ?></textarea>
            </div>

            <div class="input-group">
                <label>Clinique:</label>
                <select name="clinic_id" required>
                    <?php foreach ($clinic_names as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($appointment_data['clinic_id'] == $id) ? 'selected' : ''; ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="date-time-group">
                <div class="input-group half-width">
                    <label>Date:</label>
                    <input type="date" name="date" value="<?= $current_date; ?>" required>
                </div>
                
                <div class="input-group half-width">
                    <label>Time Slot:</label>
                    <select name="time_slot" required>
                        <option value="09:00:00" <?= ($current_time == '09:00:00') ? 'selected' : ''; ?>>09:00 - 10:00</option>
                        <option value="10:00:00" <?= ($current_time == '10:00:00') ? 'selected' : ''; ?>>10:00 - 11:00</option>
                        <option value="11:00:00" <?= ($current_time == '11:00:00') ? 'selected' : ''; ?>>11:00 - 12:00</option>
                        <option value="14:00:00" <?= ($current_time == '14:00:00') ? 'selected' : ''; ?>>14:00 - 15:00</option>
                        <option value="15:00:00" <?= ($current_time == '15:00:00') ? 'selected' : ''; ?>>15:00 - 16:00</option>
                        <option value="16:00:00" <?= ($current_time == '16:00:00') ? 'selected' : ''; ?>>16:00 - 17:00</option>
                    </select>
                </div>
            </div>

          

            <a href="treatment.php?appointment_id=<?= $appointment_id ?>" class="btn btn-treatment">
                🦷 View Treatment & Billing
            </a>
            
            <button type="submit" class="btn btn-update">💾 Update Appointment</button>
            <a href="ViewAppointments.php" class="btn btn-back">⬅ Back to List</a>
        </form>
    </div>
</body>
</html>