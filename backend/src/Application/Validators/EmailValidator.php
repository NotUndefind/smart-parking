<?php

declare(strict_types=1);

namespace App\Application\Validators;

final class EmailValidator
{
    public function validate(string $email): void
    {
        if (empty($email)) {
            throw new \InvalidArgumentException('Email cannot be empty');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
    }
}

