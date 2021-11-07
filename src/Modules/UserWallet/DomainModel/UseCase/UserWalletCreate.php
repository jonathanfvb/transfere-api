<?php

namespace Api\Modules\UserWallet\DomainModel\UseCase;

use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;

class UserWalletCreate
{
    private UserWalletRepositoryInterface $UserWalletRepository;
    
    public function __construct(
        UserWalletRepositoryInterface $UserWalletRepository
    )
    {
        $this->UserWalletRepository = $UserWalletRepository;
    }
    
    public function execute(User $User)
    {
        $UserWallet = new UserWallet();
        $UserWallet->User = $User;
        $UserWallet->balance = 0;
        $UserWallet->UpdatedAt = new \DateTimeImmutable();
        
        $this->UserWalletRepository->persist($UserWallet);
    }
}
