<?php

class UUID
{
  private string $value;

  public function __construct(?string $uuid = null)
  {
    if ($uuid === null) {
      $this->value = self::generate();
    } else {
      if (!self::isValid($uuid)) {
        throw new InvalidArgumentException("Invalid UUID format");
      }
      $this->value = $uuid;
    }
  }

  public static function generate(): string
  {
    // UUID v4 generator
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // set version to 0100
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
  }

  public static function isValid(string $uuid): bool
  {
    $pattern = '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';
    return (bool) preg_match($pattern, $uuid);
  }

  public function getValue(): string
  {
    return $this->value;
  }

  public function __toString(): string
  {
    return $this->value;
  }
}
