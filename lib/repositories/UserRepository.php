<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../security/SecurityHelper.php';
require_once __DIR__ . '/../models/Email.php';
require_once __DIR__ . '/../models/Name.php';
require_once __DIR__ . '/../models/Password.php';
require_once __DIR__ . '/../models/EntityId.php';

class UserRepository {
    private PDO $db;

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    public function create(string $username, string $email, string $hashedPassword): bool {
        try {
            $nameObj = new Name($username);
            $emailObj = new Email($email);
            
            // Check if user already exists
            if ($this->findByEmail($emailObj->getValue()) || $this->findByUsername($nameObj->getValue())) {
                throw new InvalidArgumentException("User already exists");
            }
            
            $stmt = $this->db->prepare("INSERT INTO user (username, email, password) VALUES (?, ?, ?)");
            $result = $stmt->execute([
                $nameObj->getValue(), 
                $emailObj->getValue(), 
                $hashedPassword
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("UserRepository Error: " . $e->getMessage(), 3, __DIR__ . '/../../debug.log');
            return false;
        }
    }

    public function findById(int $id): ?array {
        try {
            $entityId = new EntityId($id);
            
            $stmt = $this->db->prepare("SELECT id, username, email FROM user WHERE id = ?");
            $stmt->execute([$entityId->getValue()]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("UserRepository Error: " . $e->getMessage(), 3, __DIR__ . '/../../debug.log');
            return null;
        }
    }

    public function findByEmail(string $email): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("UserRepository Error: " . $e->getMessage(), 3, __DIR__ . '/../../debug.log');
            return null;
        }
    }

    public function findByUsername(string $username): ?array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM user WHERE username = ?");
            $stmt->execute([$username]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (PDOException $e) {
            error_log("UserRepository Error: " . $e->getMessage(), 3, __DIR__ . '/../../debug.log');
            return null;
        }
    }

    public function update(int $id, string $username, string $email): bool {
        try {
            $entityId = new EntityId($id);
            $nameObj = new Name($username);
            $emailObj = new Email($email);
            
            // Security check - users can only update their own data
            if (!isAuthenticated() || $_SESSION['user_id'] != $entityId->getValue()) {
                throw new Exception("Unauthorized access attempt");
            }
            
            $stmt = $this->db->prepare("UPDATE user SET username = ?, email = ? WHERE id = ?");
            $result = $stmt->execute([
                $nameObj->getValue(), 
                $emailObj->getValue(), 
                $entityId->getValue()
            ]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("UserRepository Error: " . $e->getMessage(), 3, __DIR__ . '/../../debug.log');
            return false;
        }
    }

    public function delete(int $id): bool {
        try {
            $entityId = new EntityId($id);
            
            // Security check - users can only delete their own account
            if (!isAuthenticated() || $_SESSION['user_id'] != $entityId->getValue()) {
                throw new Exception("Unauthorized access attempt");
            }
            
            $stmt = $this->db->prepare("DELETE FROM user WHERE id = ?");
            $result = $stmt->execute([$entityId->getValue()]);
            
            return $result;
        } catch (PDOException $e) {
            error_log("UserRepository Error: " . $e->getMessage(), 3, __DIR__ . '/../../debug.log');
            return false;
        }
    }

    public function authenticate(string $email, string $password): ?array {
        try {
            $user = $this->findByEmail($email);
            
            if (!$user || !password_verify($password, $user['password'])) {
                return null;
            }
            
            // Update last login
            $stmt = $this->db->prepare("UPDATE user SET last_login = datetime('now') WHERE id = ?");
            $stmt->execute([$user['id']]);
            
            return $user;
        } catch (PDOException $e) {
            return null;
        }
    }
}

?>
