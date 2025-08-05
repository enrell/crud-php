<?php

class Password
{
  private string $password;

  public function __construct(string $password)
  {
    $this->password = $password;
    $this->validate($password);
  }

  private function validate(string $password): bool
  {
    if (strlen($password) < 8) {
      throw new InvalidArgumentException(
        "Password must be at least 8 characters long.",
      );
    }
    return true;
  }

  public function getHash(): string
  {
    return password_hash($this->password, PASSWORD_DEFAULT);
  }
}

?>
