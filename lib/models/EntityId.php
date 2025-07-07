<?php

class EntityId {
    private int $value;

    public function __construct(int $value) {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate(int $value): void {
        if ($value <= 0) {
            throw new InvalidArgumentException("Entity ID must be a positive integer.");
        }

        // Prevent extremely large IDs that might indicate issues
        if ($value > 2147483647) { // Max int32
            throw new InvalidArgumentException("Entity ID is too large.");
        }
    }

    public function getValue(): int {
        return $this->value;
    }

    public function equals(EntityId $other): bool {
        return $this->value === $other->value;
    }

    public function __toString(): string {
        return (string) $this->value;
    }
}

?>
