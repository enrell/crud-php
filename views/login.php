<?php
header("Content-Type: text/html; charset=utf-8");
require_once "../lib/backend.php";

$error = "";

$currentPage = "login";
include "header.php";

if ($_POST) {
  $email = $_POST["email"] ?? "";
  $password = $_POST["password"] ?? "";

  try {
    require_once "../lib/models/Email.php";
    $emailObj = new Email($email);
    $validatedEmail = $emailObj->getValue();

    require_once "../lib/models/Password.php";
    $passwordObj = new Password($password);

    $user = $userRepository->authenticate($validatedEmail, $password);
    if ($user) {
      $_SESSION["user_id"] = $user["id"];
      $_SESSION["username"] = $user["username"];
      header("Location: ../index.php");
      exit();
    } else {
      $error = "Invalid email or password.";
    }
  } catch (Exception $e) {
    $error = $e->getMessage();
  }
}
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Medical Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>


    <main class="container">
        <div class="form-container">
            <h2>Login</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars(
                  $error,
                ) ?></div>
            <?php endif; ?>

            <form method="POST">


                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars(
                             $_POST["email"] ?? "",
                           ) ?>">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Login</button>
                <a href="signup.php" class="btn btn-secondary">Create Account</a>
            </form>
        </div>
    </main>
</body>
</html>
