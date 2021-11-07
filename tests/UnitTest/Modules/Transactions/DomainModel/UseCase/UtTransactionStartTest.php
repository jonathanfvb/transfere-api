<?php

namespace Tests\UnitTest\Modules\Transactions\DomainModel\UseCase;

use Tests\AbstractUnitTest;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionStart;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionStartRequest;
use Api\Modules\UserWallet\Persistence\Phalcon\UserWalletRepository;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Library\ValueObject\Cnpj;
use Api\Library\ValueObject\Cpf;

class UtTransactionStartTest extends AbstractUnitTest
{
    /** @var TransactionStart */
    private TransactionStart $TransactionStart;
    
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->TransactionStart = $this->diContainer->get('TransactionStart');
    }
    
    public function testNaoPermiteTransferirMenosQueUmCentavo()
    {
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Value not allowed');
        
        $this->TransactionStart->execute(
            new TransactionStartRequest(0.001,'payer_uuid','payee_uuid')
        );
    }
    
    public function testNaoPermiteTransferirUmTrilhaoOuMais()
    {
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Value not allowed');
        
        $this->TransactionStart->execute(
            new TransactionStartRequest(1000000000000,'payer_uuid','payee_uuid')
        );
    }
    
    public function testNaoPermiteTransacaoSePagadorNaoExiste()
    {
        // Cria o mock dos repositories
        $UserRepository = $this->createMock(UserRepository::class);
        $UserRepository->method('findByUuid')->willReturn(null);
        
        // Seta o UC no container injetando os mocks
        $this->diContainer->set(
            'TransactionStart',
            \DI\autowire(TransactionStart::class)
            ->constructorParameter('UserRepository', $UserRepository)
            );
        
        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Payer not found');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1,'payer_uuid','payee_uuid')
        );
    }
    
    public function testNaoPermiteTransacaoSePagadorNaoPossuiCarteira()
    {
        $Payer = new User();
        $Payer->Cpf = new Cpf('00000000000');
        
        // Cria o mock dos repositories
        $UserRepository = $this->createMock(UserRepository::class);
        $UserRepository->method('findByUuid')->willReturn($Payer);
        
        $UserWalletRepository = $this->createMock(UserWalletRepository::class);
        $UserWalletRepository->method('findByUserUuid')->willReturn(null);
        
        // Seta o UC no container injetando os mocks
        $this->diContainer->set(
            'TransactionStart',
            \DI\autowire(TransactionStart::class)
            ->constructorParameter('UserRepository', $UserRepository)
            ->constructorParameter('UserWalletRepository', $UserWalletRepository)
        );
        
        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Payer Wallet not found');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1,'payer_uuid','payee_uuid')
        );
    }
    
    public function testNaoPermiteLojistaEnviarDinheiro()
    {
        $SellerUser = new User();
        $SellerUser->Cnpj = new Cnpj('00000000000000');
        
        $SellerWallet = new UserWallet();
        
        // Cria o mock dos repositories
        $UserRepository = $this->createMock(UserRepository::class);
        $UserRepository->method('findByUuid')->willReturn($SellerUser);
        
        $UserWalletRepository = $this->createMock(UserWalletRepository::class);
        $UserWalletRepository->method('findByUserUuid')->willReturn($SellerWallet);
        
        // Seta o UC no container injetando os mocks
        $this->diContainer->set(
            'TransactionStart',
            \DI\autowire(TransactionStart::class)
            ->constructorParameter('UserRepository', $UserRepository)
            ->constructorParameter('UserWalletRepository', $UserWalletRepository)
        );
        
        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Seller is not allowed to send money');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1,'payer_uuid','payee_uuid')
        );
    }
    
    public function testNaoPermiteEnviarValorMaiorQueSaldoDaCarteira()
    {
        $Payer = new User();
        $Payer->Cpf = new Cpf('00000000000');
        
        $PayerWallet = new UserWallet();
        $PayerWallet->User = $Payer;
        $PayerWallet->balance = 100;
        
        // Cria o mock dos repositories
        $UserRepository = $this->createMock(UserRepository::class);
        $UserRepository->method('findByUuid')->willReturn($Payer);
        
        $UserWalletRepository = $this->createMock(UserWalletRepository::class);
        $UserWalletRepository->method('findByUserUuid')->willReturn($PayerWallet);
        
        // Seta o UC no container injetando os mocks
        $this->diContainer->set(
            'TransactionStart',
            \DI\autowire(TransactionStart::class)
            ->constructorParameter('UserRepository', $UserRepository)
            ->constructorParameter('UserWalletRepository', $UserWalletRepository)
        );
        
        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Balance unavailable');
        $this->TransactionStart->execute(
            new TransactionStartRequest(100.01,'payer_uuid','payee_uuid')
        );
    }
    
    public function testNaoPermiteTransacaoSeBeneficiarioNaoExiste()
    {
        $Payer = new User();
        $Payer->Cpf = new Cpf('00000000000');
        
        $PayerWallet = new UserWallet();
        $PayerWallet->User = $Payer;
        $PayerWallet->balance = 100;
        
        // Cria o mock dos repositories
        $UserRepository = $this->createMock(UserRepository::class);
        $UserRepository->method('findByUuid')->willReturnOnConsecutiveCalls($Payer,null);
        
        $UserWalletRepository = $this->createMock(UserWalletRepository::class);
        $UserWalletRepository->method('findByUserUuid')->willReturn($PayerWallet);
        
        // Seta o UC no container injetando os mocks
        $this->diContainer->set(
            'TransactionStart',
            \DI\autowire(TransactionStart::class)
            ->constructorParameter('UserRepository', $UserRepository)
            ->constructorParameter('UserWalletRepository', $UserWalletRepository)
        );
        
        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Payee not found');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1,'payer_uuid','payee_uuid')
        );
    }
    
    public function testNaoPermiteTransacaoSeBeneficiarioNaoPossuiCarteira()
    {
        $Payer = new User();
        $Payer->Cpf = new Cpf('00000000000');
        
        $PayerWallet = new UserWallet();
        $PayerWallet->User = $Payer;
        $PayerWallet->balance = 100;
        
        // Cria o mock dos repositories
        $UserRepository = $this->createMock(UserRepository::class);
        $UserRepository->method('findByUuid')->willReturnOnConsecutiveCalls($Payer, $Payer);
        
        $UserWalletRepository = $this->createMock(UserWalletRepository::class);
        $UserWalletRepository->method('findByUserUuid')->willReturnOnConsecutiveCalls($PayerWallet, null);
        
        // Seta o UC no container injetando os mocks
        $this->diContainer->set(
            'TransactionStart',
            \DI\autowire(TransactionStart::class)
            ->constructorParameter('UserRepository', $UserRepository)
            ->constructorParameter('UserWalletRepository', $UserWalletRepository)
        );
        
        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Payee Wallet not found');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1,'payer_uuid','payee_uuid')
        );
    }
}
