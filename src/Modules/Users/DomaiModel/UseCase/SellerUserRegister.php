<?php

namespace Api\Modules\Users\DomaiModel\UseCase;

use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Library\Contracts\HashPasswordInterface;
use Api\Modules\Users\DomaiModel\Exception\UserException;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Library\ValueObject\Cpf;
use Api\Library\ValueObject\Cnpj;

class SellerUserRegister
{
    private UserRepositoryInterface $UserRepository;
    
    private UuidGeneratorInterface $UuidGenerator;
    
    private HashPasswordInterface $HashPassword;
    
    public function __construct(
        UserRepositoryInterface $UserRepository,
        UuidGeneratorInterface $UuidGenerator,
        HashPasswordInterface $HashPassword
    )
    {
        $this->UserRepository = $UserRepository;
        $this->UuidGenerator = $UuidGenerator;
        $this->HashPassword = $HashPassword;
    }
    
    public function execute(SellerUserRegisterRequest $Request)
    {
        $Cnpj = new Cnpj($Request->cnpj);
        if ($this->UserRepository->findByCnpj($Cnpj)) {
            throw new UserException('Already exists a user with this cnpj', 400);
        }
        
        if ($this->UserRepository->findByEmail($Request->email)) {
            throw new UserException('Already exists a user with this email', 400);
        }
        
        $User = new User();
        $User->uuid = $this->UuidGenerator->generateUuid();
        $User->full_name = $Request->full_name;
        $User->Cpf = new Cpf($Request->cpf);
        $User->Cnpj = $Cnpj;
        $User->email = $Request->email;
        // Gera o hash do password
        $User->pass = $this->HashPassword->generateHashedPassword($Request->pass);
        
        $this->UserRepository->persist($User);
    }
}
