<?php
require_once '../lib/backend.php';
requireAuth();

$error = '';
$success = '';
$patient = null;

$currentPage = 'patients';
include 'header.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $patient = $patientRepository->findById($id);
    if (!$patient) {
        $error = 'Patient not found.';
    }
} else {
    $error = 'No patient ID provided.';
}

if ($_POST && $patient) {
    $name = sanitizeInput($_POST['name'] ?? '');
    $birthdate = $_POST['birthdate'] ?? '';
    $bloodType = sanitizeInput($_POST['blood_type'] ?? '');

    $result = $patientRepository->update($patient['id'], $name, $birthdate, $bloodType);
    if ($result) {
        $success = 'Patient updated successfully!';
        // Refresh patient data after update
        $patient = $patientRepository->findById($patient['id']);
    } else {
        $error = 'Failed to update patient.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Patient - Medical Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <main class="container">
        <div class="page-header">
            <h2>Edit Patient</h2>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($patient): ?>
        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="name">Patient Name:</label>
                    <input type="text" id="name" name="name" required value="<?= htmlspecialchars($patient['name']) ?>">
                </div>

                <div class="form-group">
                    <label for="birthdate">Birth Date:</label>
                    <input type="date" id="birthdate" name="birthdate" required value="<?= htmlspecialchars($patient['birthdate']) ?>">
                </div>

                <div class="form-group">
                    <label for="blood_type">Blood Type:</label>
                    <select id="blood_type" name="blood_type" required>
                        <option value="">Select Blood Type</option>
                        <option value="A+" <?= ($patient['blood_type'] === 'A+') ? 'selected' : '' ?>>A+</option>
                        <option value="A-" <?= ($patient['blood_type'] === 'A-') ? 'selected' : '' ?>>A-</option>
                        <option value="B+" <?= ($patient['blood_type'] === 'B+') ? 'selected' : '' ?>>B+</option>
                        <option value="B-" <?= ($patient['blood_type'] === 'B-') ? 'selected' : '' ?>>B-</option>
                        <option value="AB+" <?= ($patient['blood_type'] === 'AB+') ? 'selected' : '' ?>>AB+</option>
                        <option value="AB-" <?= ($patient['blood_type'] === 'AB-') ? 'selected' : '' ?>>AB-</option>
                        <option value="O+" <?= ($patient['blood_type'] === 'O+') ? 'selected' : '' ?>>O+</option>
                        <option value="O-" <?= ($patient['blood_type'] === 'O-') ? 'selected' : '' ?>>O-</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-success">Update Patient</button>
                <a href="patients.php" class="btn btn-secondary">Back to Patients</a>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>