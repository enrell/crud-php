<?php
require_once "../lib/backend.php";
requireAuth();

$error = "";
$success = "";
$patient = null;

$currentPage = "patients";
include "header.php";

if (isset($_GET["id"])) {
  $id = (int) $_GET["id"];
  $patient = $patientRepository->findById($id);
  if (!$patient) {
    $error = "Patient not found.";
  }
} else {
  $error = "No patient ID provided.";
}

if ($_POST && $patient) {
  $name = sanitizeInput($_POST["name"] ?? "");
  $birthdate = $_POST["birthdate"] ?? "";
  $bloodType = sanitizeInput($_POST["blood_type"] ?? "");

  try {
    $result = $patientRepository->update(
      $patient["id"],
      $name,
      $birthdate,
      $bloodType,
    );
    if ($result) {
      $success = "Patient updated successfully!";
      // Refresh patient data after update
      $patient = $patientRepository->findById($patient["id"]);
    } else {
      $error = "Failed to update patient.";
    }
  } catch (Exception $e) {
    $error = "Error updating patient: " . $e->getMessage();
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="pt-BR">
    <title>Editar Paciente - Sistema de Agendamento Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h2>Editar Paciente</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars(
              $success,
            ) ?></div>
        <?php endif; ?>

        <?php if ($patient): ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Nome do Paciente:</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars(
                      $patient["name"],
                    ) ?>">
                </div>

                <div class="form-group">
                    <label for="birthdate">Data de Nascimento:</label>
                    <input type="date" id="birthdate" name="birthdate" required max="<?php
                    date_default_timezone_set("America/Sao_Paulo");
                    echo date("Y-m-d");
                    ?>" value="<?= htmlspecialchars($patient["birthdate"]) ?>">
                </div>

                <div class="form-group">
                    <label for="blood_type">Tipo Sanguíneo:</label>
                    <select id="blood_type" name="blood_type" required>
                        <option value="">Selecione o Tipo Sanguíneo</option>
                        <option value="A+" <?= $patient["blood_type"] === "A+"
                          ? "selected"
                          : "" ?>>A+</option>
                        <option value="A-" <?= $patient["blood_type"] === "A-"
                          ? "selected"
                          : "" ?>>A-</option>
                        <option value="B+" <?= $patient["blood_type"] === "B+"
                          ? "selected"
                          : "" ?>>B+</option>
                        <option value="B-" <?= $patient["blood_type"] === "B-"
                          ? "selected"
                          : "" ?>>B-</option>
                        <option value="AB+" <?= $patient["blood_type"] === "AB+"
                          ? "selected"
                          : "" ?>>AB+</option>
                        <option value="AB-" <?= $patient["blood_type"] === "AB-"
                          ? "selected"
                          : "" ?>>AB-</option>
                        <option value="O+" <?= $patient["blood_type"] === "O+"
                          ? "selected"
                          : "" ?>>O+</option>
                        <option value="O-" <?= $patient["blood_type"] === "O-"
                          ? "selected"
                          : "" ?>>O-</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Atualizar Paciente</button>
                <a href="patients.php" class="btn btn-secondary">Voltar para Pacientes</a>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
