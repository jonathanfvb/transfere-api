<?php

namespace Api\Container\Modules;

use Api\Container\AbstractContainer;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;
use Api\Modules\Users\DomaiModel\UseCase\CommonUserRegister;
use Api\Modules\Users\DomaiModel\UseCase\SellerUserRegister;

class UsersContainer extends AbstractContainer
{
    public function initialize()
    {
        // Repositories
        $this->diContainer->set(
            UserRepositoryInterface::class,
            \DI\create(UserRepository::class)
        );
        
        // Use Cases
        $this->diContainer->set('CommonUserRegister', \DI\autowire(CommonUserRegister::class));
        $this->diContainer->set('SellerUserRegister', \DI\autowire(SellerUserRegister::class));
    }
}
