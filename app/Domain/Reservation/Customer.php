<?php

declare(strict_types=1);

namespace App\Domain\Reservation;

use InvalidArgumentException;

final readonly class Customer
{
    public string $name;

    public string $email;

    public function __construct(string $name, string $email)
    {
        $name = trim($name);
        $email = mb_strtolower(trim($email));

        if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
            throw new InvalidArgumentException(
                'Имя покупателя должно содержать от 2 до 80 символов.'
            );
        }

        if (
            $email === ''
            || mb_strlen($email) > 254
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            throw new InvalidArgumentException('Email покупателя имеет неверный формат.');
        }

        $this->name = $name;
        $this->email = $email;
    }
}
