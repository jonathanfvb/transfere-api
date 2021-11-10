<?php

namespace Api\Modules\Transactions\DomainModel\DTO;

class TransactionStartDTO
{
    public string $transactionUuid;
    
    public function __construct(string $transactionUuid)
    {
        $this->transactionUuid = $transactionUuid;
    }
}
