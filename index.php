<?php

require_once "vendor/autoload.php";

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

use Api\Modules\Users\DomaiModel\UseCase\CommonUserRegister;
use Api\Modules\Users\Persistence\Phalcon\UserRepository;
use Api\Library\Util\PhalconUuidGenerator;
use Api\Modules\Users\DomaiModel\UseCase\CommonUserRegisterRequest;
use Api\Library\Util\HashPassword;
use Api\Modules\Users\DomaiModel\UseCase\SellerUserRegister;
use Api\Modules\Users\DomaiModel\UseCase\SellerUserRegisterRequest;
use Api\Modules\UserWallet\Persistence\Phalcon\UserWalletRepository;
use Api\Modules\Users\DomaiModel\Model\User;
use Api\Library\ValueObject\Cpf;
use Api\Modules\UserWallet\DomainModel\Model\UserWallet;
use Api\Modules\Transactions\Persistence\Phalcon\TransactionRepository;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionStart;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionStartRequest;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionAuthorize;
use Api\Library\Service\ExternalAuthorizationService;
use Api\Library\Service\ExternalNotificationService;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionAuthorizeRequest;


$container = new FactoryDefault();
$container->set('db', function () {
    return new PdoMysql([
        'host'     => 'db',
        'username' => 'root',
        'password' => 'root',
        'dbname'   => 'transfere'
    ]);
});

$app = new Micro($container);

$app->get('/', function () use ($app) {
    $app->response->setStatusCode(200);
    $content = [
        'success' => true,
        'message' => 'Ok'
    ];
    $app->response->setJsonContent($content);
    return $app->response;
});

$app->post('/transactions', function () use ($app) {
    try {
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transação iniciada'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para iniciar a transação
        $ucTransactionStart = new TransactionStart(
            new TransactionRepository(), 
            new UserRepository(), 
            new UserWalletRepository(), 
            new PhalconUuidGenerator()
        );
        
        $Response = $ucTransactionStart->execute(
            new TransactionStartRequest(
                $payload->value, 
                $payload->payer_uuid, 
                $payload->payee_uuid
            )
        );
        
        $content['data'] = [
            'uuid' => $Response->transaction_uuid
        ];
        
        $app->response->setJsonContent($content);
        return $app->response;
    } catch (Exception $e) {        
        $code = $e->getCode() ? $e->getCode() : 500;
        $app->response->setStatusCode($code);
        $content = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        $app->response->setJsonContent($content);
        return $app->response;
    }
});

$app->put('/transactions', function () use ($app) {
    try {
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transação autorizada'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para autorizar a transação
        $ucTransactionAuthorize = new TransactionAuthorize(
            new TransactionRepository(), 
            new UserWalletRepository(), 
            new ExternalAuthorizationService(), 
            new ExternalNotificationService()
        );
        
        $Response = $ucTransactionAuthorize->execute(
            new TransactionAuthorizeRequest($payload->uuid)
        );
        
        $content['data'] = [
            'uuid' => $Response->transaction_uuid,
            'status' => $Response->status
        ];
        
        $app->response->setJsonContent($content);
        return $app->response;
    } catch (Exception $e) {
        $code = $e->getCode() ? $e->getCode() : 500;
        $app->response->setStatusCode($code);
        $content = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        $app->response->setJsonContent($content);
        return $app->response;
    }
});

$app->post('/wallet', function () use ($app) {
    try {
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Carteira registrada com sucesso'
        ];
        
        $user = $app->request->getJsonRawBody();
        
        $User = new User();
        $User->uuid = $user->user_uuid;
        $User->full_name = 'User 01';
        $User->Cpf = new Cpf('00000000191');
        $User->email = 'user1@test.com';
        $User->pass = '$2y$10$oIKIpDbF5aM.qxtzQrzL7uRxR/SZi0XYp9G3l2HCaQqk752f/h22O';
        
        $UserWallet = new UserWallet();
        $UserWallet->User = $User;
        $UserWallet->balance = 0;
        $UserWallet->UpdatedAt = new DateTimeImmutable();
        
        $UserWalletRepository = new UserWalletRepository();
        $UserWalletRepository->persist($UserWallet);
        
        $app->response->setJsonContent($content);
        return $app->response;
    } catch (Exception $e) {
        $code = $e->getCode() ? $e->getCode() : 500;
        $app->response->setStatusCode($code);
        $content = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        $app->response->setJsonContent($content);
        return $app->response;
    }
});

$app->post('/users', function () use ($app) {
    try {
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Usuário registrado com sucesso'
        ];
        
        $user = $app->request->getJsonRawBody();
        
        // caso de uso para registrar usuário comum
        $ucCommonUserReg = new CommonUserRegister(
            new UserRepository(), 
            new PhalconUuidGenerator(),
            new HashPassword()
        );
        
        $ucCommonUserReg->execute(
            new CommonUserRegisterRequest(
                $user->full_name, 
                $user->cpf, 
                $user->email, 
                $user->pass
            )
        );
        
        $app->response->setJsonContent($content);
        return $app->response;
    } catch (Exception $e) {
        $code = $e->getCode() ? $e->getCode() : 500;
        $app->response->setStatusCode($code);
        $content = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        $app->response->setJsonContent($content);
        return $app->response;
    }
});

$app->post('/sellers', function () use ($app) {
    try {
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Lojista registrado com sucesso'
        ];
        
        $user = $app->request->getJsonRawBody();
        
        // caso de uso para registrar usuário seller
        $ucSellerUserReg = new SellerUserRegister(
            new UserRepository(),
            new PhalconUuidGenerator(),
            new HashPassword()
        );
        
        $ucSellerUserReg->execute(
            new SellerUserRegisterRequest(
                $user->full_name,
                $user->cpf,
                $user->cnpj,
                $user->email,
                $user->pass
            )
        );
        
        $app->response->setJsonContent($content);
        return $app->response;
    } catch (Exception $e) {
        $code = $e->getCode() ? $e->getCode() : 500;
        $app->response->setStatusCode($code);
        $content = [
            'success' => false,
            'message' => $e->getMessage()
        ];
        $app->response->setJsonContent($content);
        return $app->response;
    }
});
    
$app->handle(
    $_SERVER["REQUEST_URI"]
);

