<?php

namespace Api\Modules\Transactions\DomainModel\Model;

use Api\Library\Contracts\Arrayable;
use Api\Modules\Users\DomaiModel\Model\User;

class Transaction implements Arrayable
{
    public string $uuid;
    
    public float $ammount;
    
    public bool $success;
    
    /** @var \DateTimeImmutable */
    public \DateTimeImmutable $CreatedAt;
    
    /** @var User */
    public User $Payer;
    
    /** @var User */
    public User $Payee;
    
    
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'ammount' => $this->ammount,
            'success' => $this->success,
            'created_at' => $this->CreatedAt->format('Y-m-d H:i:s'),
            'user_payer_uuid' => $this->Payer->uuid,
            'user_payee_uuid' => $this->Payee->uuid
        ];
    }
}
