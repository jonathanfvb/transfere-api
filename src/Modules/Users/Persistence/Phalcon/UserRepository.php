<?php

namespace Api\Modules\Users\Persistence\Phalcon;

use Api\Library\Persistence\Phalcon\PhalconAbstractRepository;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Users\DomaiModel\Model\UserEnum;
use Api\Library\ValueObject\Cpf;

class UserRepository extends PhalconAbstractRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        $this->entity = new UserModel();
    }
    
    public function persist($User): User
    {
        return $this->parsePhalconModelToDomainModel(
            parent::persist($User)
        );
    }
    
    public function findByUuid(string $uuid): ?User
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
    
    public function findByCpfAndCnpj(string $cpf, ?string $cnpj): ?User
    {
        if (empty($cnpj)) {
            $result = $this->entity->findFirst([
                'conditions' => 'cpf = :cpf: AND cnpj IS NULL',
                'bind' => ['cpf' => $cpf]
            ]);
        } else {
            $result = $this->entity->findFirst([
                'conditions' => 'cpf = :cpf: AND cnpj = :cnpj:',
                'bind' => [
                    'cpf' => $cpf,
                    'cnpj' => $cnpj
                ]
            ]);
        }
        
        if (!$result) {
            return null;
        } else {
            return $this->parsePhalconModelToDomainModel($result);
        }
    }
    
    public function findByCnpj(string $cnpj): ?User
    {
        $result = $this->entity->findFirst([
            'conditions' => 'cnpj = :cnpj:',
            'bind' => ['cnpj' => $cnpj]
        ]);
        
        if (!$result) {
            return null;
        } else {
            return $this->parsePhalconModelToDomainModel($result);
        }
    }
    
    public function findByEmail(string $email): ?User
    {
        $result = $this->entity->findFirst([
            'conditions' => 'email = :email:',
            'bind' => ['email' => $email]
        ]);
        
        if (!$result) {
            return null;
        } else {
            return $this->parsePhalconModelToDomainModel($result);
        }
    }
    
    public static function parsePhalconModelToDomainModel($result): User
    {
        $User = new User();
        $User->uuid = $result->uuid;
        $User->full_name = $result->full_name;
        $User->type = (
            empty($result->cnpj) 
            ? UserEnum::TYPE_COMMON 
            : UserEnum::TYPE_SELLER
        );
        $User->Cpf = new Cpf($result->cpf);
        $User->cnpj = $result->cnpj;
        $User->email = $result->email;
        $User->pass = $result->pass;
        
        return $User;
    }
}
