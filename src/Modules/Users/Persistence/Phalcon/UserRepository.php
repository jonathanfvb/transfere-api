<?php

namespace Api\Modules\Users\Persistence\Phalcon;

use Api\Library\Persistence\Phalcon\PhalconAbstractRepository;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;

class UserRepository extends PhalconAbstractRepository implements UserRepositoryInterface
{
    public function __construct()
    {
        $this->entity = new UserModel();
    }
    
    /**
     * {@inheritDoc}
     * @see \Api\Library\Persistence\Phalcon\PhalconAbstractRepository::persist()
     */
    public function persist($User): User
    {
        return $this->parsePhalconModelToDomainModel(
            parent::persist($User)
        );
    }
    
    /**
     * {@inheritDoc}
     * @see \Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface::findByCpf()
     */
    public function findByCpf(string $cpf): ?User
    {
        $result = $this->entity->findFirst([
            'conditions' => 'cpf = :cpf:',
            'bind' => ['cpf' => $cpf]
        ]);
        
        if (!$result) {
            return null;
        } else {
            return $this->parsePhalconModelToDomainModel($result);
        }
    }
    
    /**
     * {@inheritDoc}
     * @see \Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface::findByCnpj()
     */
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
    
    /**
     * {@inheritDoc}
     * @see \Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface::findByEmail()
     */
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
        $User->id = $result->id;
        $User->name = $result->name;
        $User->type = $result->type;
        $User->cpf = $result->cpf;
        $User->cnpj = $result->cnpj;
        $User->email = $result->email;
        $User->pass = $result->pass;
        
        return $User;
    }
}
