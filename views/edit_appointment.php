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

  try {
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
  } catch (Exception $e) {
    $error = "Error updating appointment: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="pt-BR">
    <title>Editar Consulta - Sistema de Agendamento Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h2>Editar Consulta</h2>
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
        <?php
        $doctors = $doctorRepository->findAll();
        $patients = $patientRepository->findAll();
        ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="doctor_id">Médico:</label>
                    <select id="doctor_id" name="doctor_id" required>
                        <option value="">Selecione o Médico</option>
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
                    <label for="patient_id">Paciente:</label>
                    <select id="patient_id" name="patient_id" required>
                        <option value="">Selecione o Paciente</option>
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
                    <label for="appointment_date">Data e Hora da Consulta:</label>
                    <input type="datetime-local" id="appointment_date" name="appointment_date" required
                           value="<?= htmlspecialchars(
                             date(
                               "Y-m-d\TH:i",
                               strtotime($appointment["appointment_date"]),
                             ),
                           ) ?>">
                </div>

                <div class="form-group">
                    <label for="description">Descrição:</label>
                    <textarea id="description" name="description" rows="3" required
                              placeholder="Descreva o motivo da consulta..."><?= htmlspecialchars(
                                $appointment["description"],
                              ) ?></textarea>
                </div>

                <button type="submit" class="btn btn-success">Atualizar Consulta</button>
                <a href="appointments.php" class="btn btn-secondary">Voltar para Consultas</a>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
