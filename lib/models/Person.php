<?php

require_once __DIR__ . '/Name.php';

abstract class Person {
    protected Name $name;
    protected DateTime $birthdate;

    public function __construct(string $name, string $birthdate) {
        $this->name = new Name($name);
        $this->birthdate = $this->validateBirthdate($birthdate);
    }

    private function validateBirthdate(string $birthdate): DateTime {
        if (empty($birthdate)) {
            throw new InvalidArgumentException("Birthdate cannot be empty.");
        }

        $date = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$date || $date->format('Y-m-d') !== $birthdate) {
            throw new InvalidArgumentException("Invalid birthdate format. Expected format: Y-m-d");
        }

        $now = new DateTime();
        if ($date > $now) {
            throw new InvalidArgumentException("Birthdate cannot be in the future.");
        }

        $maxAgeDate = (new DateTime())->modify('-120 years');
        if ($date < $maxAgeDate) {
            throw new InvalidArgumentException("Birthdate indicates an absurd age (over 120 years).");
        }

        return $date;
    }

    public function getName(): string {
        return $this->name->getValue();
    }

    public function getBirthdate(): string {
        return $this->birthdate->format('Y-m-d');
    }

    public function getAge(): int {
        $now = new DateTime();
        return $now->diff($this->birthdate)->y;
    }

    public function getBirthdateFormatted(): string {
        return $this->birthdate->format('M j, Y');
    }
 
}

?>
