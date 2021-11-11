<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

class TransactionStartRequest
{
    public float $value;
    
    public string $userPayerUuid;
    
    public string $userPayeeUuid;
    
    public function __construct(
        float $value,
        string $userPayerUuid,
        string $userPayeeUuid
    )
    {
        $this->value = $value;
        $this->userPayerUuid = $userPayerUuid;
        $this->userPayeeUuid = $userPayeeUuid;
    }
}
