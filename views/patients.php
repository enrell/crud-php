<?php
require_once "../lib/backend.php";
requireAuth();

$currentPage = "patients";
include "header.php";

// Handle form submissions
if ($_POST) {
  $action = $_POST["action"] ?? "";

  if ($action === "create") {
    $name = $_POST["name"] ?? "";
    $birthdate = $_POST["birthdate"] ?? "";
    $bloodType = $_POST["blood_type"] ?? "";

    try {
      $result = $patientRepository->create($name, $birthdate, $bloodType);
      if ($result) {
        $_SESSION["success_message"] = "Patient created successfully!";
      } else {
        $_SESSION["error_message"] = "Failed to create patient.";
      }
    } catch (InvalidArgumentException $e) {
      $_SESSION["error_message"] = $e->getMessage();
    }
  } elseif ($action === "update") {
    $id = (int) ($_POST["id"] ?? 0);
    $name = $_POST["name"] ?? "";
    $birthdate = $_POST["birthdate"] ?? "";
    $bloodType = $_POST["blood_type"] ?? "";

    try {
      $result = $patientRepository->update($id, $name, $birthdate, $bloodType);
      if ($result) {
        $_SESSION["success_message"] = "Patient updated successfully!";
      } else {
        $_SESSION["error_message"] = "Failed to update patient.";
      }
    } catch (InvalidArgumentException $e) {
      $_SESSION["error_message"] = $e->getMessage();
    }
  } elseif ($action === "delete") {
    $id = (int) ($_POST["id"] ?? 0);
    $result = $patientRepository->delete($id);
    if ($result) {
      $_SESSION["success_message"] = "Patient deleted successfully!";
    } else {
      $_SESSION["error_message"] =
        "Failed to delete patient. They may have active appointments.";
    }
  }
}

$patients = $patientRepository->findAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patients - Medical Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>


    <main class="container">
        <div class="page-header-with-action">
            <h2>Patients Management</h2>
            <button class="btn-add" onclick="openModal()">+ Add Patient</button>
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
                <h3>All Patients</h3>
                <div class="table-stats"><?= count(
                  $patients,
                ) ?> total patients</div>
            </div>
            <table class="patients-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Birth Date</th>
                        <th>Blood Type</th>
                        <th style="padding-right: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($patients)): ?>
                        <tr>
                            <td colspan="5" class="empty-state">
                                <div>
                                    <h3>No patients registered</h3>
                                    <p>Click the "Add Patient" button to register your first patient.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($patients as $patient): ?>
                            <tr>
                                <td><?= htmlspecialchars($patient["id"]) ?></td>
                                <td><?= htmlspecialchars(
                                  $patient["name"],
                                ) ?></td>
                                <td style="padding-right: 145px;"><?= htmlspecialchars(
                                  $patient["birthdate"],
                                ) ?></td>
                                <td style="padding-right: 80px;"><?= htmlspecialchars(
                                  $patient["blood_type"],
                                ) ?></td>
                                <td class="actions">
                                    <button class="btn btn-warning" onclick="openEditModal(<?= $patient[
                                      "id"
                                    ] ?>, '<?= htmlspecialchars(
  $patient["name"],
) ?>', '<?= $patient["birthdate"] ?>', '<?= htmlspecialchars(
  $patient["blood_type"],
) ?>')">Edit</button>
                                    <form method="POST" style="display: inline;">

                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $patient[
                                          "id"
                                        ] ?>">
                                        <button type="submit" class="btn btn-danger">Delete</button>
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
                <h3 id="modalTitle">Add New Patient</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="modalAction" value="create">
                <input type="hidden" name="id" id="modalId" value="">

                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_name">Patient Name:</label>
                        <input type="text" id="modal_name" name="name" required>
                    </div>

                    <div class="form-group">
                        <label for="modal_birthdate">Birth Date:</label>
                        <input type="date" id="modal_birthdate" name="birthdate" required>
                    </div>

                    <div class="form-group">
                        <label for="modal_blood_type">Blood Type:</label>
                        <select id="modal_blood_type" name="blood_type" required>
                            <option value="">Select Blood Type</option>
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
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-success" id="modalSubmitBtn">Add Patient</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Add New Patient';
            document.getElementById('modalAction').value = 'create';
            document.getElementById('modalId').value = '';
            document.getElementById('modal_name').value = '';
            document.getElementById('modal_birthdate').value = '';
            document.getElementById('modal_blood_type').value = '';
            document.getElementById('modalSubmitBtn').textContent = 'Add Patient';
            document.getElementById('patientModal').style.display = 'block';
        }

        function openEditModal(id, name, birthdate, bloodType) {
            document.getElementById('modalTitle').textContent = 'Edit Patient';
            document.getElementById('modalAction').value = 'update';
            document.getElementById('modalId').value = id;
            document.getElementById('modal_name').value = name;
            document.getElementById('modal_birthdate').value = birthdate;
            document.getElementById('modal_blood_type').value = bloodType;
            document.getElementById('modalSubmitBtn').textContent = 'Update Patient';
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
