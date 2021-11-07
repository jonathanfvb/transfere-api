<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

class TransactionAuthorizeRequest
{
    public string $transaction_uuid;
    
    public function __construct(string $transaction_uuid)
    {
        $this->transaction_uuid = $transaction_uuid;
    }
}
