<?php
require_once "lib/config/Database.php";
require_once "lib/security/SecurityHelper.php";

if (!isAuthenticated()) {
  header("Location: views/login.php");
  exit();
}

$currentPage = "index";
include "views/header.php";
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sistema de Agendamento Médico - Painel</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <main class="container">
    <h2>Bem-vindo de volta!</h2>
    <div class="dashboard-cards">
      <div class="card">
        <h3>Consultas</h3>
        <p>Gerenciar agendamentos médicos</p>
        <a href="views/appointments.php" class="btn">Ver Consultas</a>
      </div>
      <div class="card">
        <h3>Médicos</h3>
        <p>Gerenciar informações dos médicos</p>
        <a href="views/doctors.php" class="btn">Ver Médicos</a>
      </div>
      <div class="card">
        <h3>Pacientes</h3>
        <p>Gerenciar informações dos pacientes</p>
        <a href="views/patients.php" class="btn">Ver Pacientes</a>
      </div>
    </div>
  </main>
</body>
</html>
