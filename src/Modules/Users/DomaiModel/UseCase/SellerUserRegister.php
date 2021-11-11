<?php

namespace Api\Modules\Users\DomaiModel\UseCase;

use Api\Library\Contracts\HashPasswordInterface;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Library\Contracts\Persistence\TransactionManagerInterface;
use Api\Library\ValueObject\Cnpj;
use Api\Library\ValueObject\Cpf;
use Api\Modules\UserWallet\DomainModel\UseCase\UserWalletCreate;
use Api\Modules\Users\DomaiModel\DTO\SellerUserRegisterDTO;
use Api\Modules\Users\DomaiModel\Exception\UserException;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;

class SellerUserRegister
{
    private UserRepositoryInterface $userRepository;
    
    private UuidGeneratorInterface $uuidGenerator;
    
    private HashPasswordInterface $hashPassword;
    
    private UserWalletCreate $userWalletCreate;
    
    private TransactionManagerInterface $transactionManager;
    
    public function __construct(
        UserRepositoryInterface $userRepository,
        UuidGeneratorInterface $uuidGenerator,
        HashPasswordInterface $hashPassword,
        UserWalletCreate $userWalletCreate,
        TransactionManagerInterface $transactionManager
    )
    {
        $this->userRepository = $userRepository;
        $this->uuidGenerator = $uuidGenerator;
        $this->hashPassword = $hashPassword;
        $this->userWalletCreate = $userWalletCreate;
        $this->transactionManager = $transactionManager;
    }
    
    public function execute(SellerUserRegisterrequest $request): SellerUserRegisterDTO
    {
        $cnpj = new Cnpj($request->cnpj);
        if ($this->userRepository->findByCnpj($cnpj)) {
            throw new UserException('Already exists a user with this cnpj', 400);
        }
        
        if ($this->userRepository->findByEmail($request->email)) {
            throw new UserException('Already exists a user with this email', 400);
        }
        
        // instancia a transaction com o bd
        $dbTransaction = $this->transactionManager->getTransaction();
        
        // seta a transaction no repository
        $this->userRepository->setTransaction($dbTransaction);
        
        // inicia a transaction
        $dbTransaction->begin();
        
        // cria o usuário do tipo seller
        $user = new User();
        $user->uuid = $this->uuidGenerator->generateUuid();
        $user->fullName = $request->fullName;
        $user->cpf = new Cpf($request->cpf);
        $user->cnpj = $cnpj;
        $user->email = $request->email;
        // Gera o hash do password
        $user->pass = $this->hashPassword->generateHashedPassword($request->pass);
        
        // persiste o usuário
        $this->userRepository->persist($user);
        
        // cria a carteira do usuário
        $this->userWalletCreate->execute($user, $this->transactionManager);
        
        // realiza o commit da transaction
        $dbTransaction->commit();
        
        return new SellerUserRegisterDTO($user);
    }
}
