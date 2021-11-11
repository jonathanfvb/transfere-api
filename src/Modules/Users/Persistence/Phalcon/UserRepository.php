<?php

namespace Api\Modules\Users\Persistence\Phalcon;

use Api\Library\Persistence\Phalcon\PhalconAbstractRepository;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Users\DomaiModel\Model\UserEnum;
use Api\Library\ValueObject\Cpf;
use Api\Library\ValueObject\Cnpj;

class UserRepository extends PhalconAbstractRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        $this->entity = new UserModel();
    }
    
    public function persist($user): User
    {
        return $this->parsePhalconModelToDomainModel(
            parent::persist($user)
        );
    }
    
    public function findByUuid(string $uuid): ?User
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
    
    public function findByCpfAndCnpjNull(Cpf $cpf): ?User
    {
        $result = $this->entity->findFirst([
            'conditions' => 'cpf = :cpf: AND cnpj IS NULL',
            'bind' => ['cpf' => $cpf->getCpfUnmasked()]
        ]);
        
        if ($result) {
            return $this->parsePhalconModelToDomainModel($result);
        }
        
        return null;
    }
    
    public function findByCnpj(Cnpj $cnpj): ?User
    {
        $result = $this->entity->findFirst([
            'conditions' => 'cnpj = :cnpj:',
            'bind' => ['cnpj' => $cnpj->getCnpjUnmasked()]
        ]);
        
        if ($result) {
            return $this->parsePhalconModelToDomainModel($result);
        }
        
        return null;
    }
    
    public function findByEmail(string $email): ?User
    {
        $result = $this->entity->findFirst([
            'conditions' => 'email = :email:',
            'bind' => ['email' => $email]
        ]);
        
        if ($result) {
            return $this->parsePhalconModelToDomainModel($result);
        }
        
        return null;
    }
    
    public static function parsePhalconModelToDomainModel($result): User
    {
        $user = new User();
        $user->uuid = $result->uuid;
        $user->fullName = $result->full_name;
        $user->type = (
            empty($result->cnpj) 
            ? UserEnum::TYPE_COMMON 
            : UserEnum::TYPE_SELLER
        );
        $user->cpf = new Cpf($result->cpf);
        $user->cnpj = $result->cnpj ? new Cnpj($result->cnpj) : null;
        $user->email = $result->email;
        $user->pass = $result->pass;
        
        return $user;
    }
}
