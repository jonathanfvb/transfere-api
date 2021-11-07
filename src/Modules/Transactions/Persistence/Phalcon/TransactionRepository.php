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
    
    public static function parsePhalconModelToDomainModel($result): Transaction
    {
        $Transaction = new Transaction();
        $Transaction->uuid = $result->uuid;
        $Transaction->ammount = $result->ammount;
        $Transaction->success = $result->success;
        
        $CreatedAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result->created_at);
        if (!$CreatedAt) {
            throw new \InvalidArgumentException(
                "The field created_at isn't in the format 'Y-m-d H:i:s'",
                400
            );
        }
        $Transaction->CreatedAt = $CreatedAt;
        
        $Transaction->Payer = UserRepository::parsePhalconModelToDomainModel($result->Payer);
        $Transaction->Payee = UserRepository::parsePhalconModelToDomainModel($result->Payee);
        
        return $Transaction;
    }
}
