<?php

namespace Api\Modules\UserWallet\Persistence\Phalcon;

use Api\Library\Persistence\Phalcon\PhalconAbstractRepository;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;

use \DateTimeImmutable;
use \InvalidArgumentException;

class UserWalletRepository extends PhalconAbstractRepository implements UserWalletRepositoryInterface
{
    public function __construct()
    {
        $this->entity = new UserWalletModel();
    }
    
    public function persist($userWallet): void
    {
        parent::persist($userWallet);
    }
    
    public function findByUserUuid(string $userUuid): ?UserWallet
    {
        $result = $this->entity->findFirst([
            'conditions' => 'user_uuid = :user_uuid:',
            'bind' => ['user_uuid' => $userUuid]
        ]);
        
        if ($result) {
            return $this->parsePhalconModelToDomainModel($result);
        }
        
        return null;
    }

    public static function parsePhalconModelToDomainModel($result): UserWallet
    {
        $userWallet = new UserWallet();
        $userWallet->User = UserRepository::parsePhalconModelToDomainModel($result->User);
        $userWallet->balance = $result->balance;
        
        try {
            $updatedAt = new DateTimeImmutable($result->updated_at);
            $userWallet->updatedAt = $updatedAt;
        } catch (\Exception $e) {
            throw new InvalidArgumentException(
                "The field updated_at isn't in the format 'Y-m-d H:i:s'",
                400
            );
        }
        
        return $userWallet;
    }   
}
