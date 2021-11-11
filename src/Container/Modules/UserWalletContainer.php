<?php

namespace Api\Container\Modules;

use Api\Container\AbstractContainer;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\UserWallet\Persistence\Phalcon\UserWalletRepository;
use Api\Modules\UserWallet\DomainModel\UseCase\UserWalletAddMoney;

class UserWalletContainer extends AbstractContainer
{
    public function initialize()
    {
        // Repositories
        $this->diContainer->set(
            UserWalletRepositoryInterface::class,
            \DI\create(UserWalletRepository::class)
        );
        
        // Use Cases
        $this->diContainer->set('UserWalletAddMoney', \DI\autowire(UserWalletAddMoney::class));
    }
}
