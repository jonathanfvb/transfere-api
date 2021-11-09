<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Library\Contracts\Service\NotificationServiceInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;

class TransactionNotificationSend
{
    private TransactionRepositoryInterface $TransactionRepository;
    
    private NotificationServiceInterface $NotificationService;
    
    public function __construct(
        TransactionRepositoryInterface $TransactionRepository,
        NotificationServiceInterface $NotificationService
    )
    {
        $this->TransactionRepository = $TransactionRepository;
        $this->NotificationService = $NotificationService;
    }
    
    public function execute(TransactionNotificationSendRequest $Request)
    {
        // busca a transação
        $Transaction = $this->TransactionRepository->findByUuid($Request->transaction_uuid);
        if (!$Transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // valida se a notificação ainda não foi enviada
        if ($Transaction->isNotificationSent()) {
            throw new TransactionException(
                'Notification has already been sent',
                400
            );
        }
        
        // valida se a transação está autorizada
        if (!$Transaction->isAuthorized()) {
            throw new TransactionException(
                'Notification can not be send. Transaction is not authorized.',
                400
            );
        }
        
        // envia notificação para o beneficiário
        $is_notified = $this->NotificationService->sendNotification($Transaction->Payee);
        if (!$is_notified) {
            throw new TransactionException('Fail to send notification', 400);
        }
        
        // altera o status da notificação para enviada
        $Transaction->status_notification = TransactionEnum::NOTIFICATION_SENT;
        $Transaction->UpdatedAt = new \DateTimeImmutable();
        $this->TransactionRepository->persist($Transaction);
    }
}
