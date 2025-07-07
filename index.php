<?php
require_once 'lib/config/Database.php';
require_once 'lib/security/SecurityHelper.php';

if (!isAuthenticated()) {
    header('Location: views/login.php');
    exit();
}

$currentPage = 'index';
include 'views/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Medical Appointments - Dashboard</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="container">
    <h2>Welcome back!</h2>
    <div class="dashboard-cards">
      <div class="card">
        <h3>Appointments</h3>
        <p>Manage medical appointments</p>
        <a href="views/appointments.php" class="btn">View Appointments</a>
      </div>
      <div class="card">
        <h3>Doctors</h3>
        <p>Manage doctors information</p>
        <a href="views/doctors.php" class="btn">View Doctors</a>
      </div>
      <div class="card">
        <h3>Patients</h3>
        <p>Manage patients information</p>
        <a href="views/patients.php" class="btn">View Patients</a>
      </div>
    </div>
  </main>
</body>
</html>

