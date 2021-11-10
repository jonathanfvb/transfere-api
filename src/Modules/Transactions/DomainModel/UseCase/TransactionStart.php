<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Model\Transaction;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionStartDTO;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;
use Api\Modules\Users\DomaiModel\Model\UserEnum;
use Api\Library\Persistence\TransactionManagerInterface;
use \DateTimeImmutable;

class TransactionStart
{
    private TransactionRepositoryInterface $transactionRepository;
    
    private UserRepositoryInterface $userRepository;
    
    private UserWalletRepositoryInterface $userWalletRepository;
    
    private UuidGeneratorInterface $uuidGenerator;

    private TransactionManagerInterface $transactionManager;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        UserRepositoryInterface $userRepository,
        UserWalletRepositoryInterface $userWalletRepository,
        UuidGeneratorInterface $uuidGenerator,
        TransactionManagerInterface $transactionManager
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->userRepository = $userRepository;
        $this->userWalletRepository = $userWalletRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->transactionManager = $transactionManager;
    }
    
    public function execute(TransactionStartrequest $request): TransactionStartDTO
    {
        if ($request->value < 0.01 || $request->value > 999999999999.99) {
            throw new TransactionException('Value not allowed', 400);
        }
        
        $payer = $this->userRepository->findByUuid($request->userPayerUuid);
        if (!$payer) {
            throw new TransactionException('payer not found', 404);
        }
        $payerWallet = $this->userWalletRepository->findByUserUuid($request->userPayerUuid);
        if (!$payerWallet) {
            throw new TransactionException('payer Wallet not found', 404);
        }
        
        // valida se o pagador não é um lojista
        if ($payer->getType() == UserEnum::TYPE_SELLER) {
            throw new TransactionException('Seller is not allowed to send money', 400);
        }
        
        // valida se há saldo na carteira do usuário
        if ($request->value > $payerWallet->balance) {
            throw new TransactionException('Balance unavailable', 400);
        }
        
        $payee = $this->userRepository->findByUuid($request->userPayeeUuid);
        if (!$payee) {
            throw new TransactionException('payee not found', 404);
        }
        $payeeWallet = $this->userWalletRepository->findByUserUuid($request->userPayeeUuid);
        if (!$payeeWallet) {
            throw new TransactionException('payee Wallet not found', 404);
        }
        

        try {
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
            $transaction->payer = $payer;
            $transaction->payee = $payee;
            $transaction->createdAt = new DateTimeImmutable();
            
            // registra a transação
            $this->transactionRepository->persist($transaction);
            
            // debita o saldo da carteira do pagador
            $payerWallet->balance = $payerWallet->balance - $request->value;
            $payerWallet->updatedAt = new DateTimeImmutable();
            $this->userWalletRepository->persist($payerWallet);

            // realiza o commit da transaction
            $dbTransaction->commit();
            
            return new TransactionStartDTO($transaction->uuid);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}
