<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Library\Contracts\Service\AuthorizeServiceInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Library\Contracts\Service\NotificationServiceInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionAuthorizeDTO;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;
use Api\Library\Persistence\TransactionManagerInterface;
use \DateTimeImmutable;

class TransactionAuthorize
{
    private TransactionRepositoryInterface $transactionRepository;
    
    private UserWalletRepositoryInterface $userWalletRepository;
    
    private AuthorizeServiceInterface $authorizeService;
    
    private NotificationServiceInterface $notificationService;
    
    private TransactionManagerInterface $transactionManager;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        UserWalletRepositoryInterface $userWalletRepository,
        AuthorizeServiceInterface $authorizeService,
        NotificationServiceInterface $notificationService,
        TransactionManagerInterface $transactionManager
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->userWalletRepository = $userWalletRepository;
        $this->authorizeService = $authorizeService;
        $this->notificationService = $notificationService;
        $this->transactionManager = $transactionManager;
    }
    
    public function execute(TransactionAuthorizerequest $request): TransactionAuthorizeDTO
    {
        // busca a transação
        $transaction = $this->transactionRepository->findByUuid($request->transactionUuid);
        if (!$transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // valida se está pendente de autorização
        if (!$transaction->isAuthorizationPending()) {
            throw new TransactionException(
                "Transaction can not be authorized. Status: {$transaction->statusAuthorization}.", 
                400
            );
        }
        
        // busca a carteira do pagador
        $payerWallet = $this->userWalletRepository->findByUserUuid($transaction->payer->uuid);
        if (!$payerWallet) {
            throw new TransactionException('payer Wallet not found', 404);
        }
        
        // busca a carteira do beneficiário
        $payeeWallet = $this->userWalletRepository->findByUserUuid($transaction->payee->uuid);
        if (!$payeeWallet) {
            throw new TransactionException('payee Wallet not found', 404);
        }
        
        // consulta o serviço de autorização
        $isAuthorized = $this->authorizeService->authorize(
            $transaction->payer, 
            $transaction->ammount
        );
        if (!$isAuthorized) {
            try {
                // instancia a transaction com o bd
                $dbTransaction = $this->transactionManager->getTransaction();
                
                // seta a transaction no repository
                $this->userWalletRepository->setTransaction($dbTransaction);
                
                // inicia a transaction
                $dbTransaction->begin();
                
                // estorna o valor da carteira do pagador
                $payerWallet->balance = $payerWallet->balance + $transaction->ammount;
                $payerWallet->updatedAt = new DateTimeImmutable();
                $this->userWalletRepository->persist($payerWallet);
                
                // altera o status da transação para não autorizada
                $transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_FAILED;
                $transaction->updatedAt = new DateTimeImmutable();
                $this->transactionRepository->persist($transaction);
                
                // realiza o commit da transaction
                $dbTransaction->commit();
                
                throw new TransactionException('Transaction unauthorized', 400);
            } catch (\Exception $e) {
                throw $e;
            }
        }
        
        // TRANSAÇÃO AUTORIZADA
        // -----------------------------------
        try {
            // instancia a transaction com o bd
            $dbTransaction = $this->transactionManager->getTransaction();
            
            // seta a transaction no repository
            $this->userWalletRepository->setTransaction($dbTransaction);
            
            // inicia a transaction
            $dbTransaction->begin();
            
            // credita o valor para o beneficiário
            $payeeWallet->balance = $payeeWallet->balance + $transaction->ammount;
            $payeeWallet->updatedAt = new DateTimeImmutable();
            $this->userWalletRepository->persist($payeeWallet);
            
            // altera o status da transação para autorizada
            $transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_SUCCESS;
            $transaction->updatedAt = new DateTimeImmutable();
            $this->transactionRepository->persist($transaction);
            
            // realiza o commit da transaction
            $dbTransaction->commit();
        } catch (\Exception $e) {
            throw $e;
        }
        
        
        // ENVIA NOTIFICAÇÃO
        // ------------------------------------
        // envia notificação para o beneficiário
        $isNotified = $this->notificationService->sendNotification($transaction->payee);
        if ($isNotified) {
            // altera o status da notificação para enviada
            $transaction->statusNotification = TransactionEnum::NOTIFICATION_SENT;
            $transaction->updatedAt = new DateTimeImmutable();
            $this->transactionRepository->persist($transaction);
        }
        
        return new TransactionAuthorizeDTO(
            $transaction->uuid, 
            $transaction->statusAuthorization,
            $transaction->statusNotification
        );
    }
}
