<?php

namespace Api\Modules\Users\DomaiModel\Repository;

use Api\Modules\Users\DomaiModel\Model\User;
use Api\Library\ValueObject\Cpf;
use Api\Library\ValueObject\Cnpj;

interface UserRepositoryInterface
{
    public function persist($user): User;
    
    public function findByUuid(string $uuid): ?User;
    
    public function findByCpfAndCnpjNull(Cpf $cpf): ?User;
    
    public function findByCnpj(Cnpj $cnpj): ?User;
    
    public function findByEmail(string $cnpj): ?User;
}
