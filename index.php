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
use Api\Modules\Transactions\DomainModel\UseCase\TransactionNotificationSend;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionNotificationSendRequest;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionGetDetail;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionGetDetailRequest;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionCancel;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionCancelRequest;

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

$app->get('/transactions/{uuid}', function ($uuid) use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transaction detail'
        ];
        
        // caso de uso para autorizar a transação
        /** @var TransactionGetDetail $ucTransactionGetDetail*/
        $ucTransactionGetDetail = $DiContainer->get('TransactionGetDetail');
        
        $Response = $ucTransactionGetDetail->execute(
            new TransactionGetDetailRequest($uuid)
            );
        
        $content['data'] = $Response;
        
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

$app->post('/transactions', function () use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transaction started'
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
            'uuid' => $Response->transactionUuid
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

$app->put('/transactions/authorize', function () use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transaction authorized'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para autorizar a transação
        /** @var TransactionAuthorize $ucTransactionAuthorize*/
        $ucTransactionAuthorize = $DiContainer->get('TransactionAuthorize');
        
        $Response = $ucTransactionAuthorize->execute(
            new TransactionAuthorizeRequest($payload->uuid)
        );
        
        $content['data'] = [
            'uuid' => $Response->transactionUuid,
            'status_authorization' => $Response->statusAuthorization,
            'status_notification' => $Response->statusNotification
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

$app->put('/transactions/send-notification', function () use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transaction notification sent'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para enviar a notificação
        /** @var TransactionNotificationSend $ucTransactionNotificationSend*/
        $ucTransactionNotificationSend = $DiContainer->get('TransactionNotificationSend');
        
        $ucTransactionNotificationSend->execute(
            new TransactionNotificationSendRequest($payload->uuid)
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

$app->delete('/transactions/cancel/{uuid}', function ($uuid) use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Transaction cancelled'
        ];
        
        // caso de uso para cancelar a transação
        /** @var TransactionCancel $ucTransactionCancel*/
        $ucTransactionCancel = $DiContainer->get('TransactionCancel');
        
        $ucTransactionCancel->execute(
            new TransactionCancelRequest($uuid)
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

$app->post('/users/common', function () use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Common User registered'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para registrar usuário comum
        /** @var CommonUserRegister $ucCommonUserReg*/
        $ucCommonUserReg = $DiContainer->get('CommonUserRegister');
        
        $Response = $ucCommonUserReg->execute(
            new CommonUserRegisterRequest(
                $payload->full_name, 
                $payload->cpf, 
                $payload->email, 
                $payload->pass
            )
        );
        
        $content['data'] = $Response;
        
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

$app->post('/users/sellers', function () use ($app) {
    try {
        /** @var \DI\Container $DiContainer */
        $DiContainer = $app->getDI()->get('container');
        
        $app->response->setStatusCode(200);
        $content = [
            'success' => true,
            'message' => 'Seller User registered'
        ];
        
        $payload = $app->request->getJsonRawBody();
        
        // caso de uso para registrar usuário seller
        /** @var SellerUserRegister $ucSellerUserReg*/
        $ucSellerUserReg = $DiContainer->get('SellerUserRegister');
        
        $Response = $ucSellerUserReg->execute(
            new SellerUserRegisterRequest(
                $payload->full_name,
                $payload->cpf,
                $payload->cnpj,
                $payload->email,
                $payload->pass
            )
        );
        
        $content['data'] = $Response;
        
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

