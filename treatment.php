<?php
session_start();
include("config.php"); 

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['appointment_id']) || empty($_GET['appointment_id'])) {
    die("❌ Error: Appointment ID is missing.");
}

$appointment_id = intval($_GET['appointment_id']);
$success_message = "";
$treatment_data = [];

// ------------------------------------------------------------------
// PARTIE 1: GESTION DU POST (Sauvegarde/Update du Traitement)
// ------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $notes = $_POST['dentist_notes'];
    $cost = floatval($_POST['total_cost']);
    $next_visit = $_POST['next_visit_date'];
    $prescription = $_POST['prescription_text'];

    // Vérifier si un traitement existe déjà pour cet appointment
    $check_stmt = $conn->prepare("SELECT id FROM treatments WHERE appointment_id = ?");
    $check_stmt->bind_param("i", $appointment_id);
    $check_stmt->execute();
    $check_stmt->store_result();

    if ($check_stmt->num_rows > 0) {
        // UPDATE: Si traitement existe déjà
        $stmt = $conn->prepare("UPDATE treatments SET dentist_notes=?, total_cost=?, next_visit_date=?, prescription_text=? WHERE appointment_id=?");
        $stmt->bind_param("sdssi", $notes, $cost, $next_visit, $prescription, $appointment_id);
    } else {
        // INSERT: Si c'est le premier enregistrement
        $stmt = $conn->prepare("INSERT INTO treatments (appointment_id, dentist_notes, total_cost, next_visit_date, prescription_text) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $appointment_id, $notes, $cost, $next_visit, $prescription);
    }
    
    if ($stmt->execute()) {
        $success_message = "✅ Treatment and billing information saved successfully!";
    } else {
        $error_message = "❌ Error saving data: " . $stmt->error;
    }
    $stmt->close();
    $check_stmt->close();
}


// ------------------------------------------------------------------
// PARTIE 2: RÉCUPÉRATION DES DONNÉES (Rendez-vous et Traitement)
// ------------------------------------------------------------------

// 1. Récupérer les données du Rendez-vous
$appt_stmt = $conn->prepare("SELECT username, lastname, appointment_datetime FROM appointments WHERE id = ?");
$appt_stmt->bind_param("i", $appointment_id);
$appt_stmt->execute();
$appt_result = $appt_stmt->get_result();
$appointment_data = $appt_result->fetch_assoc();
$appt_stmt->close();


// 2. Récupérer les données de Traitement (s'il existe)
$treatment_stmt = $conn->prepare("SELECT * FROM treatments WHERE appointment_id = ?");
$treatment_stmt->bind_param("i", $appointment_id);
$treatment_stmt->execute();
$treatment_result = $treatment_stmt->get_result();
$treatment_data = $treatment_result->fetch_assoc();
$treatment_stmt->close();


// Préparer les valeurs par défaut pour le formulaire si pas de données de traitement
if (!$treatment_data) {
    $treatment_data = [
        'dentist_notes' => '', 
        'total_cost' => 0.00, 
        'next_visit_date' => '', 
        'prescription_text' => ''
    ];
}

// Formatage de la date de la prochaine visite
$next_visit_formatted = $treatment_data['next_visit_date'] ? $treatment_data['next_visit_date'] : '';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treatment & Billing for Appt #<?= $appointment_id ?></title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; }
        .container { max-width: 900px; margin: 30px auto; padding: 30px; background: #fff; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; margin-bottom: 25px; }
        
        .client-info { background-color: #ecf0f1; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .client-info p { margin: 5px 0; font-size: 1.1em; }
        .client-info span { font-weight: bold; color: #3498db; }
        
        .main-content { display: flex; gap: 30px; }
        .treatment-form { flex: 2; }
        .prescription-box { flex: 1; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
        
        /* Formulaire */
        .input-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; color: #495057; }
        input[type="text"], input[type="email"], input[type="date"], input[type="number"], textarea {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box;
        }
        textarea { resize: vertical; min-height: 100px; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; color: white; display: inline-block; margin-right: 10px; }
        .btn-save { background: #2ecc71; margin-top: 15px; }
        .btn-save:hover { background: #27ae60; }
        .btn-back { background: #3498db; }
        .btn-print { background: #e74c3c; margin-top: 10px; }
        
        .message-success { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        
        @media print {
            .btn, .main-content, .client-info, .message-success, h2 { display: none; }
            .prescription-box { display: block; width: 100%; box-shadow: none; border: none; }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Treatment & Billing for Appointment #<?= $appointment_id ?></h2>
        
        <?php if (!empty($success_message)) echo "<div class='message-success'>$success_message</div>"; ?>

        <div class="client-info">
            <p>Patient: <span><?= htmlspecialchars($appointment_data['username'] . ' ' . $appointment_data['lastname']); ?></span></p>
            <p>Appointment Time: <span><?= date("Y-m-d H:i", strtotime($appointment_data['appointment_datetime'])); ?></span></p>
        </div>

        <div class="main-content">
            
            <div class="treatment-form">
                <h3>Treatment & Billing Details</h3>
                <form method="POST" action="treatment.php?appointment_id=<?= $appointment_id ?>">
                    
                    <div class="input-group">
                        <label>Dentist's Notes (Diagnosis / Procedure Done):</label>
                        <textarea name="dentist_notes" required><?= htmlspecialchars($treatment_data['dentist_notes']); ?></textarea>
                    </div>
                    
                    <div class="input-group">
                        <label>Total Cost (MAD):</label>
                        <input type="number" name="total_cost" step="0.01" value="<?= htmlspecialchars($treatment_data['total_cost']); ?>" required>
                    </div>
                    
                    <div class="input-group">
                        <label>Next Visit Date (Optional):</label>
                        <input type="date" name="next_visit_date" value="<?= $next_visit_formatted; ?>">
                    </div>

                    <div class="input-group">
                        <label>Prescription / Medication (for printing):</label>
                        <textarea name="prescription_text"><?= htmlspecialchars($treatment_data['prescription_text']); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-save">💾 Save Treatment Data</button>
                    <a href="editAppointment.php?id=<?= $appointment_id ?>" class="btn btn-back">⬅ Back to Edit Appointment</a>
                </form>
            </div>
            
            <div class="prescription-box">
                <h4>🖨️ Prescription Area</h4>
                <p>This box shows what will be printed.</p>
                <div class="prescription-output">
                    <h5>Medication:</h5>
                    <p><?= nl2br(htmlspecialchars($treatment_data['prescription_text'])); ?></p>
                </div>
                
                <button class="btn btn-print" onclick="window.print()">Print Prescription</button>
            </div>
            
        </div>
    </div>
</body>
</html>