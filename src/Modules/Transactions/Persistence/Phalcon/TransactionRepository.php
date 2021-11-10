<?php

namespace Api\Modules\Transactions\Persistence\Phalcon;

use Api\Library\Persistence\Phalcon\PhalconAbstractRepository;
use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Model\Transaction;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;

class TransactionRepository extends PhalconAbstractRepository implements TransactionRepositoryInterface
{
    public function __construct()
    {
        $this->entity = new TransactionModel();
    }
    
    public function persist($transaction): void
    {
        parent::persist($transaction);
    }
    
    public function findByUuid(string $uuid): ?Transaction
    {
        $result = $this->entity->findFirst([
            'conditions' => 'uuid = :uuid:',
            'bind' => ['uuid' => $uuid]
        ]);
        
        if ($result) {
            return $this->parsePhalconModelToDomainModel($result);
        }
        
        return null;
    }
    
    public static function parsePhalconModelToDomainModel($result): Transaction
    {
        $transaction = new Transaction();
        $transaction->uuid = $result->uuid;
        $transaction->ammount = $result->ammount;
        $transaction->statusAuthorization = $result->status_authorization;
        $transaction->statusNotification = $result->status_notification;
        
        $createdAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result->created_at);
        if (!$createdAt) {
            throw new \InvalidArgumentException(
                "The field created_at isn't in the format 'Y-m-d H:i:s'",
                400
            );
        }
        $transaction->createdAt = $createdAt;
        
        if ($result->updated_at) {
            $updatedAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result->updated_at);
            if (!$updatedAt) {
                throw new \InvalidArgumentException(
                    "The field updated_at isn't in the format 'Y-m-d H:i:s'",
                    400
                );
            }
            $transaction->updatedAt = $updatedAt;
        }
        
        $transaction->payer = UserRepository::parsePhalconModelToDomainModel($result->Payer);
        $transaction->payee = UserRepository::parsePhalconModelToDomainModel($result->Payee);
        
        return $transaction;
    }
}
