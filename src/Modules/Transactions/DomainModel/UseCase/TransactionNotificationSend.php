<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Library\Contracts\Service\NotificationServiceInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;

class TransactionNotificationSend
{
    private TransactionRepositoryInterface $transactionRepository;
    
    private NotificationServiceInterface $notificationService;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        NotificationServiceInterface $notificationService
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->notificationService = $notificationService;
    }
    
    public function execute(TransactionNotificationSendrequest $request)
    {
        // busca a transação
        $transaction = $this->transactionRepository->findByUuid($request->transactionUuid);
        if (!$transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // valida se a notificação ainda não foi enviada
        if ($transaction->isNotificationSent()) {
            throw new TransactionException(
                'Notification has already been sent',
                400
            );
        }
        
        // valida se a transação está autorizada
        if (!$transaction->isAuthorized()) {
            throw new TransactionException(
                'Notification can not be send. Transaction is not authorized.',
                400
            );
        }
        
        // envia notificação para o beneficiário
        $isNotified = $this->notificationService->sendNotification($transaction->payee);
        if (!$isNotified) {
            throw new TransactionException('Fail to send notification', 400);
        }
        
        // altera o status da notificação para enviada
        $transaction->statusNotification = TransactionEnum::NOTIFICATION_SENT;
        $transaction->updatedAt = new \DateTimeImmutable();
        $this->transactionRepository->persist($transaction);
    }
}
