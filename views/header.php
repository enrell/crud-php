<header>
    <div class="container">
        <h1>Medical Appointments System</h1>
        <nav>
            <?php if (isAuthenticated()): ?>
                <a href="/index.php" class="<?= ($currentPage === 'index') ? 'active' : '' ?>">Home</a>
                <a href="/views/appointments.php" class="<?= ($currentPage === 'appointments') ? 'active' : '' ?>">Appointments</a>
                <a href="/views/doctors.php" class="<?= ($currentPage === 'doctors') ? 'active' : '' ?>">Doctors</a>
                <a href="/views/patients.php" class="<?= ($currentPage === 'patients') ? 'active' : '' ?>">Patients</a>
                <a href="/views/logout.php">Logout</a>
            <?php else: ?>
                <a href="/views/login.php" class="<?= ($currentPage === 'login') ? 'active' : '' ?>">Login</a>
                <a href="/views/signup.php" class="<?= ($currentPage === 'signup') ? 'active' : '' ?>">Sign Up</a>
            <?php endif; ?>
        </nav>
    </div>
</header>