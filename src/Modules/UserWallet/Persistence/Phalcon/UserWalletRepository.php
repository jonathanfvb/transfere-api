<?php

namespace Api\Modules\UserWallet\Persistence\Phalcon;

use Api\Library\Persistence\Phalcon\PhalconAbstractRepository;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;

class UserWalletRepository extends PhalconAbstractRepository implements UserWalletRepositoryInterface
{
    public function __construct()
    {
        $this->entity = new UserWalletModel();
    }
    
    public function persist($UserWallet): void
    {
        parent::persist($UserWallet);
    }
    
    public function findByUserUuid(string $user_uuid): ?UserWallet
    {
        $result = $this->entity->findFirst([
            'conditions' => 'user_uuid = :user_uuid:',
            'bind' => ['user_uuid' => $user_uuid]
        ]);
        
        if (!$result) {
            return null;
        } else {
            return $this->parsePhalconModelToDomainModel($result);
        }
    }

    public static function parsePhalconModelToDomainModel($result): UserWallet
    {
        $UserWallet = new UserWallet();
        $UserWallet->User = UserRepository::parsePhalconModelToDomainModel($result->User);
        $UserWallet->balance = $result->balance;
        
        $UpdatedAt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $result->updated_at);
        if (!$UpdatedAt) {
            throw new \InvalidArgumentException(
                "The field updated_at isn't in the format 'Y-m-d H:i:s'", 
                400
            );
        }
        $UserWallet->UpdatedAt = $UpdatedAt;
        
        return $UserWallet;
    }   
}

