<?php
session_start();
// Vérification dial l'Admin Session (Khassha tkoun f l'awal!)
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

include("config.php"); 

// Définir les noms des cliniques pour l'affichage
$clinic_names = [
    1 => "Clinique 1 (Guéliz)",
    2 => "Clinique 2 (Massira)",
    3 => "Clinique 3 (Dawdiyat)"
];

// Gestion de l'action de suppression (DELETE)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Utilisation des Prepared Statements pour la sécurité (MOULAHADA: Khassak tbeddel hadchi f l'code dialek!)
    $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: ViewAppointments.php"); // refresh page
    exit();
}

// ----------------------------------------------------
// NQADOU L'FILTRE DIAL L'CLINIQUE
// ----------------------------------------------------
$selected_clinic = null;
$where_clause = "";

if (isset($_GET['clinic']) && in_array($_GET['clinic'], [1, 2, 3])) {
    $selected_clinic = intval($_GET['clinic']);
    $where_clause = " WHERE clinic_id = " . $selected_clinic;
}

// Récupération de tous les rendez-vous (m3a modification dial appointment_date l'appointment_datetime)
$sql = "SELECT id, username, lastname, email, tele, problem, appointment_datetime, clinic_id, created_at 
        FROM appointments" . $where_clause . " ORDER BY appointment_datetime DESC"; // Tri par date
        
$result = $conn->query($sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Appointments</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f7f6; }
        nav { background: #34495e; padding: 15px; text-align: center; }
        nav a { color: white; margin: 0 15px; text-decoration: none; font-weight: bold; }
        .container { max-width: 1300px; margin: 20px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
        h2 { color: #2c3e50; border-bottom: 2px solid #ecf0f1; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 12px; text-align: left; font-size: 0.9em; }
        table th { background: #3498db; color: white; text-transform: uppercase; }
        
        .btn { padding: 8px 12px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; color: white; display: inline-block; margin: 2px 0; }
        .btn-delete { background: #e74c3c; }
        .btn-edit { background: #f39c12; }

        .filter-section { margin-bottom: 20px; text-align: center; }
        .filter-section select { padding: 10px; border-radius: 5px; border: 1px solid #ccc; }
        .filter-section button { padding: 10px 15px; background: #2ecc71; color: white; border: none; border-radius: 5px; cursor: pointer; }
    </style>
</head>
<body>
    <nav>
        <a href="HomePage.php">HOME</a>
        <a href="ViewAppointments.php">ADMIN - View Appointments</a>
        <a href="login.php">LOGOUT</a>
    </nav>

    <div class="container">
        <h2>📋 All Appointments</h2>
        
        <div class="filter-section">
            <form method="GET" action="ViewAppointments.php">
                <label for="clinic-filter">Filter by Clinic:</label>
                <select name="clinic" id="clinic-filter">
                    <option value="">-- All Clinics --</option>
                    <?php foreach ($clinic_names as $id => $name): ?>
                        <option value="<?= $id ?>" <?= ($selected_clinic == $id) ? 'selected' : ''; ?>>
                            <?= $name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit">Filter</button>
            </form>
        </div>
        <?php if ($result->num_rows > 0): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Clinic</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Phone</th>
                    <th>Appointment Date & Time</th>
                    <th>Problem</th>
                    <th>Actions</th>
                </tr>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id']; ?></td>
                    <td>
                        <?= $clinic_names[$row['clinic_id']] ?? 'N/A' ?>
                    </td>
                    <td><?= htmlspecialchars($row['username']); ?></td>
                    <td><?= htmlspecialchars($row['lastname']); ?></td>
                    <td><?= htmlspecialchars($row['tele']); ?></td>
                    <td>
                        <?= date("Y-m-d H:i", strtotime($row['appointment_datetime'])); ?>
                    </td>
                    <td><?= nl2br(htmlspecialchars($row['problem'])); ?></td>
                    <td>
                        <a class="btn btn-edit" href="editAppointment.php?id=<?= $row['id']; ?>">Edit</a>
                        <a class="btn btn-delete" href="ViewAppointments.php?delete=<?= $row['id']; ?>" 
                           onclick="return confirm('Are you sure you want to delete this appointment?');">
                           Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p style="color:#e74c3c; text-align:center; font-weight:bold;">❌ No appointments found <?= $selected_clinic ? ' for this clinic' : ''; ?>.</p>
        <?php endif; ?>
    </div>
</body>
</html>