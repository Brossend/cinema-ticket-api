<?php

declare(strict_types=1);

namespace App\Infrastructure\Transactions;

use App\Application\Contracts\TransactionManager;
use Closure;
use Illuminate\Support\Facades\DB;

final class LaravelTransactionManager implements TransactionManager
{
    public function run(Closure $callback): mixed
    {
        return DB::transaction($callback, 3);
    }
}
