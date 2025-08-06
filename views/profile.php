<?php
require_once __DIR__ . "/../lib/security/SecurityHelper.php";
require_once __DIR__ . "/../lib/backend.php";

requireAuth();

$currentPage = "profile";
$userId = $_SESSION["user_id"] ?? null;
$user = $userRepository->findByIdWithPassword($userId);
$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $action = $_POST["action"] ?? "";

  if ($action === "update_profile") {
    $username = sanitizeInput($_POST["username"] ?? "");
    $email = sanitizeInput($_POST["email"] ?? "");

    if (empty($username) || empty($email)) {
      $error = "Nome de usuário e email são obrigatórios.";
    } else {
      try {
        $userRepository->update($userId, $username, $email);
        $_SESSION["username"] = $username;
        $user = $userRepository->findByIdWithPassword($userId);
        $success = "Perfil atualizado com sucesso!";
      } catch (Exception $e) {
        $error = "Erro ao atualizar perfil: " . $e->getMessage();
      }
    }
  }

  if ($action === "update_password") {
    $currentPassword = $_POST["current_password"] ?? "";
    $newPassword = $_POST["new_password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
      empty($currentPassword) ||
      empty($newPassword) ||
      empty($confirmPassword)
    ) {
      $error = "Todos os campos de senha são obrigatórios.";
    } elseif ($newPassword !== $confirmPassword) {
      $error = "Nova senha e confirmação não coincidem.";
    } elseif (
      !isset($user["password"]) ||
      !password_verify($currentPassword, $user["password"])
    ) {
      $error = "Senha atual incorreta.";
    } else {
      try {
        $userRepository->updatePassword($userId, $newPassword);
        $success = "Senha atualizada com sucesso!";
      } catch (Exception $e) {
        $error = "Erro ao atualizar senha: " . $e->getMessage();
      }
    }
  }

  if ($action === "delete_account") {
    try {
      if ($userRepository->delete($userId)) {
        session_destroy();
        header("Location: /views/login.php?message=account_deleted");
        exit();
      } else {
        $error = "Erro ao excluir conta.";
      }
    } catch (Exception $e) {
      $error = "Erro ao excluir conta: " . $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Sistema Médico</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <?php include "header.php"; ?>

    <main>
        <div class="settings-container">
            <main class="settings-content">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <section id="profile" class="settings-section">
                    <div class="section-header">
                        <h3>Informações Pessoais</h3>
                        <p>Atualize seu nome de usuário e email</p>
                    </div>
                    <div class="section-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="form-group">
                                <label for="username">Nome de Usuário</label>
                                <input type="text" id="username" name="username"
                                       value="<?= htmlspecialchars(
                                         $user["username"],
                                       ) ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email"
                                       value="<?= htmlspecialchars(
                                         $user["email"],
                                       ) ?>" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section id="security" class="settings-section">
                    <div class="section-header">
                        <h3>Segurança</h3>
                        <p>Altere sua senha para manter sua conta segura</p>
                    </div>
                    <div class="section-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="update_password">
                            <div class="form-group">
                                <label for="current_password">Senha Atual</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Nova Senha</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmar Nova Senha</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Alterar Senha</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section id="danger" class="settings-section">
                    <div class="section-header">
                        <h3>Zona de Perigo</h3>
                        <p>Ações permanentes que afetam sua conta</p>
                    </div>
                    <div class="section-body danger-zone-body">
                        <div class="danger-item">
                            <div>
                                <strong>Excluir esta conta</strong>
                                <p>Uma vez que você exclui sua conta, não há como voltar atrás. Por favor, tenha certeza.</p>
                            </div>
                            <button type="button" class="btn btn-danger" onclick="confirmDeleteAccount()">
                                Excluir Conta
                            </button>
                        </div>
                        <form id="delete-account-form" method="POST" style="display: none;">
                            <input type="hidden" name="action" value="delete_account">
                        </form>
                    </div>
                </section>
            </main>
        </div>
    </main>

    <script>
    function confirmDeleteAccount() {
        if (confirm('Tem certeza que deseja excluir sua conta? Esta ação não pode ser desfeita.')) {
            if (confirm('ÚLTIMA CONFIRMAÇÃO: Todos os seus dados serão perdidos permanentemente. Continuar?')) {
                document.getElementById('delete-account-form').submit();
            }
        }
    }


    </script>
</body>
</html>
