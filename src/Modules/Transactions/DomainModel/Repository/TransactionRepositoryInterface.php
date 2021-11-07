<?php

namespace Api\Modules\Transactions\DomainModel\Repository;

use Api\Modules\Transactions\DomainModel\Model\Transaction;

interface TransactionRepositoryInterface
{
    public function persist($Transaction): void;
    
    public function findByUuid(string $uuid): ?Transaction;
}
