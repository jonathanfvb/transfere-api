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
    
    public function persist($Transaction): void
    {
        parent::persist($Transaction);
    }
    
    public function findByUuid(string $uuid): ?Transaction
    {
        $result = $this->entity->findFirst([
            'conditions' => 'uuid = :uuid:',
            'bind' => ['uuid' => $uuid]
        ]);
        
        if (!$result) {
            return null;
        } else {
            return $this->parsePhalconModelToDomainModel($result);
        }
    }
    
    public static function parsePhalconModelToDomainModel($result): Transaction
    {
        $Transaction = new Transaction();
        $Transaction->uuid = $result->uuid;
        $Transaction->ammount = $result->ammount;
        $Transaction->status_authorization = $result->status_authorization;
        $Transaction->status_notification = $result->status_notification;
        
        $CreatedAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result->created_at);
        if (!$CreatedAt) {
            throw new \InvalidArgumentException(
                "The field created_at isn't in the format 'Y-m-d H:i:s'",
                400
            );
        }
        $Transaction->CreatedAt = $CreatedAt;
        
        if ($result->updated_at) {
            $UpdatedAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result->updated_at);
            if (!$UpdatedAt) {
                throw new \InvalidArgumentException(
                    "The field updated_at isn't in the format 'Y-m-d H:i:s'",
                    400
                );
            }
            $Transaction->UpdatedAt = $UpdatedAt;
        }
        
        $Transaction->Payer = UserRepository::parsePhalconModelToDomainModel($result->Payer);
        $Transaction->Payee = UserRepository::parsePhalconModelToDomainModel($result->Payee);
        
        return $Transaction;
    }
}
