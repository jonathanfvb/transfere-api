<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

class TransactionNotificationSendRequest
{
    public string $transactionUuid;
    
    public function __construct(string $transactionUuid)
    {
        $this->transactionUuid = $transactionUuid;
    }
}
