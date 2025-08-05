<?php

require_once __DIR__ . "/Person.php";
require_once __DIR__ . "/Expertise.php";

class Doctor extends Person
{
  private Expertise $expertise;

  public function __construct(
    string $name,
    string $birthdate,
    string $expertise,
  ) {
    parent::__construct($name, $birthdate);
    $this->expertise = new Expertise($expertise);
  }

  public function getExpertise(): string
  {
    return $this->expertise->getValue();
  }

  public function getExpertiseCapitalized(): string
  {
    return $this->expertise->getCapitalized();
  }
}

?>
