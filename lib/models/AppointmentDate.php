<?php

class AppointmentDate {
    private string $value;

    public function __construct(string $value) {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(string $value): void {
        if (empty($value)) {
            throw new InvalidArgumentException("Appointment date cannot be empty.");
        }

        $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if (!$date || $date->format('Y-m-d H:i:s') !== $value) {
            throw new InvalidArgumentException("Invalid appointment date format. Expected format: Y-m-d H:i:s");
        }

        $now = new DateTime();
        if ($date <= $now) {
            throw new InvalidArgumentException("Appointment date must be in the future.");
        }

        // Check if appointment is within reasonable time frame (max 2 years in future)
        $maxFutureDate = (new DateTime())->modify('+2 years');
        if ($date > $maxFutureDate) {
            throw new InvalidArgumentException("Appointment date cannot be more than 2 years in the future.");
        }

        // Check if appointment is during business hours (8 AM to 6 PM)
        $hour = (int)$date->format('H');
        if ($hour < 8 || $hour >= 18) {
            throw new InvalidArgumentException("Appointment must be scheduled during business hours (8 AM to 6 PM).");
        }

        // Check if appointment is not on weekend
        $dayOfWeek = (int)$date->format('w');
        if ($dayOfWeek === 0 || $dayOfWeek === 6) {
            throw new InvalidArgumentException("Appointments cannot be scheduled on weekends.");
        }
    }

    public function getValue(): string {
        return $this->value;
    }

    public function getFormattedDate(): string {
        $date = new DateTime($this->value);
        return $date->format('M j, Y g:i A');
    }

    public function isToday(): bool {
        $appointmentDate = new DateTime($this->value);
        $today = new DateTime();
        return $appointmentDate->format('Y-m-d') === $today->format('Y-m-d');
    }

    public function getDaysUntilAppointment(): int {
        $appointmentDate = new DateTime($this->value);
        $now = new DateTime();
        return $now->diff($appointmentDate)->days;
    }
}

?>
