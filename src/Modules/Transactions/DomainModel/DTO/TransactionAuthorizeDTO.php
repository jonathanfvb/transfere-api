<?php

namespace Api\Modules\Transactions\DomainModel\DTO;

class TransactionAuthorizeDTO
{
    public string $transaction_uuid;
    
    public string $status_authorization;
    
    public string $status_notification;
    
    public function __construct(
        string $transaction_uuid,
        string $status_authorization,
        string $status_notification
    )
    {
        $this->transaction_uuid = $transaction_uuid;
        $this->status_authorization = $status_authorization;
        $this->status_notification = $status_notification;
    }
}
