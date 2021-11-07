<?php

namespace Api\Modules\UserWallet\DomainModel\Repository;

use Api\Modules\UserWallet\DomainModel\Model\UserWallet;

interface UserWalletRepositoryInterface
{
    public function persist($UserWallet): void;
    
    public function findByUserUuid(string $user_uuid): ?UserWallet;
}
