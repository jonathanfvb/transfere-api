<?php

namespace Tests\UnitTest\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\Persistence\Phalcon\UserTransactionRepository;
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
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findByUuid')->willReturn(null);
        $userTransactionRepository = $this->createMock(UserTransactionRepository::class);
        $userTransactionRepository->method('getUserRepository')->willReturn($userRepository);

        $this->diContainer->set('TransactionStart',
            \DI\autowire(TransactionStart::class)
                ->constructorParameter('userTransactionRepository', $userTransactionRepository)
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
        $payer = new User();
        $payer->cpf = new Cpf('00000000000');
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findByUuid')->willReturn($payer);

        $userWalletRepository = $this->createMock(UserWalletRepository::class);
        $userWalletRepository->method('findByUserUuid')->willReturn(null);

        $userTransactionRepository = $this->createMock(UserTransactionRepository::class);
        $userTransactionRepository->method('getUserRepository')->willReturn($userRepository);
        $userTransactionRepository->method('getUserWalletRepository')->willReturn($userWalletRepository);

        $this->diContainer->set('TransactionStart',
            \DI\autowire(TransactionStart::class)
                ->constructorParameter('userTransactionRepository', $userTransactionRepository)
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
        $sellerUser = new User();
        $sellerUser->cnpj = new Cnpj('00000000000000');
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findByUuid')->willReturn($sellerUser);

        $sellerWallet = new UserWallet();
        $userWalletRepository = $this->createMock(UserWalletRepository::class);
        $userWalletRepository->method('findByUserUuid')->willReturn($sellerWallet);

        $userTransactionRepository = $this->createMock(UserTransactionRepository::class);
        $userTransactionRepository->method('getUserRepository')->willReturn($userRepository);
        $userTransactionRepository->method('getUserWalletRepository')->willReturn($userWalletRepository);

        $this->diContainer->set('TransactionStart',
            \DI\autowire(TransactionStart::class)
                ->constructorParameter('userTransactionRepository', $userTransactionRepository)
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
        $payer = new User();
        $payer->cpf = new Cpf('00000000000');
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findByUuid')->willReturn($payer);

        $payerWallet = new UserWallet();
        $payerWallet->User = $payer;
        $payerWallet->balance = 100;
        $userWalletRepository = $this->createMock(UserWalletRepository::class);
        $userWalletRepository->method('findByUserUuid')->willReturn($payerWallet);

        $userTransactionRepository = $this->createMock(UserTransactionRepository::class);
        $userTransactionRepository->method('getUserRepository')->willReturn($userRepository);
        $userTransactionRepository->method('getUserWalletRepository')->willReturn($userWalletRepository);

        $this->diContainer->set('TransactionStart',
            \DI\autowire(TransactionStart::class)
                ->constructorParameter('userTransactionRepository', $userTransactionRepository)
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
        $payer = new User();
        $payer->cpf = new Cpf('00000000000');
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findByUuid')->willReturnOnConsecutiveCalls($payer,null);

        $payerWallet = new UserWallet();
        $payerWallet->User = $payer;
        $payerWallet->balance = 100;
        $userWalletRepository = $this->createMock(UserWalletRepository::class);
        $userWalletRepository->method('findByUserUuid')->willReturn($payerWallet);

        $userTransactionRepository = $this->createMock(UserTransactionRepository::class);
        $userTransactionRepository->method('getUserRepository')->willReturn($userRepository);
        $userTransactionRepository->method('getUserWalletRepository')->willReturn($userWalletRepository);

        $this->diContainer->set('TransactionStart',
            \DI\autowire(TransactionStart::class)
                ->constructorParameter('userTransactionRepository', $userTransactionRepository)
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
        $payer = new User();
        $payer->cpf = new Cpf('00000000000');
        $userRepository = $this->createMock(UserRepository::class);
        $userRepository->method('findByUuid')->willReturnOnConsecutiveCalls($payer, $payer);

        $payerWallet = new UserWallet();
        $payerWallet->User = $payer;
        $payerWallet->balance = 100;
        $userWalletRepository = $this->createMock(UserWalletRepository::class);
        $userWalletRepository->method('findByUserUuid')->willReturnOnConsecutiveCalls($payerWallet, null);

        $userTransactionRepository = $this->createMock(UserTransactionRepository::class);
        $userTransactionRepository->method('getUserRepository')->willReturn($userRepository);
        $userTransactionRepository->method('getUserWalletRepository')->willReturn($userWalletRepository);

        $this->diContainer->set('TransactionStart',
            \DI\autowire(TransactionStart::class)
                ->constructorParameter('userTransactionRepository', $userTransactionRepository)
        );
        $this->TransactionStart = $this->diContainer->get('TransactionStart');

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Payee Wallet not found');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1,'payer_uuid','payee_uuid')
        );
    }
}
