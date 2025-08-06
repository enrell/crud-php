<?php

require_once __DIR__ . "/Name.php";

abstract class Person
{
  protected Name $name;
  protected DateTime $birthdate;

  public function __construct(string $name, string $birthdate)
  {
    $this->name = new Name($name);
    $this->birthdate = $this->validateBirthdate($birthdate);
  }

  private function validateBirthdate(string $birthdate): DateTime
  {
    if (empty($birthdate)) {
      throw new InvalidArgumentException(
        "A data de nascimento não pode estar vazia.",
      );
    }

    $date = DateTime::createFromFormat("Y-m-d", $birthdate);
    if (!$date || $date->format("Y-m-d") !== $birthdate) {
      throw new InvalidArgumentException(
        "Formato de data de nascimento inválido. O formato esperado é YYYY-MM-DD.",
      );
    }

    $date->setTime(0, 0, 0);
    $today = new DateTime("today");

    if ($date > $today) {
      throw new InvalidArgumentException(
        "A data de nascimento não pode ser no futuro.",
      );
    }

    return $date;
  }

  public function getName(): string
  {
    return $this->name->getValue();
  }

  public function getBirthdate(): string
  {
    return $this->birthdate->format("Y-m-d");
  }

  public function getAge(): int
  {
    $now = new DateTime();
    return $now->diff($this->birthdate)->y;
  }

  public function getBirthdateFormatted(): string
  {
    return $this->birthdate->format("d/m/Y");
  }
}

?>
