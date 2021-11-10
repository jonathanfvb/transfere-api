<?php

namespace Api\Modules\Transactions\DomainModel\DTO;

class TransactionAuthorizeDTO
{
    public string $transactionUuid;
    
    public string $statusAuthorization;
    
    public string $statusNotification;
    
    public function __construct(
        string $transactionUuid,
        string $statusAuthorization,
        string $statusNotification
    )
    {
        $this->transactionUuid = $transactionUuid;
        $this->statusAuthorization = $statusAuthorization;
        $this->statusNotification = $statusNotification;
    }
}
