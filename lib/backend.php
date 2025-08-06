<?php

require_once __DIR__ . "/config/Database.php";
require_once __DIR__ . "/security/SecurityHelper.php";
require_once __DIR__ . "/repositories/UserRepository.php";
require_once __DIR__ . "/repositories/DoctorRepository.php";
require_once __DIR__ . "/repositories/PatientRepository.php";
require_once __DIR__ . "/repositories/AppointmentRepository.php";

// Initialize repositories
$userRepository = new UserRepository($db);
$doctorRepository = new DoctorRepository($db);
$patientRepository = new PatientRepository($db);
$appointmentRepository = new AppointmentRepository($db);
