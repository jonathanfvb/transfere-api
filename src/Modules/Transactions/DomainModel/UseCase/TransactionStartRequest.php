<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

class TransactionStartRequest
{
    public float $value;
    
    public string $user_payer_uuid;
    
    public string $user_payee_uuid;
    
    public function __construct(
        float $value,
        string $user_payer_uuid,
        string $user_payee_uuid
    )
    {
        $this->value = $value;
        $this->user_payer_uuid = $user_payer_uuid;
        $this->user_payee_uuid = $user_payee_uuid;
    }
}
