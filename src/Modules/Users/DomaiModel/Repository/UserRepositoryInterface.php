<?php

namespace Api\Modules\Users\DomaiModel\Repository;

use Api\Modules\Users\DomaiModel\Model\User;
use Api\Library\ValueObject\Cpf;
use Api\Library\ValueObject\Cnpj;

interface UserRepositoryInterface
{
    public function persist($User): User;
    
    public function findByUuid(string $uuid): ?User;
    
    public function findByCpfAndCnpjNull(Cpf $Cpf): ?User;
    
    public function findByCnpj(Cnpj $Cnpj): ?User;
    
    public function findByEmail(string $cnpj): ?User;
}
