<?php
header('Content-Type: text/html; charset=utf-8');
require_once '../lib/backend.php';

$error = '';
$success = '';

$currentPage = 'signup';
include 'header.php';

if ($_POST) {
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        try {
            // Create Password object (validates automatically)
            require_once '../lib/models/Password.php';
            $passwordObj = new Password($password);

            // Create Name object (validates automatically)
            require_once '../lib/models/Name.php';
            $nameObj = new Name($username);

            // Create Email object (validates automatically)
            require_once '../lib/models/Email.php';
            $emailObj = new Email($email);
            
            // Create user
            $hashedPassword = $passwordObj->getHash();
            $result = $userRepository->create($nameObj->getValue(), $emailObj->getValue(), $hashedPassword);
            
            if ($result) {
                $success = 'Account created successfully! You can now login.';
            } else {
                $error = 'Failed to create account. User may already exist.';
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Medical Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    

    <main class="container">
        <div class="form-container">
            <h2>Create Account</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                
                
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required 
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <small>Password must be at least 8 characters.</small>
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>
                <a href="login.php" class="btn btn-secondary">Already have an account?</a>
            </form>
        </div>
    </main>
</body>
</html>
