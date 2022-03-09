<?php

namespace Api\Modules\UserWallet\DomainModel\UseCase;

use Api\Library\Contracts\Persistence\TransactionManagerInterface;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Users\DomaiModel\Model\User;
use DateTimeImmutable;

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
        User $user, 
        ?TransactionManagerInterface $transactionManager = null
    )
    {
        if (!empty($transactionManager)) {
            $this->userWalletRepository->setTransaction($transactionManager->getTransaction());
        }
        
        $userWallet = new UserWallet();
        $userWallet->User = $user;
        $userWallet->balance = 0;
        $userWallet->updatedAt = new DateTimeImmutable();
        $this->userWalletRepository->persist($userWallet);
    }
}
