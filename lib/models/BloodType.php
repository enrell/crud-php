<?php

class BloodType
{
  private string $value;
  private const VALID_BLOOD_TYPES = [
    "A+",
    "A-",
    "B+",
    "B-",
    "AB+",
    "AB-",
    "O+",
    "O-",
  ];

  public function __construct(string $value)
  {
    $this->validate($value);
    $this->value = $value;
  }

  private function validate(string $value): void
  {
    if (empty($value)) {
      throw new InvalidArgumentException("Blood type cannot be empty.");
    }

    $normalizedValue = strtoupper(trim($value));
    if (!in_array($normalizedValue, self::VALID_BLOOD_TYPES)) {
      throw new InvalidArgumentException(
        "Invalid blood type. Valid types: " .
          implode(", ", self::VALID_BLOOD_TYPES),
      );
    }
  }

  public function getValue(): string
  {
    return strtoupper(trim($this->value));
  }

  public function isRhPositive(): bool
  {
    return str_ends_with($this->getValue(), "+");
  }

  public function getAboGroup(): string
  {
    return rtrim($this->getValue(), "+-");
  }
}

?>
