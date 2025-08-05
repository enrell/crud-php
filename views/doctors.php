<?php
require_once "../lib/backend.php";
requireAuth();

$error = "";

$currentPage = "doctors";
include "header.php";

// Handle form submissions
if ($_POST) {
  $action = $_POST["action"] ?? "";

  if ($action === "create") {
    $name = $_POST["name"] ?? "";
    $expertise = $_POST["expertise"] ?? "";

    try {
      $result = $doctorRepository->create($name, $expertise);
      if ($result) {
        $_SESSION["success_message"] = "Doctor registered successfully!";
      } else {
        $_SESSION["error_message"] = "Failed to register doctor.";
      }
    } catch (InvalidArgumentException $e) {
      $_SESSION["error_message"] = $e->getMessage();
    }
  } elseif ($action === "update") {
    $id = (int) ($_POST["id"] ?? 0);
    $name = $_POST["name"] ?? "";
    $expertise = $_POST["expertise"] ?? "";

    try {
      $result = $doctorRepository->update($id, $name, $expertise);
      if ($result) {
        $_SESSION["success_message"] = "Doctor updated successfully!";
      } else {
        $error = "Failed to update doctor.";
      }
    } catch (InvalidArgumentException $e) {
      $_SESSION["error_message"] = $e->getMessage();
    }
  } elseif ($action === "delete") {
    $id = (int) ($_POST["id"] ?? 0);
    try {
      $result = $doctorRepository->delete($id);
      if ($result) {
        $_SESSION["success_message"] = "Doctor deleted successfully!";
      } else {
        $_SESSION["error_message"] =
          "Failed to delete doctor. They may have active appointments.";
      }
    } catch (InvalidArgumentException $e) {
      $_SESSION["error_message"] = $e->getMessage();
    }
  }
}

$doctors = $doctorRepository->findAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Médicos - Sistema de Agendamento Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>


    <main class="container">
        <div class="page-header-with-action">
            <h2>Gerenciamento de Médicos</h2>
            <button class="btn-add" onclick="openModal()">+ Adicionar Médico</button>
        </div>

        <?php if (isset($_SESSION["error_message"])): ?>
            <div class="alert alert-error"><?= htmlspecialchars(
              $_SESSION["error_message"],
            ) ?></div>
            <?php unset($_SESSION["error_message"]);endif; ?>

        <?php if (isset($_SESSION["success_message"])): ?>
            <div class="alert alert-success"><?= htmlspecialchars(
              $_SESSION["success_message"],
            ) ?></div>
            <?php unset($_SESSION["success_message"]);endif; ?>



        <!-- Doctors List -->
        <div class="table-container">
            <div class="table-header">
                <h3>Todos os Médicos</h3>
                <div class="table-stats"><?= count(
                  $doctors,
                ) ?> médicos no total</div>
            </div>
            <table class="doctors-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Especialidade</th>
                        <th style="padding-right: 80px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($doctors)): ?>
                        <tr>
                            <td colspan="4" class="empty-state">
                                <div>
                                    <h3>Nenhum médico cadastrado</h3>
                                    <p>Clique no botão "Adicionar Médico" para cadastrar seu primeiro médico.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($doctors as $doctor): ?>
                            <tr>
                                <td><?= htmlspecialchars($doctor["id"]) ?></td>
                                <td><?= htmlspecialchars(
                                  $doctor["name"],
                                ) ?></td>
                                <td><?= htmlspecialchars(
                                  $doctor["expertise"],
                                ) ?></td>
                                <td class="actions">
                                    <button class="btn btn-warning" onclick="openEditModal(<?= $doctor[
                                      "id"
                                    ] ?>, '<?= htmlspecialchars(
  $doctor["name"],
) ?>', '<?= htmlspecialchars($doctor["expertise"]) ?>')">Editar</button>
                                    <form method="POST" style="display: inline;">

                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $doctor[
                                          "id"
                                        ] ?>">
                                        <button type="submit" class="btn btn-danger">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal for adding/editing doctor -->
    <div id="doctorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Adicionar Novo Médico</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="modalAction" value="create">
                <input type="hidden" name="id" id="modalId" value="">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_name">Nome do Médico:</label>
                        <input type="text" id="modal_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="modal_expertise">Especialidade:</label>
                        <input type="text" id="modal_expertise" name="expertise" required
                               placeholder="ex: Cardiologia, Pediatria, Clínica Geral">
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn btn-success" id="modalSubmitBtn">Adicionar Médico</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Adicionar Novo Médico';
            document.getElementById('modalAction').value = 'create';
            document.getElementById('modalId').value = '';
            document.getElementById('modal_name').value = '';
            document.getElementById('modal_expertise').value = '';
            document.getElementById('modalSubmitBtn').textContent = 'Adicionar Médico';
            document.getElementById('doctorModal').style.display = 'block';
        }

        function openEditModal(id, name, expertise) {
            document.getElementById('modalTitle').textContent = 'Editar Médico';
            document.getElementById('modalAction').value = 'update';
            document.getElementById('modalId').value = id;
            document.getElementById('modal_name').value = name;
            document.getElementById('modal_expertise').value = expertise;
            document.getElementById('modalSubmitBtn').textContent = 'Atualizar Médico';
            document.getElementById('doctorModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('doctorModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('doctorModal');
            if (event.target == modal) {
                closeModal();
            }
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });
    </script>
</body>
</html>
