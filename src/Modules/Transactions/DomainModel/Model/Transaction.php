<?php

namespace Api\Modules\Transactions\DomainModel\Model;

use Api\Library\Contracts\Arrayable;
use Api\Modules\Users\DomaiModel\Model\User;

class Transaction implements Arrayable
{
    public string $uuid;
    
    public float $ammount;
    
    public string $statusAuthorization;
    
    public string $statusNotification;
    
    /** @var \DateTimeImmutable */
    public \DateTimeImmutable $createdAt;
    
    /** @var \DateTimeImmutable */
    public ?\DateTimeImmutable $updatedAt = null;
    
    /** @var User */
    public User $payer;
    
    /** @var User */
    public User $payee;
    
    
    public function isAuthorizationPending(): bool
    {
        return $this->statusAuthorization == TransactionEnum::AUTHORIZATION_PENDING;
    }
    
    public function isAuthorized(): bool
    {
        return $this->statusAuthorization == TransactionEnum::AUTHORIZATION_SUCCESS;
    }
    
    public function isNotificationPending(): bool
    {
        return $this->statusNotification == TransactionEnum::NOTIFICATION_PENDING;
    }
    
    public function isNotificationSent(): bool
    {
        return $this->statusNotification == TransactionEnum::NOTIFICATION_SENT;
    }
    
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'ammount' => $this->ammount,
            'status_authorization' => $this->statusAuthorization,
            'status_notification' => $this->statusNotification,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt ? $this->updatedAt->format('Y-m-d H:i:s') : null,
            'user_payer_uuid' => $this->payer->uuid,
            'user_payee_uuid' => $this->payee->uuid
        ];
    }
}
