<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../security/SecurityHelper.php";

require_once __DIR__ . "/../models/EntityId.php";
require_once __DIR__ . "/../models/Name.php";
require_once __DIR__ . "/../services/DependencyChecker.php";

class DoctorRepository
{
  private PDO $db;
  private DependencyChecker $dependencyChecker;

  public function __construct(PDO $db)
  {
    $this->db = $db;
    $this->dependencyChecker = new DependencyChecker($db);
  }

  public function create(string $name, string $expertise): bool
  {
    try {
      requireAuth();

      $nameObj = new Name($name);
      $stmt = $this->db->prepare(
        "INSERT INTO doctor (name, expertise) VALUES (?, ?)",
      );
      $result = $stmt->execute([$nameObj->getValue(), $expertise]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "DoctorRepository Error: " . $e->getMessage(),
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

      $stmt = $this->db->prepare("SELECT * FROM doctor WHERE id = ?");
      $stmt->execute([$entityId->getValue()]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log(
        "DoctorRepository Error: " . $e->getMessage(),
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

      $stmt = $this->db->prepare("SELECT * FROM doctor ORDER BY name");
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      error_log(
        "DoctorRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return [];
    }
  }

  public function update(int $id, string $name, string $expertise): bool
  {
    try {
      requireAuth();

      $entityId = new EntityId($id);
      $nameObj = new Name($name);
      $stmt = $this->db->prepare(
        "UPDATE doctor SET name = ?, expertise = ? WHERE id = ?",
      );
      $result = $stmt->execute([
        $nameObj->getValue(),
        $expertise,
        $entityId->getValue(),
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "DoctorRepository Error: " . $e->getMessage(),
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
      $this->dependencyChecker->ensureCanDeleteDoctor($entityId->getValue());

      $stmt = $this->db->prepare("DELETE FROM doctor WHERE id = ?");
      $result = $stmt->execute([$entityId->getValue()]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "DoctorRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }
}
