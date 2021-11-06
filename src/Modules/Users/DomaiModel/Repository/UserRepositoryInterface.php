<?php

namespace Api\Modules\Users\DomaiModel\Repository;

use Api\Modules\Users\DomaiModel\Model\User;

interface UserRepositoryInterface
{
    /**
     * Persiste o user
     * @param User $User
     * @return User
     */
    public function persist($User): User;
    
    /**
     * Busca um user pelo cpf
     *  
     * @param string $cpf
     * @return User|NULL
     */
    public function findByCpf(string $cpf): ?User;
    
    /**
     * Busca um user pelo cnpj
     *
     * @param string $cpf
     * @return User|NULL
     */
    public function findByCnpj(string $cnpj): ?User;
    
    /**
     * Busca um user pelo email
     *
     * @param string $cpf
     * @return User|NULL
     */
    public function findByEmail(string $cnpj): ?User;
}
