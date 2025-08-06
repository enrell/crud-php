<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../security/SecurityHelper.php";
require_once __DIR__ . "/../models/AppointmentDate.php";
require_once __DIR__ . "/../models/Description.php";

class AppointmentRepository
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function create(
    int $doctorId,
    int $patientId,
    string $appointmentDate,
    string $description,
  ): bool {
    try {
      requireAuth();

      $appointmentDateObj = new AppointmentDate($appointmentDate);
      $descriptionObj = new Description($description);

      $stmt = $this->db->prepare(
        "INSERT INTO appointment (doctor_id, patient_id, appointment_date, description) VALUES (?, ?, ?, ?)",
      );
      $result = $stmt->execute([
        $doctorId,
        $patientId,
        $appointmentDateObj->getValue(),
        $descriptionObj->getValue(),
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "AppointmentRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }

  public function findById(int $id): ?array
  {
    try {
      requireAuth();

      $stmt = $this->db
        ->prepare("SELECT a.*, d.name as doctor_name, p.name as patient_name
                                        FROM appointment a
                                        JOIN doctor d ON a.doctor_id = d.id
                                        JOIN patient p ON a.patient_id = p.id
                                        WHERE a.id = ? AND a.deleted_at IS NULL");
      $stmt->execute([$id]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log(
        "AppointmentRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return null;
    }
  }

  public function findAll(): array
  {
    try {
      requireAuth();

      $stmt = $this->db
        ->prepare("SELECT a.*, d.name as doctor_name, p.name as patient_name
                                        FROM appointment a
                                        JOIN doctor d ON a.doctor_id = d.id
                                        JOIN patient p ON a.patient_id = p.id
                                        WHERE a.deleted_at IS NULL
                                        ORDER BY a.appointment_date");
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log(
        "AppointmentRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return [];
    }
  }

  public function update(
    int $id,
    int $doctorId,
    int $patientId,
    string $appointmentDate,
    string $description,
  ): bool {
    try {
      requireAuth();

      $appointmentDateObj = new AppointmentDate($appointmentDate);
      $descriptionObj = new Description($description);

      $stmt = $this->db->prepare(
        "UPDATE appointment SET doctor_id = ?, patient_id = ?, appointment_date = ?, description = ? WHERE id = ? AND deleted_at IS NULL",
      );
      $result = $stmt->execute([
        $doctorId,
        $patientId,
        $appointmentDateObj->getValue(),
        $descriptionObj->getValue(),
        $id,
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "AppointmentRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }

  public function delete(int $id): bool
  {
    try {
      requireAuth();

      $stmt = $this->db->prepare("DELETE FROM appointment WHERE id = ?");
      $result = $stmt->execute([$id]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "AppointmentRepository Delete Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }
}
