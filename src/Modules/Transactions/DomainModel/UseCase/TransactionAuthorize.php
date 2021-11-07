<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Library\Contracts\Service\AuthorizeServiceInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Library\Contracts\Service\NotificationServiceInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionAuthorizeDTO;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;

class TransactionAuthorize
{
    private TransactionRepositoryInterface $TransactionRepository;
    
    private UserWalletRepositoryInterface $UserWalletRepository;
    
    private AuthorizeServiceInterface $AuthorizeService;
    
    private NotificationServiceInterface $NotificationService;
    
    public function __construct(
        TransactionRepositoryInterface $TransactionRepository,
        UserWalletRepositoryInterface $UserWalletRepository,
        AuthorizeServiceInterface $AuthorizeService,
        NotificationServiceInterface $NotificationService
    )
    {
        $this->TransactionRepository = $TransactionRepository;
        $this->UserWalletRepository = $UserWalletRepository;
        $this->AuthorizeService = $AuthorizeService;
        $this->NotificationService = $NotificationService;
    }
    
    public function execute(TransactionAuthorizeRequest $Request): TransactionAuthorizeDTO
    {
        $Transaction = $this->TransactionRepository->findByUuid($Request->transaction_uuid);
        if (!$Transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // busca a carteira do pagador
        $PayerWallet = $this->UserWalletRepository->findByUserUuid($Transaction->Payer->uuid);
        if (!$PayerWallet) {
            throw new TransactionException('Payer Wallet not found', 404);
        }
        
        // busca a carteira do beneficiário
        $PayeeWallet = $this->UserWalletRepository->findByUserUuid($Transaction->Payee->uuid);
        if (!$PayeeWallet) {
            throw new TransactionException('Payee Wallet not found', 404);
        }
        
        // consulta o serviço de autorização
        $is_authorized = $this->AuthorizeService->authorize(
            $Transaction->Payer, 
            $Transaction->ammount
        );
        if (!$is_authorized) {
            // estorna o valor da carteira do pagador
            $PayerWallet->balance = $PayerWallet->balance + $Transaction->ammount;
            $this->UserWalletRepository->persist($PayerWallet);
            
            // altera o status da transação para não autorizada
            $Transaction->status = TransactionEnum::STATUS_FINISHED_UNAUTHORIZED;
            $Transaction->UpdatedAt = new \DateTimeImmutable();
            $this->TransactionRepository->persist($Transaction);
            
            throw new TransactionException('Transaction unauthorized', 400);
        }
        
        // credita o valor para o beneficiário
        $PayeeWallet->balance = $PayeeWallet->balance + $Transaction->ammount;
        $this->UserWalletRepository->persist($PayeeWallet);
        
        // altera o status da transação notificação pendente
        $Transaction->status = TransactionEnum::STATUS_PENDING_NOTIFICATION;
        $Transaction->UpdatedAt = new \DateTimeImmutable();
        $this->TransactionRepository->persist($Transaction);
        
        // envia notificação para o beneficiário
        $is_notified = $this->NotificationService->sendNotification($Transaction->Payee);
        if ($is_notified) {
            // altera o status da transação para autorizada
            $Transaction->status = TransactionEnum::STATUS_FINISHED_AUTHORIZED;
            $Transaction->UpdatedAt = new \DateTimeImmutable();
            $this->TransactionRepository->persist($Transaction);
        }
        
        return new TransactionAuthorizeDTO(
            $Transaction->uuid, 
            $Transaction->status
        );
    }
}
