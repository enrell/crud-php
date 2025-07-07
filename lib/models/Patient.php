<?php

require_once __DIR__ . '/Person.php';
require_once __DIR__ . '/BloodType.php';

class Patient extends Person {
    private BloodType $bloodType;

    public function __construct(string $name, string $birthdate, string $bloodType) {
        parent::__construct($name, $birthdate);
        $this->bloodType = new BloodType($bloodType);
    }

    public function getBloodType(): string {
        return $this->bloodType->getValue();
    }

}

?>
