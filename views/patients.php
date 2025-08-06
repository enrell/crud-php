<?php
require_once "../lib/backend.php";
requireAuth();

$currentPage = "patients";
include "header.php";

// Handle form submissions
if ($_POST) {
  $action = $_POST["action"] ?? "";

  if ($action === "create") {
    $name = sanitizeInput($_POST["name"] ?? "");
    $birthdate = $_POST["birthdate"] ?? "";
    $bloodType = sanitizeInput($_POST["blood_type"] ?? "");

    if (empty($name) || empty($birthdate) || empty($bloodType)) {
      $_SESSION["error_message"] = "Todos os campos são obrigatórios.";
    } else {
      try {
        $result = $patientRepository->create($name, $birthdate, $bloodType);
        if ($result) {
          $_SESSION["success_message"] = "Patient registered successfully!";
        } else {
          $_SESSION["error_message"] = "Failed to register patient.";
        }
      } catch (InvalidArgumentException $e) {
        $_SESSION["error_message"] = $e->getMessage();
      }
    }
  } elseif ($action === "update") {
    $id = (int) ($_POST["id"] ?? 0);
    $name = sanitizeInput($_POST["name"] ?? "");
    $birthdate = $_POST["birthdate"] ?? "";
    $bloodType = sanitizeInput($_POST["blood_type"] ?? "");

    if ($id <= 0 || empty($name) || empty($birthdate) || empty($bloodType)) {
      $_SESSION["error_message"] = "Dados inválidos fornecidos.";
    } else {
      try {
        $result = $patientRepository->update(
          $id,
          $name,
          $birthdate,
          $bloodType,
        );
        if ($result) {
          $_SESSION["success_message"] = "Patient updated successfully!";
        } else {
          $_SESSION["error_message"] = "Failed to update patient.";
        }
      } catch (InvalidArgumentException $e) {
        $_SESSION["error_message"] = $e->getMessage();
      }
    }
  } elseif ($action === "delete") {
    $id = (int) ($_POST["id"] ?? 0);
    if ($id <= 0) {
      $_SESSION["error_message"] = "ID inválido.";
    } else {
      try {
        $result = $patientRepository->delete($id);
        if ($result) {
          $_SESSION["success_message"] = "Patient deleted successfully!";
        } else {
          $_SESSION["error_message"] =
            "Failed to delete patient. They may have scheduled appointments.";
        }
      } catch (Exception $e) {
        $_SESSION["error_message"] =
          "Erro ao excluir paciente: " . $e->getMessage();
      }
    }
  }
}

$patients = $patientRepository->findAll();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pacientes - Sistema de Agendamento Médico</title>
  <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>


  <main class="container">
    <div class="page-header-with-action">
      <h2>Gerenciamento de Pacientes</h2>
      <button class="btn-add" onclick="openModal()">+ Adicionar Paciente</button>
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

    <!-- Patients List -->
    <div class="table-container">
      <div class="table-header">
        <h3>Todos os Pacientes</h3>
        <div class="table-stats"><?= count(
          $patients,
        ) ?> pacientes no total</div>
      </div>
      <table class="patients-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Data de Nascimento</th>
            <th>Tipo Sanguíneo</th>
            <th style="padding-right: 120px;">Ações</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($patients)): ?>
            <tr>
              <td colspan="5" class="empty-state">
                <div>
                  <h3>Nenhum paciente cadastrado</h3>
                  <p>Clique no botão "Adicionar Paciente" para cadastrar seu primeiro paciente.</p>
                </div>
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($patients as $patient): ?>
              <tr>
                <td><?= htmlspecialchars($patient["id"]) ?></td>
                <td><?= htmlspecialchars($patient["name"]) ?></td>
                <td style="padding-right: 145px;"><?= htmlspecialchars(
                  date("d/m/Y", strtotime($patient["birthdate"])),
                ) ?></td>
                <td style="padding-right: 80px;"><?= htmlspecialchars(
                  $patient["blood_type"],
                ) ?></td>
                <td class="actions">
                  <button class="btn btn-warning" onclick="openEditModal(<?= $patient[
                    "id"
                  ] ?>, '<?= htmlspecialchars(
  $patient["name"],
  ENT_QUOTES,
) ?>', '<?= $patient["birthdate"] ?>', '<?= htmlspecialchars(
  $patient["blood_type"],
  ENT_QUOTES,
) ?>')">Editar</button>
                  <form method="POST" style="display: inline;">

                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= $patient[
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

  <!-- Modal for adding/editing patient -->
  <div id="patientModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3 id="modalTitle">Adicionar Novo Paciente</h3>
        <span class="close" onclick="closeModal()">&times;</span>
      </div>
      <form method="POST">
        <input type="hidden" name="action" id="modalAction" value="create">
        <input type="hidden" name="id" id="modalId" value="">

        <div class="modal-body">
          <div class="form-group">
            <label for="modal_name">Nome:</label>
            <input type="text" id="modal_name" name="name" required>
          </div>
          <div class="form-group">
            <label for="modal_birthdate">Data de Nascimento:</label>
            <input type="date" id="modal_birthdate" name="birthdate" required max="<?php
            date_default_timezone_set("America/Sao_Paulo");
            echo date("Y-m-d");
            ?>">
          </div>
          <div class="form-group">
            <label for="modal_blood_type">Tipo Sanguíneo:</label>
            <select id="modal_blood_type" name="blood_type" required>
              <option value="">Selecione o Tipo Sanguíneo</option>
              <option value="A+">A+</option>
              <option value="A-">A-</option>
              <option value="B+">B+</option>
              <option value="B-">B-</option>
              <option value="AB+">AB+</option>
              <option value="AB-">AB-</option>
              <option value="O+">O+</option>
              <option value="O-">O-</option>
            </select>
          </div>
          <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
            <button type="submit" class="btn btn-success" id="modalSubmitBtn">Adicionar Paciente</button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <script>
    function openModal() {
      document.getElementById('modalTitle').textContent = 'Adicionar Novo Paciente';
      document.getElementById('modalAction').value = 'create';
      document.getElementById('modalId').value = '';
      document.getElementById('modal_name').value = '';
      document.getElementById('modal_birthdate').value = '';
      document.getElementById('modal_blood_type').value = '';
      document.getElementById('modalSubmitBtn').textContent = 'Adicionar Paciente';
      document.getElementById('patientModal').style.display = 'block';
    }

    function openEditModal(id, name, birthdate, bloodType) {
      document.getElementById('modalTitle').textContent = 'Editar Paciente';
      document.getElementById('modalAction').value = 'update';
      document.getElementById('modalId').value = id;
      document.getElementById('modal_name').value = name;
      document.getElementById('modal_birthdate').value = birthdate;
      document.getElementById('modal_blood_type').value = bloodType;
      document.getElementById('modalSubmitBtn').textContent = 'Atualizar Paciente';
      document.getElementById('patientModal').style.display = 'block';
    }

    function closeModal() {
      document.getElementById('patientModal').style.display = 'none';
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
      var modal = document.getElementById('patientModal');
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
