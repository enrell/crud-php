<?php 
class Email {
    private string $email;

    public function __construct(string $email) {
      $this->email = $email;
      $this->validate($email);
    }

    private function validate(string $email): bool {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        } else {
            throw new InvalidArgumentException("Invalid email format: $email");
        }
    }

    public function getValue(): string {
        return $this->email;
    }
}

?>