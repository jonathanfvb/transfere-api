<?php

namespace Api\Modules\Transactions\DomainModel\Model;

use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;

class UserTransaction
{
    public User $user;
    
    public UserWallet $userWallet;
    
    public function __construct(
        User $user,
        UserWallet $userWallet
    )
    {
        $this->user = $user;
        $this->userWallet = $userWallet;
    }
}
