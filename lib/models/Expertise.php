<?php

class Expertise
{
  private string $value;
  private const int MIN_LENGTH = 5;
  private const int MAX_LENGTH = 100;

  public function __construct(string $value)
  {
    $this->validate($value);
    $this->value = trim($value);
  }

  private function validate(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException("Expertise cannot be empty.");
    }

    $trimmedValue = trim($value);
    if (strlen($trimmedValue) < self::MIN_LENGTH) {
      throw new InvalidArgumentException(
        "Expertise must be at least " . self::MIN_LENGTH . " characters long.",
      );
    }

    if (strlen($trimmedValue) > self::MAX_LENGTH) {
      throw new InvalidArgumentException(
        "Expertise cannot exceed " . self::MAX_LENGTH . " characters.",
      );
    }

    // Check for valid characters (letters, spaces, hyphens, apostrophes)
    if (!preg_match('/^[a-zA-Z\s\-\']+$/', $trimmedValue)) {
      throw new InvalidArgumentException(
        "Expertise can only contain letters, spaces, hyphens, and apostrophes.",
      );
    }
  }

  public function getValue(): string
  {
    return $this->value;
  }

  public function getCapitalized(): string
  {
    return ucwords(strtolower($this->value));
  }
}
