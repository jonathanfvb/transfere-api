<?php

namespace Api\Modules\UserWallet\DomainModel\UseCase;

use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Library\Persistence\TransactionManagerInterface;

class UserWalletCreate
{
    private UserWalletRepositoryInterface $UserWalletRepository;
    
    public function __construct(
        UserWalletRepositoryInterface $UserWalletRepository
    )
    {
        $this->UserWalletRepository = $UserWalletRepository;
    }
    
    public function execute(
        User $User, 
        ?TransactionManagerInterface $TransactionManager = null
    )
    {
        if (!empty($TransactionManager)) {
            // seta a transaction no repository
            $this->UserWalletRepository->setTransaction($TransactionManager->getTransaction());
        }
        
        $UserWallet = new UserWallet();
        $UserWallet->User = $User;
        $UserWallet->balance = 0;
        $UserWallet->UpdatedAt = new \DateTimeImmutable();
        
        $this->UserWalletRepository->persist($UserWallet);
    }
}
