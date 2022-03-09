<?php

namespace Api\Modules\Users\DomaiModel\UseCase;

use Api\Library\Contracts\HashPasswordInterface;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Library\Contracts\Persistence\TransactionManagerInterface;
use Api\Library\ValueObject\Cpf;
use Api\Modules\UserWallet\DomainModel\UseCase\UserWalletCreate;
use Api\Modules\Users\DomaiModel\DTO\CommonUserRegisterDTO;
use Api\Modules\Users\DomaiModel\Exception\UserException;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;

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
        
        $dbTransaction = $this->transactionManager->getTransaction();
        $this->userRepository->setTransaction($dbTransaction);
        $dbTransaction->begin();
                    
        $user = new User();
        $user->uuid = $this->uuidGenerator->generateUuid();
        $user->fullName = $request->fullName;
        $user->cpf = $cpf;
        $user->email = $request->email;
        $user->pass = $this->hashPassword->generateHashedPassword($request->pass);
        $this->userRepository->persist($user);
        $this->userWalletCreate->execute($user, $this->transactionManager);
        
        $dbTransaction->commit();
        return new CommonUserRegisterDTO($user);
    }
}
