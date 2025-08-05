<?php
require_once "../lib/backend.php";
requireAuth();

$error = "";
$success = "";
$appointment = null;

$currentPage = "appointments";
include "header.php";

if (isset($_GET["id"])) {
  $id = (int) $_GET["id"];
  $appointment = $appointmentRepository->findById($id);
  if (!$appointment) {
    $error = "Appointment not found.";
  }
} else {
  $error = "No appointment ID provided.";
}

if ($_POST && $appointment) {
  $doctorId = (int) ($_POST["doctor_id"] ?? 0);
  $patientId = (int) ($_POST["patient_id"] ?? 0);
  $appointmentDate = $_POST["appointment_date"] ?? "";
  $description = sanitizeInput($_POST["description"] ?? "");

  // Format date for database
  $appointmentDate = str_replace("T", " ", $appointmentDate) . ":00";

  $result = $appointmentRepository->update(
    $appointment["id"],
    $doctorId,
    $patientId,
    $appointmentDate,
    $description,
  );
  if ($result) {
    $success = "Appointment updated successfully!";
    // Refresh appointment data after update
    $appointment = $appointmentRepository->findById($appointment["id"]);
  } else {
    $error = "Failed to update appointment.";
  }
}

$doctors = $doctorRepository->findAll();
$patients = $patientRepository->findAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Appointment - Medical Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h2>Edit Appointment</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars(
              $success,
            ) ?></div>
        <?php endif; ?>

        <?php if ($appointment): ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="doctor_id">Doctor:</label>
                    <select id="doctor_id" name="doctor_id" required>
                        <option value="">Select Doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?= $doctor["id"] ?>" <?= $doctor[
  "id"
] == $appointment["doctor_id"]
  ? "selected"
  : "" ?>>
                                <?= htmlspecialchars(
                                  $doctor["name"],
                                ) ?> - <?= htmlspecialchars(
   $doctor["expertise"],
 ) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="patient_id">Patient:</label>
                    <select id="patient_id" name="patient_id" required>
                        <option value="">Select Patient</option>
                        <?php foreach ($patients as $patient): ?>
                            <option value="<?= $patient["id"] ?>" <?= $patient[
  "id"
] == $appointment["patient_id"]
  ? "selected"
  : "" ?>>
                                <?= htmlspecialchars($patient["name"]) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Appointment Date & Time:</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required
                           value="<?= htmlspecialchars(
                             date(
                               "Y-m-d\TH:i",
                               strtotime($appointment["appointment_date"]),
                             ),
                           ) ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description:</label>
                    <textarea id="description" name="description" rows="3" required
                              placeholder="Describe the reason for the appointment..."><?= htmlspecialchars(
                                $appointment["description"],
                              ) ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">Update Appointment</button>
                <a href="appointments.php" class="btn btn-secondary">Back to Appointments</a>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
