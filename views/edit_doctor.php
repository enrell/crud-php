<?php
require_once "../lib/backend.php";
requireAuth();

$error = "";
$success = "";
$doctor = null;

$currentPage = "doctors";
include "header.php";

if (isset($_GET["id"])) {
  $id = (int) $_GET["id"];
  $doctor = $doctorRepository->findById($id);
  if (!$doctor) {
    $error = "Doctor not found.";
  }
} else {
  $error = "No doctor ID provided.";
}

if ($_POST && $doctor) {
  $name = sanitizeInput($_POST["name"] ?? "");
  $expertise = sanitizeInput($_POST["expertise"] ?? "");

  $result = $doctorRepository->update($doctor["id"], $name, $expertise);
  if ($result) {
    $success = "Doctor updated successfully!";
    // Refresh doctor data after update
    $doctor = $doctorRepository->findById($doctor["id"]);
  } else {
    $error = "Failed to update doctor.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Doctor - Medical Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h2>Edit Doctor</h2>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars(
              $success,
            ) ?></div>
        <?php endif; ?>

        <?php if ($doctor): ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Doctor Name:</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars(
                      $doctor["name"],
                    ) ?>">
                </div>

                <div class="form-group">
                    <label for="expertise">Expertise:</label>
                    <input type="text" id="expertise" name="expertise" required value="<?= htmlspecialchars(
                      $doctor["expertise"],
                    ) ?>">
                </div>

                <button type="submit" class="btn btn-success">Update Doctor</button>
                <a href="doctors.php" class="btn btn-secondary">Back to Doctors</a>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
