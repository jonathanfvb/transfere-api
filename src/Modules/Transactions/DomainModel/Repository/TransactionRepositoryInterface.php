<?php

namespace Api\Modules\Transactions\DomainModel\Repository;

interface TransactionRepositoryInterface
{
    public function persist($Transaction): void;
}
