<?php

namespace Api\Modules\UserWallet\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\UserTransactionRepositoryInterface;
use Api\Modules\UserWallet\DomainModel\Exception\UserWalletException;
use Api\Modules\UserWallet\DomainModel\Model\UserWalletEnum;
use DateTimeImmutable;

class UserWalletAddMoney
{
    private UserTransactionRepositoryInterface $userTransactionRepository;
    
    public function __construct(
        UserTransactionRepositoryInterface $userTransactionRepository
    )
    {
        $this->userTransactionRepository = $userTransactionRepository;
    }
    
    public function execute(UserWalletAddMoneyRequest $request)
    {
        if ($request->value < UserWalletEnum::ADD_MONEY_MIN_VALUE
            || $request->value > UserWalletEnum::ADD_MONEY_MAX_VALUE
        ) {
            throw new UserWalletException('Value not allowed', 400);
        }
            
        $userRepository = $this->userTransactionRepository->getUserRepository();
        $userWalletRepository = $this->userTransactionRepository->getUserWalletRepository();
        
        $user = $userRepository->findByUuid($request->userUuid);
        if (!$user) {
            throw new UserWalletException('Uuer not found', 404);
        }
        $userWallet = $userWalletRepository->findByUserUuid($request->userUuid);
        if (!$userWallet) {
            throw new UserWalletException('User Wallet not found', 404);
        }
        
        $userWallet->balance += $request->value;
        if ($userWallet->balance > UserWalletEnum::MAX_BALANCE_LIMIT) {
            throw new UserWalletException('Value exceeds the max balance limit', 400);
        }
        
        $userWallet->updatedAt = new DateTimeImmutable();
        $userWalletRepository->persist($userWallet);
    }
}
