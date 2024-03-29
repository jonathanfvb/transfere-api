<?php

namespace Api\Modules\UserWallet\DomainModel\Model;

use Api\Library\Contracts\Arrayable;
use Api\Modules\Users\DomaiModel\Model\User;

class UserWallet implements Arrayable
{
    /** @var User */
    public User $User;
    
    public float $balance = 0;
    
    /** @var \DateTimeImmutable */
    public \DateTimeImmutable $updatedAt;
    
    
    public function toArray(): array
    {
        return [
            'user_uuid' => $this->User->uuid,
            'balance' => $this->balance,
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
