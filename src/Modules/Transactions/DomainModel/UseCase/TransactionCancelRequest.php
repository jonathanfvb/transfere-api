<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

class TransactionCancelRequest
{
    public string $transactionUuid;
    
    public function __construct(string $transactionUuid)
    {
        $this->transactionUuid = $transactionUuid;
    }
}
