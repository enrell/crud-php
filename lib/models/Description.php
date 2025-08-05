<?php

class Description
{
  private string $value;
  private const MIN_LENGTH = 10;
  private const MAX_LENGTH = 500;

  public function __construct(string $value)
  {
    $this->validate($value);
    $this->value = trim($value);
  }

  private function validate(string $value): void
  {
    if (empty(trim($value))) {
      throw new InvalidArgumentException("Description cannot be empty.");
    }

    $trimmedValue = trim($value);
    if (strlen($trimmedValue) < self::MIN_LENGTH) {
      throw new InvalidArgumentException(
        "Description must be at least " .
          self::MIN_LENGTH .
          " characters long.",
      );
    }

    if (strlen($trimmedValue) > self::MAX_LENGTH) {
      throw new InvalidArgumentException(
        "Description cannot exceed " . self::MAX_LENGTH . " characters.",
      );
    }

    // Check for basic content validation (not just spaces or special characters)
    if (preg_match('/^[\s\W]*$/', $trimmedValue)) {
      throw new InvalidArgumentException(
        "Description must contain meaningful content.",
      );
    }
  }

  public function getValue(): string
  {
    return $this->value;
  }

  public function getTruncated(int $length = 50): string
  {
    if (strlen($this->value) <= $length) {
      return $this->value;
    }
    return substr($this->value, 0, $length) . "...";
  }

  public function getWordCount(): int
  {
    return str_word_count($this->value);
  }

  public function getCharacterCount(): int
  {
    return strlen($this->value);
  }
}

?>
