<?php

namespace Api\Modules\Users\DomaiModel\DTO;

use Api\Modules\Users\DomaiModel\Model\User;

class CommonUserRegisterDTO
{
    public string $uuid;
    
    public function __construct(User $user)
    {
        $this->uuid = $user->uuid;
    }
}
