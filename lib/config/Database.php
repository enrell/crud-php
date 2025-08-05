<?php
try {
  $databaseFile = __DIR__ . "../../../database.sqlite";
  $db = new PDO('sqlite:' . $databaseFile);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
  echo "Connection error:" . $e->getMessage();
  exit();
}

try {
  $db->exec(statement: "CREATE TABLE IF NOT EXISTS user (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        username TEXT NOT NULL UNIQUE,
                        email TEXT NOT NULL UNIQUE,
                        profile_image TEXT,
                        password TEXT NOT NULL,
                        last_login TEXT DEFAULT CURRENT_TIMESTAMP
                      );
          ");

  $db->exec(statement: "CREATE TABLE IF NOT EXISTS doctor (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        expertise TEXT NOT NULL
                        );
          ");

  $db->exec(statement: "CREATE TABLE IF NOT EXISTS patient (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        name TEXT NOT NULL,
                        birthdate TEXT NOT NULL,
                        blood_type  TEXT NOT NULL
                      );
          ");

  $db->exec(statement: "CREATE TABLE IF NOT EXISTS appointment (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        doctor_id INTEGER NOT NULL,
                        patient_id INTEGER NOT NULL,
                        appointment_date TIMESTAMP NOT NULL,
                        deleted_at TIMESTAMP NULL,
                        description TEXT NOT NULL,
                        FOREIGN KEY (doctor_id) REFERENCES doctor(id),
                        FOREIGN KEY (patient_id) REFERENCES patient(id)
                      );
          ");
  
} catch (PDOException $e) {
  echo "Error creating tables.". $e->getMessage();
}

?>
