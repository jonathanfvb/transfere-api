<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Library\Contracts\Persistence\TransactionManagerInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;
use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Repository\UserTransactionRepositoryInterface;
use DateTimeImmutable;

class TransactionCancel
{
    private TransactionRepositoryInterface $transactionRepository;
    
    private UserTransactionRepositoryInterface $userTransactionRepostory;
    
    private TransactionManagerInterface $transactionManager;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        UserTransactionRepositoryInterface $userTransactionRepostory,
        TransactionManagerInterface $transactionManager
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->userTransactionRepostory = $userTransactionRepostory;
        $this->transactionManager = $transactionManager;
    }
    
    public function execute(TransactionCancelrequest $request)
    {
        // busca a transação
        $transaction = $this->transactionRepository->findByUuid($request->transactionUuid);
        if (!$transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // valida se está pendente de autorização
        if (!$transaction->isAuthorizationPending()) {
            throw new TransactionException(
                "Transaction can not be cancelled. Status: {$transaction->statusAuthorization}.",
                400
            );
        }
        
        // instancia a transaction com o bd
        $dbTransaction = $this->transactionManager->getTransaction();
        
        // seta a transaction no repository
        $this->transactionRepository->setTransaction($dbTransaction);
        
        // inicia a transaction
        $dbTransaction->begin();
        
        // cancela a transação
        $transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_CANCELLED;
        $transaction->updatedAt = new DateTimeImmutable();
        $this->transactionRepository->persist($transaction);
        
        // credita o saldo na carteira do pagador
        $userWalletRepository = $this->userTransactionRepostory->getUserWalletRepository();
        $payerWallet = $userWalletRepository->findByUserUuid($transaction->payer->uuid);
        if (!$payerWallet) {
            throw new TransactionException('Payer Wallet not found', 404);
        }
        
        $payerWallet->balance += $transaction->ammount;
        $payerWallet->updatedAt = new DateTimeImmutable();
        
        $userWalletRepository->persist($payerWallet);
        
        // realiza o commit da transaction
        $dbTransaction->commit();        
    }
}
