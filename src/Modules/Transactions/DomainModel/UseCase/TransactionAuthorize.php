<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Library\Contracts\Service\AuthorizeServiceInterface;
use Api\Library\Contracts\Persistence\TransactionManagerInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionAuthorizeDTO;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\Transaction;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;
use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use DateTimeImmutable;

class TransactionAuthorize
{
    private TransactionRepositoryInterface $transactionRepository;
    
    private UserWalletRepositoryInterface $userWalletRepository;
    
    private AuthorizeServiceInterface $authorizeService;
    
    private TransactionManagerInterface $transactionManager;
    
    private Transaction $transaction;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        UserWalletRepositoryInterface $userWalletRepository,
        AuthorizeServiceInterface $authorizeService,
        TransactionManagerInterface $transactionManager
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->userWalletRepository = $userWalletRepository;
        $this->authorizeService = $authorizeService;
        $this->transactionManager = $transactionManager;
    }
    
    public function execute(TransactionAuthorizerequest $request): TransactionAuthorizeDTO
    {
        // busca a transação
        $this->transaction = $this->transactionRepository->findByUuid($request->transactionUuid);
        if (!$this->transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // valida se está pendente de autorização
        if (!$this->transaction->isAuthorizationPending()) {
            throw new TransactionException(
                "Transaction can not be authorized. Status: {$this->transaction->statusAuthorization}.", 
                400
            );
        }
        
        // busca a carteira do pagador
        $payerWallet = $this->userWalletRepository->findByUserUuid($this->transaction->payer->uuid);
        if (!$payerWallet) {
            throw new TransactionException('payer Wallet not found', 404);
        }
        
        // busca a carteira do beneficiário
        $payeeWallet = $this->userWalletRepository->findByUserUuid($this->transaction->payee->uuid);
        if (!$payeeWallet) {
            throw new TransactionException('payee Wallet not found', 404);
        }
        
        // realiza a autorização em um serviço externo
        $this->authorizeByExternalService($payerWallet);
        
        // instancia a transaction com o bd
        $dbTransaction = $this->transactionManager->getTransaction();
        
        // seta a transaction no repository
        $this->userWalletRepository->setTransaction($dbTransaction);
        
        // inicia a transaction
        $dbTransaction->begin();
        
        // credita o valor para o beneficiário
        $payeeWallet->balance += $this->transaction->ammount;
        $payeeWallet->updatedAt = new DateTimeImmutable();
        $this->userWalletRepository->persist($payeeWallet);
        
        // altera o status da transação para autorizada
        $this->transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_SUCCESS;
        $this->transaction->updatedAt = new DateTimeImmutable();
        $this->transactionRepository->persist($this->transaction);
        
        // realiza o commit da transaction
        $dbTransaction->commit();
        
        return new TransactionAuthorizeDTO(
            $this->transaction->uuid, 
            $this->transaction->statusAuthorization,
            $this->transaction->statusNotification
        );
    }
    
    private function authorizeByExternalService(UserWallet $payerWallet)
    {
        $isAuthorized = $this->authorizeService->authorize(
            $this->transaction->payer,
            $this->transaction->ammount
        );
        if (!$isAuthorized) {
            // instancia a transaction com o bd
            $dbTransaction = $this->transactionManager->getTransaction();
            
            // seta a transaction no repository
            $this->userWalletRepository->setTransaction($dbTransaction);
            
            // inicia a transaction
            $dbTransaction->begin();
            
            // credita o valor na carteira do pagador
            $payerWallet->balance += $this->transaction->ammount;
            $payerWallet->updatedAt = new DateTimeImmutable();
            $this->userWalletRepository->persist($payerWallet);
            
            // altera o status da transação para não autorizada
            $this->transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_FAILED;
            $this->transaction->updatedAt = new DateTimeImmutable();
            $this->transactionRepository->persist($this->transaction);
            
            // realiza o commit da transaction
            $dbTransaction->commit();
            
            throw new TransactionException('Transaction unauthorized', 400);
        }
    }
}
