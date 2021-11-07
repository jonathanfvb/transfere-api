<?php

namespace Api\Modules\Transactions\DomainModel\Model;

use Api\Library\Contracts\Arrayable;
use Api\Modules\Users\DomaiModel\Model\User;

class Transaction implements Arrayable
{
    public string $uuid;
    
    public float $ammount;
    
    public string $status;
    
    /** @var \DateTimeImmutable */
    public \DateTimeImmutable $CreatedAt;
    
    /** @var \DateTimeImmutable */
    public ?\DateTimeImmutable $UpdatedAt = null;
    
    /** @var User */
    public User $Payer;
    
    /** @var User */
    public User $Payee;
    
    
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'ammount' => $this->ammount,
            'status' => $this->status,
            'created_at' => $this->CreatedAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->UpdatedAt ? $this->UpdatedAt->format('Y-m-d H:i:s') : null,
            'user_payer_uuid' => $this->Payer->uuid,
            'user_payee_uuid' => $this->Payee->uuid
        ];
    }
}
