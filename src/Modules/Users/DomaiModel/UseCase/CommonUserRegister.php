<?php

namespace Api\Modules\Users\DomaiModel\UseCase;

use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Users\DomaiModel\Exception\UserException;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Library\Contracts\UuidGeneratorInterface;

class CommonUserRegister
{
    private UserRepositoryInterface $UserRepository;
    
    private UuidGeneratorInterface $UuidGenerator;
    
    public function __construct(
        UserRepositoryInterface $UserRepository,
        UuidGeneratorInterface $UuidGenerator
    )
    {
        $this->UserRepository = $UserRepository;
        $this->UuidGenerator = $UuidGenerator;
    }
    
    public function execute(CommonUserRegisterRequest $Request)
    {
        if ($this->UserRepository->findByCpfAndCnpj($Request->cpf, null)) {
            throw new UserException('Already exists a user with this cpf', 400);
        }
        
        if ($this->UserRepository->findByEmail($Request->email)) {
            throw new UserException('Already exists a user with this email', 400);
        }
        
        $User = new User();
        $User->uuid = $this->UuidGenerator->generateUuid();
        $User->full_name = $Request->full_name;
        $User->cpf = $Request->cpf;
        $User->email = $Request->email;
        // TODO - crypt password
        $User->pass = $Request->pass;
        
        $this->UserRepository->persist($User);
    }
}
