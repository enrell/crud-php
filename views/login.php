<?php
require_once "../lib/backend.php";

$error = "";
$currentPage = "login";

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
?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Sistema de Agendamento Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include "header.php"; ?>
    <main class="container">
        <div class="form-container">
            <h2>Entrar</h2>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars(
                  $error,
                ) ?></div>
            <?php endif; ?>

            <form method="POST">


                <div class="form-group">
                    <label for="email">E-mail:</label>
                    <input type="email" id="email" name="email" required
                           value="<?= htmlspecialchars(
                             $_POST["email"] ?? "",
                           ) ?>">
                </div>

                <div class="form-group">
                    <label for="password">Senha:</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="btn btn-primary">Entrar</button>
                <a href="signup.php" class="btn btn-secondary">Criar Conta</a>
            </form>
        </div>
    </main>
</body>
</html>
