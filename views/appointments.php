<?php
require_once "../lib/backend.php";
requireAuth();

$currentPage = "appointments";
include "header.php";

// Handle form submissions
if ($_POST) {
  $action = $_POST["action"] ?? "";

  if ($action === "create") {
    $doctorId = (int) ($_POST["doctor_id"] ?? 0);
    $patientId = (int) ($_POST["patient_id"] ?? 0);
    $appointmentDate = $_POST["appointment_date"] ?? "";
    $description = sanitizeInput($_POST["description"] ?? "");

    if (
      $doctorId <= 0 ||
      $patientId <= 0 ||
      empty($appointmentDate) ||
      empty($description)
    ) {
      $_SESSION["error_message"] = "Todos os campos são obrigatórios.";
    } else {
      try {
        $result = $appointmentRepository->create(
          $doctorId,
          $patientId,
          $appointmentDate,
          $description,
        );
        if ($result) {
          $_SESSION["success_message"] = "Consulta agendada com sucesso!";
        } else {
          $_SESSION["error_message"] = "Falha ao agendar consulta.";
        }
      } catch (InvalidArgumentException $e) {
        $_SESSION["error_message"] = $e->getMessage();
      }
    }
  } elseif ($action === "update") {
    $id = (int) ($_POST["id"] ?? 0);
    $doctorId = (int) ($_POST["doctor_id"] ?? 0);
    $patientId = (int) ($_POST["patient_id"] ?? 0);
    $appointmentDate = $_POST["appointment_date"] ?? "";
    $description = sanitizeInput($_POST["description"] ?? "");

    if (
      $id <= 0 ||
      $doctorId <= 0 ||
      $patientId <= 0 ||
      empty($appointmentDate) ||
      empty($description)
    ) {
      $_SESSION["error_message"] = "Todos os campos são obrigatórios.";
    } else {
      try {
        $result = $appointmentRepository->update(
          $id,
          $doctorId,
          $patientId,
          $appointmentDate,
          $description,
        );
        if ($result) {
          $_SESSION["success_message"] = "Consulta atualizada com sucesso!";
        } else {
          $error = "Falha ao atualizar consulta.";
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
        $result = $appointmentRepository->delete($id);
        if ($result) {
          $_SESSION["success_message"] = "Consulta excluída com sucesso!";
        } else {
          $_SESSION["error_message"] =
            "Falha ao excluir consulta. A consulta pode não existir ou já ter sido excluída. Atualize a página e tente novamente.";
        }
      } catch (Exception $e) {
        $_SESSION["error_message"] =
          "Erro ao excluir consulta: " . $e->getMessage();
      }
    }
  }
}

$appointments = $appointmentRepository->findAll();
$doctors = $doctorRepository->findAll();
$patients = $patientRepository->findAll();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Language" content="pt-BR">
    <title>Consultas - Sistema de Agendamento Médico</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>


    <main class="container">
        <div class="page-header-with-action">
            <h2>Gerenciamento de Consultas</h2>
            <button class="btn-add" onclick="openModal()">+ Nova Consulta</button>
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

        <!-- Lista de Consultas -->
        <div class="table-container">
            <div class="table-header">
                <h3>Todas as Consultas</h3>
                <div class="table-stats"><?= count(
                  $appointments,
                ) ?> consultas no total</div>
            </div>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Médico</th>
                        <th>Paciente</th>
                        <th>Data</th>
                        <th>Descrição</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhuma consulta encontrada.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars(
                                  $appointment["id"],
                                ) ?></td>
                                <td><?= htmlspecialchars(
                                  $appointment["doctor_name"],
                                ) ?></td>
                                <td><?= htmlspecialchars(
                                  $appointment["patient_name"],
                                ) ?></td>
                                <td><?= htmlspecialchars(
                                  date(
                                    "d/m/Y",
                                    strtotime($appointment["appointment_date"]),
                                  ),
                                ) ?></td>
                                <td><?= htmlspecialchars(
                                  $appointment["description"],
                                ) ?></td>
                                <td class="actions">
                                    <button class="btn btn-warning" onclick="openEditModal(<?= $appointment[
                                      "id"
                                    ] ?>, <?= $appointment[
  "doctor_id"
] ?>, <?= $appointment["patient_id"] ?>, '<?= date(
  "Y-m-d",
  strtotime($appointment["appointment_date"]),
) ?>', '<?= htmlspecialchars(
  $appointment["description"],
  ENT_QUOTES,
) ?>')">Editar</button>
                                    <form method="POST" style="display: inline;">

                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $appointment[
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

    <!-- Modal for adding new appointment -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Agendar Nova Consulta</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="modalAction" value="create">
                <input type="hidden" name="id" id="modalId" value="">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_doctor_id">Médico:</label>
                        <select id="modal_doctor_id" name="doctor_id" required>
                            <option value="">Selecione o Médico</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor["id"] ?>">
                                    <?= htmlspecialchars(
                                      $doctor["name"],
                                    ) ?> - <?= htmlspecialchars(
   $doctor["expertise"],
 ) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_patient_id">Paciente:</label>
                        <select id="modal_patient_id" name="patient_id" required>
                            <option value="">Selecione o Paciente</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient["id"] ?>">
                                    <?= htmlspecialchars($patient["name"]) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_appointment_date">Data da Consulta:</label>
                        <input type="date" id="modal_appointment_date" name="appointment_date" required>
                    </div>

                    <div class="form-group">
                        <label for="modal_description">Descrição:</label>
                        <textarea id="modal_description" name="description" rows="3" required
                                  placeholder="Descreva o motivo da consulta..."></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Agendar Consulta</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Agendar Nova Consulta';
            document.getElementById('modalAction').value = 'create';
            document.getElementById('modalId').value = '';
            document.getElementById('modal_doctor_id').value = '';
            document.getElementById('modal_patient_id').value = '';
            document.getElementById('modal_appointment_date').value = '';
            document.getElementById('modal_description').value = '';
            document.getElementById('modalSubmitBtn').textContent = 'Agendar Consulta';
            document.getElementById('appointmentModal').style.display = 'block';
        }

        function openEditModal(id, doctorId, patientId, appointmentDate, description) {
            document.getElementById('modalTitle').textContent = 'Editar Consulta';
            document.getElementById('modalAction').value = 'update';
            document.getElementById('modalId').value = id;
            document.getElementById('modal_doctor_id').value = doctorId;
            document.getElementById('modal_patient_id').value = patientId;
            document.getElementById('modal_appointment_date').value = appointmentDate;
            document.getElementById('modal_description').value = description;
            document.getElementById('modalSubmitBtn').textContent = 'Atualizar Consulta';
            document.getElementById('appointmentModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('appointmentModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var modal = document.getElementById('appointmentModal');
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
