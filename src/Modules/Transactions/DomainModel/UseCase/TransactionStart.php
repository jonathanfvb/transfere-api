<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Library\Contracts\Persistence\TransactionManagerInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionStartDTO;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\Transaction;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;
use Api\Modules\Transactions\DomainModel\Model\UserTransaction;
use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Repository\UserTransactionRepositoryInterface;
use DateTimeImmutable;

class TransactionStart
{
    private TransactionRepositoryInterface $transactionRepository;
    
    private UserTransactionRepositoryInterface $userTransactionRepository;
    
    private UuidGeneratorInterface $uuidGenerator;
    
    private TransactionManagerInterface $transactionManager;
    
    private UserTransaction $transactionPayer;
    
    private UserTransaction $transactionPayee;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        UserTransactionRepositoryInterface $userTransactionRepository,
        UuidGeneratorInterface $uuidGenerator,
        TransactionManagerInterface $transactionManager
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->userTransactionRepository = $userTransactionRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->transactionManager = $transactionManager;
    }
    
    public function execute(TransactionStartrequest $request): TransactionStartDTO
    {
        $this->validate($request);
        
        $dbTransaction = $this->transactionManager->getTransaction();
        $this->transactionRepository->setTransaction($dbTransaction);
        $dbTransaction->begin();

        // registra a transação
        $transaction = new Transaction();
        $transaction->uuid = $this->uuidGenerator->generateUuid();
        $transaction->ammount = $request->value;
        $transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_PENDING;
        $transaction->statusNotification = TransactionEnum::NOTIFICATION_PENDING;
        $transaction->payer = $this->transactionPayer->user;
        $transaction->payee = $this->transactionPayee->user;
        $transaction->createdAt = new DateTimeImmutable();
        $this->transactionRepository->persist($transaction);
        
        // debita o saldo da carteira do pagador
        $this->transactionPayer->userWallet->balance -= $request->value;
        $this->transactionPayer->userWallet->updatedAt = new DateTimeImmutable();
        $userWalletRepository = $this->userTransactionRepository->getUserWalletRepository();
        $userWalletRepository->persist($this->transactionPayer->userWallet);
        
        $dbTransaction->commit();
        return new TransactionStartDTO($transaction->uuid);
    }
    
    private function validate(TransactionStartrequest $request)
    {
        if ($request->value < TransactionEnum::TRANSACTION_MIN_VALUE 
            || $request->value > TransactionEnum::TRANSACTION_MAX_VALUE
        ) {
            throw new TransactionException('Value not allowed', 400);
        }
        
        $userRepository = $this->userTransactionRepository->getUserRepository();
        $userWalletRepository = $this->userTransactionRepository->getUserWalletRepository();
        $payer = $userRepository->findByUuid($request->userPayerUuid);
        if (!$payer) {
            throw new TransactionException('Payer not found', 404);
        }
        $payerWallet = $userWalletRepository->findByUserUuid($request->userPayerUuid);
        if (!$payerWallet) {
            throw new TransactionException('Payer Wallet not found', 404);
        }
        
        if ($payer->isSeller()) {
            throw new TransactionException('Seller is not allowed to send money', 400);
        }
        
        if ($request->value > $payerWallet->balance) {
            throw new TransactionException('Balance unavailable', 400);
        }
        $this->transactionPayer = new UserTransaction($payer, $payerWallet);

        $payee = $userRepository->findByUuid($request->userPayeeUuid);
        if (!$payee) {
            throw new TransactionException('Payee not found', 404);
        }
        $payeeWallet = $userWalletRepository->findByUserUuid($request->userPayeeUuid);
        if (!$payeeWallet) {
            throw new TransactionException('Payee Wallet not found', 404);
        }
        $this->transactionPayee = new UserTransaction($payee, $payeeWallet);
    }
}
