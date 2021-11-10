<?php

namespace Api\Modules\UserWallet\DomainModel\UseCase;

use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Library\Persistence\TransactionManagerInterface;

class UserWalletCreate
{
    private UserWalletRepositoryInterface $userWalletRepository;
    
    public function __construct(
        UserWalletRepositoryInterface $userWalletRepository
    )
    {
        $this->userWalletRepository = $userWalletRepository;
    }
    
    public function execute(
        User $User, 
        ?TransactionManagerInterface $transactionManager = null
    )
    {
        if (!empty($transactionManager)) {
            // seta a transaction no repository
            $this->userWalletRepository->setTransaction($transactionManager->getTransaction());
        }
        
        $userWallet = new UserWallet();
        $userWallet->User = $User;
        $userWallet->balance = 0;
        $userWallet->updatedAt = new \DateTimeImmutable();
        
        $this->userWalletRepository->persist($userWallet);
    }
}
