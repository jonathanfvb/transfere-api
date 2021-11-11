<?php

namespace Api\Modules\Transactions\Persistence\Phalcon;

use Api\Library\Persistence\Phalcon\PhalconAbstractRepository;
use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Model\Transaction;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;

use \DateTimeImmutable;
use \InvalidArgumentException;

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
        
        try {
            $createdAt = new DateTimeImmutable($result->created_at);
            $transaction->createdAt = $createdAt;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "The field created_at isn't in the format 'Y-m-d H:i:s'",
                400
            );            
        }
        
        try {
            $updatedAt = new DateTimeImmutable($result->updated_at);
            $transaction->updatedAt = $updatedAt;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "The field updated_at isn't in the format 'Y-m-d H:i:s'",
                400
            );
        }

        $userRepository = new UserRepository();
        
        $transaction->payer = $userRepository->parsePhalconModelToDomainModel($result->Payer);
        $transaction->payee = $userRepository->parsePhalconModelToDomainModel($result->Payee);
        
        return $transaction;
    }
}
