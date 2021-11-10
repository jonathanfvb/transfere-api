<?php

namespace Api\Modules\Transactions\DomainModel\DTO;

use Api\Modules\Transactions\DomainModel\Model\Transaction;

class TransactionGetDetailDTO
{
    public string $uuid;
    
    public float $ammount;
    
    public string $statusAuthorization;
    
    public string $statusNotification;
    
    public string $payerUuid;
    
    public string $payeeUuid;
    
    public string $createdAt;
    
    public string $updatedAt;
    
    public function __construct(Transaction $transaction)
    {
        $this->uuid = $transaction->uuid;
        $this->ammount = $transaction->ammount;
        $this->statusAuthorization = $transaction->statusAuthorization;
        $this->statusNotification = $transaction->statusNotification;
        $this->payerUuid = $transaction->payer->uuid;
        $this->payeeUuid = $transaction->payee->uuid;
        $this->createdAt = $transaction->createdAt->format('Y-m-d H:i:s');
        $this->updatedAt = $transaction->updatedAt->format('Y-m-d H:i:s');
    }
}
