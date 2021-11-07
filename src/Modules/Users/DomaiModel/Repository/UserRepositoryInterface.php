<?php

namespace Api\Modules\Users\DomaiModel\Repository;

use Api\Modules\Users\DomaiModel\Model\User;

interface UserRepositoryInterface
{
    public function persist($User): User;
    
    public function findByUuid(string $uuid): ?User;
    
    public function findByCpfAndCnpj(string $cpf, ?string $cnpj): ?User;
    
    public function findByCnpj(string $cnpj): ?User;
    
    public function findByEmail(string $cnpj): ?User;
}
