<?php

namespace Api\Modules\UserWallet\DomainModel\Repository;

use Api\Modules\UserWallet\DomainModel\Model\UserWallet;

interface UserWalletRepositoryInterface
{
    public function persist($userWallet): void;
    
    public function findByUserUuid(string $userUuid): ?UserWallet;
}
