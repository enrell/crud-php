<?php

class Name
{
  private string $value;

  public function __construct(string $value)
  {
    $this->value = $value;
    $this->validate($value);
  }

  private function validate(string $value): bool
  {
    if (empty($value)) {
      throw new InvalidArgumentException("Name cannot be empty.");
    }
    if (preg_match("/[^a-zA-Z\s]/", $value)) {
      throw new InvalidArgumentException(
        "Name can only contain letters and spaces.",
      );
    }
    if (strlen($value) < 6 || strlen($value) > 50) {
      throw new InvalidArgumentException(
        "Name must be between 6 and 50 characters long.",
      );
    }
    return true;
  }

  public function getValue(): string
  {
    return $this->value;
  }
}
