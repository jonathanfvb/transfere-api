<?php

namespace Api\Modules\Transactions\DomainModel\Repository;

use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;

interface UserTransactionRepositoryInterface
{
    public function getUserRepository(): UserRepositoryInterface;
    
    public function getUserWalletRepository(): UserWalletRepositoryInterface;
}
