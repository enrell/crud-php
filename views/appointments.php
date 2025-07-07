<?php
require_once '../lib/backend.php';
requireAuth();




$currentPage = 'appointments';
include 'header.php';

// Handle form submissions
if ($_POST) {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            $doctorId = (int)($_POST['doctor_id'] ?? 0);
            $patientId = (int)($_POST['patient_id'] ?? 0);
            $appointmentDate = $_POST['appointment_date'] ?? '';
            $appointmentDate = str_replace('T', ' ', $appointmentDate) . ':00';
            $description = $_POST['description'] ?? '';
            
            try {
                $result = $appointmentRepository->create($doctorId, $patientId, $appointmentDate, $description);
                if ($result) {
                    $_SESSION['success_message'] = 'Appointment created successfully!';
                } else {
                    $_SESSION['error_message'] = 'Failed to create appointment.';
                }
            } catch (InvalidArgumentException $e) {
                $_SESSION['error_message'] = $e->getMessage();
            }
        } elseif ($action === 'update') {
            $id = (int)($_POST['id'] ?? 0);
            $doctorId = (int)($_POST['doctor_id'] ?? 0);
            $patientId = (int)($_POST['patient_id'] ?? 0);
            $appointmentDate = $_POST['appointment_date'] ?? '';
            $appointmentDate = str_replace('T', ' ', $appointmentDate) . ':00';
            $description = $_POST['description'] ?? '';
            
            try {
                $result = $appointmentRepository->update($id, $doctorId, $patientId, $appointmentDate, $description);
                if ($result) {
                    $_SESSION['success_message'] = 'Appointment updated successfully!';
                } else {
                    $error = 'Failed to update appointment.';
                }
            } catch (InvalidArgumentException $e) {
                $_SESSION['error_message'] = $e->getMessage();
            }
        } elseif ($action === 'delete') {
            $id = (int)($_POST['id'] ?? 0);
            $result = $appointmentRepository->delete($id);
            if ($result) {
                $_SESSION['success_message'] = 'Appointment deleted successfully!';
            } else {
                $_SESSION['error_message'] = 'Failed to delete appointment. The appointment may not exist or may have already been deleted. Please refresh the page and try again.';
            }
        }
    }

$appointments = $appointmentRepository->findAll();
$doctors = $doctorRepository->findAll();
$patients = $patientRepository->findAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments - Medical Appointments</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    

    <main class="container">
        <div class="page-header-with-action">
            <h2>Appointments Management</h2>
            <button class="btn-add" onclick="openModal()">+ New Appointment</button>
        </div>
        
        
        
        
        
        <?php
        if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error"><?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']);
        endif; ?>
        
        <?php
        if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success_message']) ?></div>
            <?php unset($_SESSION['success_message']);
        endif; ?>

        

        <!-- Appointments List -->
        <div class="table-container">
            <div class="table-header">
                <h3>All Appointments</h3>
                <div class="table-stats"><?= count($appointments) ?> total appointments</div>
            </div>
            <table class="appointments-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Doctor</th>
                        <th>Patient</th>
                        <th>Date & Time</th>
                        <th>Description</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="6" class="empty-state">
                                <div>
                                    <h3>No appointments scheduled</h3>
                                    <p>Click the "New Appointment" button to schedule your first appointment.</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $appointment): ?>
                            <tr>
                                <td><?= htmlspecialchars($appointment['id']) ?></td>
                                <td><?= htmlspecialchars($appointment['doctor_name']) ?></td>
                                <td><?= htmlspecialchars($appointment['patient_name']) ?></td>
                                <td><?= htmlspecialchars(date('M j, Y g:i A', strtotime($appointment['appointment_date']))) ?></td>
                                <td><?= htmlspecialchars($appointment['description']) ?></td>
                                <td class="actions">
                                    <button class="btn btn-warning" onclick="openEditModal(<?= $appointment['id'] ?>, <?= $appointment['doctor_id'] ?>, <?= $appointment['patient_id'] ?>, '<?= date('Y-m-d\TH:i', strtotime($appointment['appointment_date'])) ?>', '<?= htmlspecialchars($appointment['description']) ?>')">Edit</button>
                                    <form method="POST" style="display: inline;">
                        
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $appointment['id'] ?>">
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

    <!-- Modal for adding new appointment -->
    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Schedule New Appointment</h3>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form method="POST">
                <input type="hidden" name="action" id="modalAction" value="create">
                <input type="hidden" name="id" id="modalId" value="">
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modal_doctor_id">Doctor:</label>
                        <select id="modal_doctor_id" name="doctor_id" required>
                            <option value="">Select Doctor</option>
                            <?php foreach ($doctors as $doctor): ?>
                                <option value="<?= $doctor['id'] ?>">
                                    <?= htmlspecialchars($doctor['name']) ?> - <?= htmlspecialchars($doctor['expertise']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_patient_id">Patient:</label>
                        <select id="modal_patient_id" name="patient_id" required>
                            <option value="">Select Patient</option>
                            <?php foreach ($patients as $patient): ?>
                                <option value="<?= $patient['id'] ?>">
                                    <?= htmlspecialchars($patient['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_appointment_date">Appointment Date & Time:</label>
                        <input type="datetime-local" id="modal_appointment_date" name="appointment_date" required 
                               min="<?= date('Y-m-d\TH:i') ?>">
                    </div>

                    <div class="form-group">
                        <label for="modal_description">Description:</label>
                        <textarea id="modal_description" name="description" rows="3" required 
                                  placeholder="Describe the reason for the appointment..."></textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                        <button type="submit" class="btn btn-success" id="modalSubmitBtn">Schedule Appointment</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('modalTitle').textContent = 'Schedule New Appointment';
            document.getElementById('modalAction').value = 'create';
            document.getElementById('modalId').value = '';
            document.getElementById('modal_doctor_id').value = '';
            document.getElementById('modal_patient_id').value = '';
            document.getElementById('modal_appointment_date').value = '';
            document.getElementById('modal_description').value = '';
            document.getElementById('modalSubmitBtn').textContent = 'Schedule Appointment';
            document.getElementById('appointmentModal').style.display = 'block';
        }

        function openEditModal(id, doctorId, patientId, appointmentDate, description) {
            document.getElementById('modalTitle').textContent = 'Edit Appointment';
            document.getElementById('modalAction').value = 'update';
            document.getElementById('modalId').value = id;
            document.getElementById('modal_doctor_id').value = doctorId;
            document.getElementById('modal_patient_id').value = patientId;
            document.getElementById('modal_appointment_date').value = appointmentDate;
            document.getElementById('modal_description').value = description;
            document.getElementById('modalSubmitBtn').textContent = 'Update Appointment';
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
