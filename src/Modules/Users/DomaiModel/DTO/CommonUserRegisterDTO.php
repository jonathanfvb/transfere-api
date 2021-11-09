<?php

namespace Api\Modules\Users\DomainModel\DTO;

use Api\Modules\Users\DomaiModel\Model\User;

class CommonUserRegisterDTO
{
    public string $uuid;
    
    public function __construct(User $User)
    {
        $this->uuid = $User->uuid;
    }
}
