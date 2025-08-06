<?php

class AppointmentDate
{
  private string $value;

  public function __construct(string $value)
  {
    $this->validate($value);
    $this->value = $value;
  }

  private function validate(string $value): void
  {
    if (empty($value)) {
      throw new InvalidArgumentException(
        "A data da consulta não pode estar vazia.",
      );
    }

    // O formato 'Y-m-d' é o padrão universal para <input type="date">
    $date = DateTime::createFromFormat("Y-m-d", $value);

    // Verifica se o formato da string está incorreto
    if ($date === false) {
      throw new InvalidArgumentException(
        "Formato de data inválido. O formato esperado é YYYY-MM-DD.",
      );
    }

    // Normaliza a data para o início do dia para uma comparação justa
    $date->setTime(0, 0, 0);
  }

  public function getValue(): string
  {
    return $this->value;
  }

  public function getFormattedDate(): string
  {
    $date = new DateTime($this->value);
    return $date->format("d/m/Y"); // Formato brasileiro para exibição
  }

  public function isToday(): bool
  {
    $appointmentDate = new DateTime($this->value);
    $today = new DateTime();
    return $appointmentDate->format("Y-m-d") === $today->format("Y-m-d");
  }

  public function getDaysUntilAppointment(): int
  {
    $appointmentDate = new DateTime($this->value);
    $now = new DateTime();
    return $now->diff($appointmentDate)->days;
  }
}

?>
