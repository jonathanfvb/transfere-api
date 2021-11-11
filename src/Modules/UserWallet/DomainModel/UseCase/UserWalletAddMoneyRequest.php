<?php

namespace Api\Modules\UserWallet\DomainModel\UseCase;

class UserWalletAddMoneyRequest
{
    public string $userUuid;
    
    public float $value;
    
    public function __construct(
        string $userUuid,
        float $value
    )
    {
        $this->userUuid = $userUuid;
        $this->value = $value;
    }
}
