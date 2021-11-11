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
    
    private UserTransactionRepositoryInterface $userTransactionRepostory;
    
    private UuidGeneratorInterface $uuidGenerator;
    
    private TransactionManagerInterface $transactionManager;
    
    private UserTransaction $transactionPayer;
    
    private UserTransaction $transactionPayee;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        UserTransactionRepositoryInterface $userTransactionRepostory,
        UuidGeneratorInterface $uuidGenerator,
        TransactionManagerInterface $transactionManager
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->userTransactionRepostory = $userTransactionRepostory;
        $this->uuidGenerator = $uuidGenerator;
        $this->transactionManager = $transactionManager;
    }
    
    public function execute(TransactionStartrequest $request): TransactionStartDTO
    {
        $this->validate($request);
        
        // instancia a transaction com o bd
        $dbTransaction = $this->transactionManager->getTransaction();
        
        // seta a transaction no repository
        $this->transactionRepository->setTransaction($dbTransaction);
        
        // inicia a transaction
        $dbTransaction->begin();
        
        // cria a transação com status pendente de autorização e notificação
        $transaction = new Transaction();
        $transaction->uuid = $this->uuidGenerator->generateUuid();
        $transaction->ammount = $request->value;
        $transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_PENDING;
        $transaction->statusNotification = TransactionEnum::NOTIFICATION_PENDING;
        $transaction->payer = $this->transactionPayer->user;
        $transaction->payee = $this->transactionPayee->user;
        $transaction->createdAt = new DateTimeImmutable();
        
        // registra a transação
        $this->transactionRepository->persist($transaction);
        
        // debita o saldo da carteira do pagador
        $this->transactionPayer->userWallet->balance -= $request->value;
        $this->transactionPayer->userWallet->updatedAt = new DateTimeImmutable();
        
        $userWalletRepository = $this->userTransactionRepostory->getUserWalletRepository();
        $userWalletRepository->persist($this->transactionPayer->userWallet);
        
        // realiza o commit da transaction
        $dbTransaction->commit();
        
        return new TransactionStartDTO($transaction->uuid);
    }
    
    private function validate(TransactionStartrequest $request)
    {
        if ($request->value < 0.01 || $request->value > 999999999999.99) {
            throw new TransactionException('Value not allowed', 400);
        }
        
        $userRepository = $this->userTransactionRepostory->getUserRepository();
        $userWalletRepository = $this->userTransactionRepostory->getUserWalletRepository();
        
        $payer = $userRepository->findByUuid($request->userPayerUuid);
        if (!$payer) {
            throw new TransactionException('Payer not found', 404);
        }
        $payerWallet = $userWalletRepository->findByUserUuid($request->userPayerUuid);
        if (!$payerWallet) {
            throw new TransactionException('Payer Wallet not found', 404);
        }
        
        // valida se o pagador não é um lojista
        if ($payer->isSeller()) {
            throw new TransactionException('Seller is not allowed to send money', 400);
        }
        
        // valida se há saldo na carteira do usuário
        if ($request->value > $payerWallet->balance) {
            throw new TransactionException('Balance unavailable', 400);
        }
        
        // instancia o usuário pagador
        $this->transactionPayer = new UserTransaction($payer, $payerWallet);
        
        $payee = $userRepository->findByUuid($request->userPayeeUuid);
        if (!$payee) {
            throw new TransactionException('Payee not found', 404);
        }
        $payeeWallet = $userWalletRepository->findByUserUuid($request->userPayeeUuid);
        if (!$payeeWallet) {
            throw new TransactionException('Payee Wallet not found', 404);
        }
        
        // instancia o usuário beneficiário
        $this->transactionPayee = new UserTransaction($payee, $payeeWallet);
    }
}
