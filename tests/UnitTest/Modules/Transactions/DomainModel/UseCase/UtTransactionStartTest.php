<?php

namespace Tests\UnitTest\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\UserTransactionRepositoryInterface;
use Api\Modules\Transactions\Persistence\Phalcon\UserTransactionRepository;
use Api\Modules\Users\DomaiModel\Repository\UserRepositoryInterface;
use Api\Modules\UserWallet\DomainModel\Repository\UserWalletRepositoryInterface;
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

    /** @var UserTransactionRepositoryInterface  */
    private UserTransactionRepositoryInterface $mockUserTransactionRepository;

    /** @var UserRepositoryInterface  */
    private UserRepositoryInterface $mockUserRepository;

    /** @var UserWalletRepositoryInterface  */
    private UserWalletRepositoryInterface $mockUserWalletRepository;


    protected function setUp(): void
    {
        parent::setUp();

        $this->mockUserRepository = $this->createMock(UserRepository::class);
        $this->diContainer->set(UserRepositoryInterface::class, $this->mockUserRepository);

        $this->mockUserWalletRepository = $this->createMock(UserWalletRepository::class);
        $this->diContainer->set(UserWalletRepositoryInterface::class, $this->mockUserWalletRepository);

        $this->mockUserTransactionRepository = $this->createMock(UserTransactionRepository::class);
        $this->diContainer->set(UserTransactionRepositoryInterface::class, $this->mockUserTransactionRepository);
    }

    private function setContainerInjectionParameter(string $name, $paramName, $paramValue)
    {
        $this->diContainer->set(
            $name,
            \DI\autowire(TransactionStart::class)->constructorParameter($paramName, $paramValue)
        );
    }

    public function testNaoPermiteTransferirMenosQueUmCentavo()
    {
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Value not allowed');

        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        $this->TransactionStart->execute(
            new TransactionStartRequest(0.001,'payer_uuid','payee_uuid')
        );
    }

    public function testNaoPermiteTransferirUmTrilhaoOuMais()
    {
        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Value not allowed');

        $this->TransactionStart = $this->diContainer->get('TransactionStart');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1000000000000,'payer_uuid','payee_uuid')
        );
    }

    public function testNaoPermiteTransacaoSePagadorNaoExiste()
    {
        $this->mockUserRepository->method('findByUuid')->willReturn(null);
        $this->mockUserTransactionRepository->method('getUserRepository')->willReturn($this->mockUserRepository);
        $this->setContainerInjectionParameter(
            'TransactionStart',
            'userTransactionRepository',
            $this->mockUserTransactionRepository
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
        $this->mockUserRepository->method('findByUuid')->willReturn(new User());
        $this->mockUserWalletRepository->method('findByUserUuid')->willReturn(null);
        $this->mockUserTransactionRepository->method('getUserRepository')->willReturn($this->mockUserRepository);
        $this->mockUserTransactionRepository->method('getUserWalletRepository')->willReturn($this->mockUserWalletRepository);
        $this->setContainerInjectionParameter(
            'TransactionStart',
            'userTransactionRepository',
            $this->mockUserTransactionRepository
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
        $this->mockUserRepository->method('findByUuid')->willReturn($sellerUser);
        $this->mockUserWalletRepository->method('findByUserUuid')->willReturn(new UserWallet());
        $this->mockUserTransactionRepository->method('getUserRepository')->willReturn($this->mockUserRepository);
        $this->mockUserTransactionRepository->method('getUserWalletRepository')->willReturn($this->mockUserWalletRepository);
        $this->setContainerInjectionParameter(
            'TransactionStart',
            'userTransactionRepository',
            $this->mockUserTransactionRepository
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
        $this->mockUserRepository->method('findByUuid')->willReturn($payer);

        $payerWallet = new UserWallet();
        $payerWallet->User = $payer;
        $payerWallet->balance = 100;
        $this->mockUserWalletRepository->method('findByUserUuid')->willReturn($payerWallet);
        $this->mockUserTransactionRepository->method('getUserRepository')->willReturn($this->mockUserRepository);
        $this->mockUserTransactionRepository->method('getUserWalletRepository')->willReturn($this->mockUserWalletRepository);
        $this->setContainerInjectionParameter(
            'TransactionStart',
            'userTransactionRepository',
            $this->mockUserTransactionRepository
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
        $this->mockUserRepository->method('findByUuid')->willReturnOnConsecutiveCalls($payer, null);

        $payerWallet = new UserWallet();
        $payerWallet->User = $payer;
        $payerWallet->balance = 100;
        $this->mockUserWalletRepository->method('findByUserUuid')->willReturn($payerWallet);
        $this->mockUserTransactionRepository->method('getUserRepository')->willReturn($this->mockUserRepository);
        $this->mockUserTransactionRepository->method('getUserWalletRepository')->willReturn($this->mockUserWalletRepository);
        $this->setContainerInjectionParameter(
            'TransactionStart',
            'userTransactionRepository',
            $this->mockUserTransactionRepository
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
        $this->mockUserRepository->method('findByUuid')->willReturnOnConsecutiveCalls($payer, $payer);

        $payerWallet = new UserWallet();
        $payerWallet->User = $payer;
        $payerWallet->balance = 100;
        $this->mockUserWalletRepository->method('findByUserUuid')->willReturnOnConsecutiveCalls($payerWallet, null);
        $this->mockUserTransactionRepository->method('getUserRepository')->willReturn($this->mockUserRepository);
        $this->mockUserTransactionRepository->method('getUserWalletRepository')->willReturn($this->mockUserWalletRepository);
        $this->setContainerInjectionParameter(
            'TransactionStart',
            'userTransactionRepository',
            $this->mockUserTransactionRepository
        );
        $this->TransactionStart = $this->diContainer->get('TransactionStart');

        $this->expectException(TransactionException::class);
        $this->expectExceptionMessage('Payee Wallet not found');
        $this->TransactionStart->execute(
            new TransactionStartRequest(1,'payer_uuid','payee_uuid')
        );
    }
}
