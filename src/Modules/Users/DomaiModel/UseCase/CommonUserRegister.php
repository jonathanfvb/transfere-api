<?php

namespace Api\Modules\Users\DomaiModel\UseCase;

use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Users\DomaiModel\Exception\UserException;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Library\Contracts\HashPasswordInterface;
use Api\Library\ValueObject\Cpf;
use Api\Modules\UserWallet\DomainModel\UseCase\UserWalletCreate;
use Api\Library\Persistence\TransactionManagerInterface;

class CommonUserRegister
{
    private UserRepositoryInterface $UserRepository;
    
    private UuidGeneratorInterface $UuidGenerator;
    
    private HashPasswordInterface $HashPassword;
    
    private UserWalletCreate $UserWalletCreate;
    
    private TransactionManagerInterface $TransactionManager;
    
    public function __construct(
        UserRepositoryInterface $UserRepository,
        UuidGeneratorInterface $UuidGenerator,
        HashPasswordInterface $HashPassword,
        UserWalletCreate $UserWalletCreate,
        TransactionManagerInterface $TransactionManager
    )
    {
        $this->UserRepository = $UserRepository;
        $this->UuidGenerator = $UuidGenerator;
        $this->HashPassword = $HashPassword;
        $this->UserWalletCreate = $UserWalletCreate;
        $this->TransactionManager = $TransactionManager;
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
        
        try {
            // instancia a transaction com o bd
            $dbTransaction = $this->TransactionManager->getTransaction();
            
            // seta a transaction no repository
            $this->UserRepository->setTransaction($dbTransaction);
            
            // inicia a transaction
            $dbTransaction->begin();
                        
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
            $this->UserWalletCreate->execute($User, $this->TransactionManager);
            
            // realiza o commit da transaction
            $dbTransaction->commit();
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
