<?php require_once __DIR__ . "/../lib/security/SecurityHelper.php"; ?>
<header>
    <div class="container">
        <h1>Sistema de Agendamento Médico</h1>
        <nav>
            <?php if (isAuthenticated()): ?>
                <a href="/index.php" class="<?= $currentPage === "index"
                  ? "active"
                  : "" ?>">Início</a>
                <a href="/views/appointments.php" class="<?= $currentPage ===
                "appointments"
                  ? "active"
                  : "" ?>">Consultas</a>
                <a href="/views/doctors.php" class="<?= $currentPage ===
                "doctors"
                  ? "active"
                  : "" ?>">Médicos</a>
                <a href="/views/patients.php" class="<?= $currentPage ===
                "patients"
                  ? "active"
                  : "" ?>">Pacientes</a>
                <a href="/views/logout.php">Sair</a>
            <?php else: ?>
                <a href="/views/login.php" class="<?= $currentPage === "login"
                  ? "active"
                  : "" ?>">Entrar</a>
                <a href="/views/signup.php" class="<?= $currentPage === "signup"
                  ? "active"
                  : "" ?>">Cadastrar</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
