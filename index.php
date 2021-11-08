<?php

require_once "vendor/autoload.php";

use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;

use Api\Container\BuilderContainer;
use Api\Modules\Users\DomaiModel\UseCase\CommonUserRegister;
use Api\Modules\Users\DomaiModel\UseCase\CommonUserRegisterRequest;
use Api\Modules\Users\DomaiModel\UseCase\SellerUserRegister;
use Api\Modules\Users\DomaiModel\UseCase\SellerUserRegisterRequest;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionStart;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionStartRequest;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionAuthorize;
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

$container->set('container', function () {
    return new BuilderContainer();    
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
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transação iniciada'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para iniciar a transação        
        /** @var TransactionStart $ucTransactionStart*/
        $ucTransactionStart = $DiContainer->get('TransactionStart');
        
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
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transação autorizada'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para autorizar a transação
        /** @var TransactionAuthorize $ucTransactionAuthorize*/
        $ucTransactionAuthorize = $DiContainer->get('TransactionAuthorize');
        
        $Response = $ucTransactionAuthorize->execute(
            new TransactionAuthorizeRequest($payload->uuid)
        );
        
        $content['data'] = [
            'uuid' => $Response->transaction_uuid,
            'status_authorization' => $Response->status_authorization,
            'status_notification' => $Response->status_notification
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

$app->post('/users', function () use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Usuário registrado com sucesso'
        ];
        
        $user = $app->request->getJsonRawBody();
        
        // caso de uso para registrar usuário comum
        /** @var CommonUserRegister $ucCommonUserReg*/
        $ucCommonUserReg = $DiContainer->get('CommonUserRegister');
        
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
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Lojista registrado com sucesso'
        ];
        
        $user = $app->request->getJsonRawBody();
        
        // caso de uso para registrar usuário seller
        /** @var SellerUserRegister $ucSellerUserReg*/
        $ucSellerUserReg = $DiContainer->get('SellerUserRegister');
        
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

