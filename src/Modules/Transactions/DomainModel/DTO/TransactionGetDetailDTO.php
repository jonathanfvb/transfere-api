<?php

namespace Api\Modules\Transactions\DomainModel\DTO;

use Api\Modules\Transactions\DomainModel\Model\Transaction;

class TransactionGetDetailDTO
{
    public string $transaction_uuid;
    
    public string $ammount;
    
    public string $status_authorization;
    
    public string $status_notification;
    
    public string $created_at;
    
    public string $updated_at;
    
    public function __construct(Transaction $Transaction)
    {
        $this->uuid = $Transaction->uuid;
        $this->ammount = $Transaction->ammount;
        $this->status_authorization = $Transaction->status_authorization;
        $this->status_notification = $Transaction->status_notification;
        $this->created_at = $Transaction->CreatedAt->format('Y-m-d H:i:s');
        $this->updated_at = $Transaction->UpdatedAt->format('Y-m-d H:i:s');
    }
}
