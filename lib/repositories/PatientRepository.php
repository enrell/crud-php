<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../security/SecurityHelper.php";
require_once __DIR__ . "/../models/Patient.php";
require_once __DIR__ . "/../models/EntityId.php";
require_once __DIR__ . "/../services/DependencyChecker.php";

class PatientRepository
{
  private PDO $db;
  private DependencyChecker $dependencyChecker;

  public function __construct(PDO $db)
  {
    $this->db = $db;
    $this->dependencyChecker = new DependencyChecker($db);
  }

  public function create(
    string $name,
    string $birthdate,
    string $bloodType,
  ): bool {
    try {
      requireAuth();

      $patient = new Patient($name, $birthdate, $bloodType);

      $stmt = $this->db->prepare(
        "INSERT INTO patient (name, birthdate, blood_type) VALUES (?, ?, ?)",
      );
      $result = $stmt->execute([
        $patient->getName(),
        $patient->getBirthdate(),
        $patient->getBloodType(),
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "PatientRepository Error: " . $e->getMessage(),
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

      $entityId = new EntityId($id);

      $stmt = $this->db->prepare("SELECT * FROM patient WHERE id = ?");
      $stmt->execute([$entityId->getValue()]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log(
        "PatientRepository Error: " . $e->getMessage(),
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

      $stmt = $this->db->prepare("SELECT * FROM patient ORDER BY name");
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log(
        "PatientRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return [];
    }
  }

  public function update(
    int $id,
    string $name,
    string $birthdate,
    string $bloodType,
  ): bool {
    try {
      requireAuth();

      $entityId = new EntityId($id);
      $patient = new Patient($name, $birthdate, $bloodType);

      $stmt = $this->db->prepare(
        "UPDATE patient SET name = ?, birthdate = ?, blood_type = ? WHERE id = ?",
      );
      $result = $stmt->execute([
        $patient->getName(),
        $patient->getBirthdate(),
        $patient->getBloodType(),
        $entityId->getValue(),
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "PatientRepository Error: " . $e->getMessage(),
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

      $entityId = new EntityId($id);
      $this->dependencyChecker->ensureCanDeletePatient($entityId->getValue());

      $stmt = $this->db->prepare("DELETE FROM patient WHERE id = ?");
      $result = $stmt->execute([$entityId->getValue()]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "PatientRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }
}
