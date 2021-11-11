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
use Api\Modules\Users\DomaiModel\DTO\CommonUserRegisterDTO;

class CommonUserRegister
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
    
    public function execute(CommonUserRegisterrequest $request): CommonUserRegisterDTO
    {
        $cpf = new Cpf($request->cpf);
        if ($this->userRepository->findByCpfAndCnpjNull($cpf)) {
            throw new UserException('Already exists a user with this cpf', 400);
        }
        
        if ($this->userRepository->findByEmail($request->email)) {
            throw new UserException('Already exists a user with this email', 400);
        }
        
        try {
            // instancia a transaction com o bd
            $dbTransaction = $this->transactionManager->getTransaction();
            
            // seta a transaction no repository
            $this->userRepository->setTransaction($dbTransaction);
            
            // inicia a transaction
            $dbTransaction->begin();
                        
            // cria o usuário do tipo common
            $user = new User();
            $user->uuid = $this->uuidGenerator->generateUuid();
            $user->fullName = $request->fullName;
            $user->cpf = $cpf;
            $user->email = $request->email;
            // Gera o hash do password
            $user->pass = $this->hashPassword->generateHashedPassword($request->pass);
            
            // persiste o usuário
            $this->userRepository->persist($user);
            
            // cria a carteira do usuário
            $this->userWalletCreate->execute($user, $this->transactionManager);
            
            // realiza o commit da transaction
            $dbTransaction->commit();
            
            return new CommonUserRegisterDTO($user);
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
