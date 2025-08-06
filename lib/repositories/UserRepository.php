<?php

require_once __DIR__ . "/../config/Database.php";
require_once __DIR__ . "/../security/SecurityHelper.php";
require_once __DIR__ . "/../models/Email.php";
require_once __DIR__ . "/../models/Name.php";
require_once __DIR__ . "/../models/Password.php";
require_once __DIR__ . "/../models/UUID.php";

class UserRepository
{
  private PDO $db;

  public function __construct(PDO $db)
  {
    $this->db = $db;
  }

  public function create(
    string $username,
    string $email,
    string $hashedPassword,
  ): bool {
    try {
      $nameObj = new Name($username);
      $emailObj = new Email($email);
      $uuidObj = new UUID();

      if (
        $this->findByEmail($emailObj->getValue()) ||
        $this->findByUsername($nameObj->getValue())
      ) {
        throw new InvalidArgumentException("User already exists");
      }

      $stmt = $this->db->prepare(
        "INSERT INTO user (id, username, email, password) VALUES (?, ?, ?, ?)",
      );
      $result = $stmt->execute([
        $uuidObj->getValue(),
        $nameObj->getValue(),
        $emailObj->getValue(),
        $hashedPassword,
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }

  public function findById(string $id): ?array
  {
    try {
      $uuidObj = new UUID($id);

      $stmt = $this->db->prepare(
        "SELECT id, username, email, profile_image FROM user WHERE id = ?",
      );
      $stmt->execute([$uuidObj->getValue()]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return null;
    }
  }

  public function findByIdWithPassword(string $id): ?array
  {
    try {
      $uuidObj = new UUID($id);

      $stmt = $this->db->prepare(
        "SELECT id, username, email, password, profile_image FROM user WHERE id = ?",
      );
      $stmt->execute([$uuidObj->getValue()]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return null;
    }
  }

  public function findByEmail(string $email): ?array
  {
    try {
      $stmt = $this->db->prepare("SELECT * FROM user WHERE email = ?");
      $stmt->execute([$email]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return null;
    }
  }

  public function findByUsername(string $username): ?array
  {
    try {
      $stmt = $this->db->prepare("SELECT * FROM user WHERE username = ?");
      $stmt->execute([$username]);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ?: null;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return null;
    }
  }

  public function update(string $id, string $username, string $email): bool
  {
    try {
      $uuidObj = new UUID($id);
      $nameObj = new Name($username);
      $emailObj = new Email($email);

      if (!isAuthenticated() || $_SESSION["user_id"] != $uuidObj->getValue()) {
        throw new Exception("Unauthorized access attempt");
      }

      $stmt = $this->db->prepare(
        "UPDATE user SET username = ?, email = ? WHERE id = ?",
      );
      $result = $stmt->execute([
        $nameObj->getValue(),
        $emailObj->getValue(),
        $uuidObj->getValue(),
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }

  public function delete(string $id): bool
  {
    try {
      $uuidObj = new UUID($id);

      if (!isAuthenticated() || $_SESSION["user_id"] != $uuidObj->getValue()) {
        throw new Exception("Unauthorized access attempt");
      }

      $stmt = $this->db->prepare("DELETE FROM user WHERE id = ?");
      $result = $stmt->execute([$uuidObj->getValue()]);

      if ($result) {
        $this->deleteUserDirectory($uuidObj->getValue());
      }

      return $result;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }

  private function deleteUserDirectory(string $id): void
  {
    try {
      $userDir = __DIR__ . "/../../public/" . $id;

      if (is_dir($userDir)) {
        $it = new RecursiveDirectoryIterator(
          $userDir,
          RecursiveDirectoryIterator::SKIP_DOTS,
        );
        $files = new RecursiveIteratorIterator(
          $it,
          RecursiveIteratorIterator::CHILD_FIRST,
        );
        foreach ($files as $file) {
          if ($file->isDir()) {
            rmdir($file->getRealPath());
          } else {
            unlink($file->getRealPath());
          }
        }
        rmdir($userDir);
      }
    } catch (Exception $e) {
      error_log(
        "UserRepository Error: Could not delete user directory for user ID: $id. " .
          $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
    }
  }

  public function authenticate(string $email, string $password): ?array
  {
    try {
      $user = $this->findByEmail($email);

      if (!$user || !password_verify($password, $user["password"])) {
        return null;
      }

      $stmt = $this->db->prepare(
        "UPDATE user SET last_login = datetime('now') WHERE id = ?",
      );
      $stmt->execute([$user["id"]]);

      return $user;
    } catch (PDOException $e) {
      return null;
    }
  }

  public function updateProfileImage(string $id, string $profile_image): bool
  {
    try {
      $uuidObj = new UUID($id);

      if (!isAuthenticated() || $_SESSION["user_id"] != $uuidObj->getValue()) {
        throw new Exception("Unauthorized access attempt");
      }

      $stmt = $this->db->prepare(
        "UPDATE user SET profile_image = ? WHERE id = ?",
      );
      $result = $stmt->execute([$profile_image, $uuidObj->getValue()]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }

  public function deleteProfileImage(string $id): bool
  {
    try {
      $uuidObj = new UUID($id);

      if (!isAuthenticated() || $_SESSION["user_id"] != $uuidObj->getValue()) {
        throw new Exception("Unauthorized access attempt");
      }

      $stmt = $this->db->prepare(
        "UPDATE user SET profile_image = NULL WHERE id = ?",
      );
      $result = $stmt->execute([$uuidObj->getValue()]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "UserRepositoryError: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }

  public function updatePassword(string $id, string $newPassword): bool
  {
    try {
      $uuidObj = new UUID($id);
      $passwordObj = new Password($newPassword);

      if (!isAuthenticated() || $_SESSION["user_id"] != $uuidObj->getValue()) {
        throw new Exception("Unauthorized access attempt");
      }

      $stmt = $this->db->prepare(
        "UPDATE user SET password = ? WHERE id = ?",
      );
      $result = $stmt->execute([
        $passwordObj->getHash(),
        $uuidObj->getValue(),
      ]);

      return $result;
    } catch (PDOException $e) {
      error_log(
        "UserRepository Error: " . $e->getMessage(),
        3,
        __DIR__ . "/../../debug.log",
      );
      return false;
    }
  }
}
