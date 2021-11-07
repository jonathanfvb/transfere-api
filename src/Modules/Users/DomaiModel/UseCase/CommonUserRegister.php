<?php

namespace Api\Modules\Users\DomaiModel\UseCase;

use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Users\DomaiModel\Exception\UserException;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Library\Contracts\HashPasswordInterface;
use Api\Library\ValueObject\Cpf;
use Api\Modules\UserWallet\DomainModel\UseCase\UserWalletCreate;

class CommonUserRegister
{
    private UserRepositoryInterface $UserRepository;
    
    private UuidGeneratorInterface $UuidGenerator;
    
    private HashPasswordInterface $HashPassword;
    
    private UserWalletCreate $UserWalletCreate;
    
    public function __construct(
        UserRepositoryInterface $UserRepository,
        UuidGeneratorInterface $UuidGenerator,
        HashPasswordInterface $HashPassword,
        UserWalletCreate $UserWalletCreate
    )
    {
        $this->UserRepository = $UserRepository;
        $this->UuidGenerator = $UuidGenerator;
        $this->HashPassword = $HashPassword;
        $this->UserWalletCreate = $UserWalletCreate;
    }
    
    public function execute(CommonUserRegisterRequest $Request)
    {
        $Cpf = new Cpf($Request->cpf);
        if ($this->UserRepository->findByCpfAndCnpjNull($Cpf)) {
            throw new UserException('Already exists a user with this cpf', 400);
        }
        
        if ($this->UserRepository->findByEmail($Request->email)) {
            throw new UserException('Already exists a user with this email', 400);
        }
        
        // cria o usuário do tipo common
        $User = new User();
        $User->uuid = $this->UuidGenerator->generateUuid();
        $User->full_name = $Request->full_name;
        $User->Cpf = $Cpf;
        $User->email = $Request->email;
        // Gera o hash do password
        $User->pass = $this->HashPassword->generateHashedPassword($Request->pass);
        
        // persiste o usuário
        $this->UserRepository->persist($User);
        
        // cria a carteira do usuário
        $this->UserWalletCreate->execute($User);
    }
}
