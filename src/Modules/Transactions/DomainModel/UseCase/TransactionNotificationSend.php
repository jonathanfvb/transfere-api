<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Library\Contracts\Service\NotificationServiceInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;
use \DateTimeImmutable;

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
        $transaction = $this->transactionRepository->findByUuid($request->transactionUuid);
        if (!$transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        if ($transaction->isNotificationSent()) {
            throw new TransactionException(
                'Notification has already been sent',
                400
            );
        }
        
        if (!$transaction->isAuthorized()) {
            throw new TransactionException(
                'Notification can not be send. Transaction is not authorized.',
                400
            );
        }
        
        $isNotified = $this->notificationService->sendNotification($transaction->payee);
        if (!$isNotified) {
            throw new TransactionException('Fail to send notification', 400);
        }
        
        $transaction->statusNotification = TransactionEnum::NOTIFICATION_SENT;
        $transaction->updatedAt = new DateTimeImmutable();
        $this->transactionRepository->persist($transaction);
    }
}
