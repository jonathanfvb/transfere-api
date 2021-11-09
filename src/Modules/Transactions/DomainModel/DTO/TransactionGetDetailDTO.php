<?php

namespace Api\Modules\Transactions\DomainModel\DTO;

use Api\Modules\Transactions\DomainModel\Model\Transaction;

class TransactionGetDetailDTO
{
    public string $uuid;
    
    public float $ammount;
    
    public string $status_authorization;
    
    public string $status_notification;
    
    public string $payer_uuid;
    
    public string $payee_uuid;
    
    public string $created_at;
    
    public string $updated_at;
    
    public function __construct(Transaction $Transaction)
    {
        $this->uuid = $Transaction->uuid;
        $this->ammount = $Transaction->ammount;
        $this->status_authorization = $Transaction->status_authorization;
        $this->status_notification = $Transaction->status_notification;
        $this->payer_uuid = $Transaction->Payer->uuid;
        $this->payee_uuid = $Transaction->Payee->uuid;
        $this->created_at = $Transaction->CreatedAt->format('Y-m-d H:i:s');
        $this->updated_at = $Transaction->UpdatedAt->format('Y-m-d H:i:s');
    }
    
    public function __toString()
    {
        return [
            'uuid' => $this->uuid,
            'ammount' => $this->ammount,
            'status_authorization' => $this->status_authorization,
            'status_notification' => $this->status_notification,
            'payer_uuid' => $this->payer_uuid,
            'payee_uuid' => $this->payee_uuid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
