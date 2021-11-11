<?php

namespace Api\Modules\Transactions\Persistence\Phalcon;

use Api\Modules\Transactions\DomainModel\Repository\UserTransactionRepositoryInterface;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;
use Api\Modules\UserWallet\Persistence\Phalcon\UserWalletRepository;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;

class UserTransactionRepository implements UserTransactionRepositoryInterface
{
    public function getUserRepository(): UserRepositoryInterface
    {
        return new UserRepository();
    }

    public function getUserWalletRepository(): UserWalletRepositoryInterface
    {
        return new UserWalletRepository();
    }
}

