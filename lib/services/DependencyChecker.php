<?php

class DependencyChecker
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function hasDoctorActiveAppointments(int $doctorId): bool
  {
    $stmt = $this->db->prepare(
      "SELECT COUNT(*) FROM appointment WHERE doctor_id = ? AND deleted_at IS NULL",
    );
    $stmt->execute([$doctorId]);
    return $stmt->fetchColumn() > 0;
  }

  public function hasPatientActiveAppointments(int $patientId): bool
  {
    $stmt = $this->db->prepare(
      "SELECT COUNT(*) FROM appointment WHERE patient_id = ? AND deleted_at IS NULL",
    );
    $stmt->execute([$patientId]);
    return $stmt->fetchColumn() > 0;
  }

  public function getActiveAppointmentCount(int $doctorId, int $patientId): int
  {
    if ($patientId !== null) {
      $stmt = $this->db->prepare(
        "SELECT COUNT(*) FROM appointment WHERE patient_id = ? AND deleted_at IS NULL",
      );
      $stmt->execute([$patientId]);
    } else {
      $stmt = $this->db->prepare(
        "SELECT COUNT(*) FROM appointment WHERE doctor_id = ? AND deleted_at IS NULL",
      );
      $stmt->execute([$doctorId]);
    }

    return $stmt->fetchColumn();
  }

  public function ensureCanDeleteDoctor(int $doctorId): void
  {
    if ($this->hasDoctorActiveAppointments($doctorId)) {
      throw new InvalidArgumentException(
        "Cannot delete doctor with active appointments",
      );
    }
  }

  public function ensureCanDeletePatient(int $patientId): void
  {
    if ($this->hasPatientActiveAppointments($patientId)) {
      throw new InvalidArgumentException(
        "Cannot delete patient with active appointments",
      );
    }
  }
}

?>
