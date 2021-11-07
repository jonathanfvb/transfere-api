<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Model\Transaction;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionStartDTO;

class TransactionStart
{
    private TransactionRepositoryInterface $TransactionRepository;
    
    private UserRepositoryInterface $UserRepository;
    
    private UserWalletRepositoryInterface $UserWalletRepository;
    
    private UuidGeneratorInterface $UuidGenerator;
    
    public function __construct(
        TransactionRepositoryInterface $TransactionRepository,
        UserRepositoryInterface $UserRepository,
        UserWalletRepositoryInterface $UserWalletRepository,
        UuidGeneratorInterface $UuidGenerator
    )
    {
        $this->TransactionRepository = $TransactionRepository;
        $this->UserRepository = $UserRepository;
        $this->UserWalletRepository = $UserWalletRepository;
        $this->UuidGenerator = $UuidGenerator;
    }
    
    public function execute(TransactionStartRequest $Request): TransactionStartDTO
    {
        if ($Request->value < 0.01 || $Request->value > 999999999999) {
            throw new TransactionException('Value not allowed', 400);
        }
        
        $Payer = $this->UserRepository->findByUuid($Request->user_payer_uuid);
        if (!$Payer) {
            throw new TransactionException('Payer not found', 404);
        }
        $PayerWallet = $this->UserWalletRepository->findByUserUuid($Request->user_payer_uuid);
        if (!$PayerWallet) {
            throw new TransactionException('Payer Wallet not found', 404);
        }
        
        // valida se há saldo na carteira do usuário
        if ($Request->value > $PayerWallet->balance) {
            throw new TransactionException('Balance unavailable', 400);
        }
        
        $Payee = $this->UserRepository->findByUuid($Request->user_payee_uuid);
        if (!$Payee) {
            throw new TransactionException('Payee not found', 404);
        }
        $PayeeWallet = $this->UserWalletRepository->findByUserUuid($Request->user_payee_uuid);
        if (!$PayeeWallet) {
            throw new TransactionException('Payee Wallet not found', 404);
        }
        
        // cria a transação com status pendente de autorização
        $Transaction = new Transaction();
        $Transaction->uuid = $this->UuidGenerator->generateUuid();
        $Transaction->ammount = $Request->value;
        $Transaction->Payer = $Payer;
        $Transaction->Payee = $Payee;
        $Transaction->CreatedAt = new \DateTimeImmutable();
        $Transaction->success = 0;
        
        // registra a transação
        $this->TransactionRepository->persist($Transaction);
        
        // debita o saldo da carteira do pagador
        $PayerWallet->balance = $PayerWallet->balance - $Request->value;
        $this->UserWalletRepository->persist($PayerWallet);
        
        return new TransactionStartDTO($Transaction->uuid);
    }
}
