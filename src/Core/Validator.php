<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    private array $errors = [];
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function required(string $field, string $message): self
    {
        $value = $this->data[$field] ?? null;
        if (is_string($value)) {
            $value = trim($value);
        }
        if ($value === null || $value === '' || $value === []) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function email(string $field, string $message): self
    {
        $value = trim((string) ($this->data[$field] ?? ''));
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function min(string $field, int $length, string $message): self
    {
        $value = (string) ($this->data[$field] ?? '');
        if ($value !== '' && mb_strlen($value) < $length) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function max(string $field, int $length, string $message): self
    {
        $value = (string) ($this->data[$field] ?? '');
        if (mb_strlen($value) > $length) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function numeric(string $field, string $message): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !is_numeric($value)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function confirmed(string $field, string $message): self
    {
        if (($this->data[$field] ?? null) !== ($this->data[$field . '_confirmation'] ?? null)) {
            $this->errors[$field . '_confirmation'] = $message;
        }
        return $this;
    }

    public function in(string $field, array $allowed, string $message): self
    {
        $value = $this->data[$field] ?? null;
        if ($value !== null && $value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = $message;
        }
        return $this;
    }

    public function add(string $field, string $message): self
    {
        $this->errors[$field] = $message;
        return $this;
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
