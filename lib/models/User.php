<?php

require_once __DIR__ . "/Name.php";
require_once __DIR__ . "/Email.php";
require_once __DIR__ . "/Password.php";

class User
{
  private ?int $id = null;
  private Name $name;
  private Email $email;
  private Password $password;

  public function __construct(
    ?int $id,
    Name $name,
    Email $email,
    Password $password,
  ) {
    $this->id = $id;
    $this->name = $name;
    $this->email = $email;
    $this->password = $password;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getName(): Name
  {
    return $this->name;
  }

  public function getEmail(): Email
  {
    return $this->email;
  }

  public function getPassword(): Password
  {
    return $this->password;
  }

  public function getHashedPassword(): string
  {
    return $this->password->getHash();
  }

  public function verifyPassword(string $password): bool
  {
    return password_verify($password, $this->getHashedPassword());
  }
}
