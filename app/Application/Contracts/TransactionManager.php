<?php

declare(strict_types=1);

namespace App\Application\Contracts;

use Closure;

interface TransactionManager
{
    public function run(Closure $callback): mixed;
}
