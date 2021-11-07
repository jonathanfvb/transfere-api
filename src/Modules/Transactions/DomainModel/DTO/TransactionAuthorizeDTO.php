<?php

namespace Api\Modules\Transactions\DomainModel\DTO;

class TransactionAuthorizeDTO
{
    public string $transaction_uuid;
    
    public string $status;
    
    public function __construct(
        string $transaction_uuid,
        string $status
    )
    {
        $this->transaction_uuid = $transaction_uuid;
        $this->status = $status;
    }
}
