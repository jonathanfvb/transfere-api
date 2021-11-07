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
    
$app->handle(
    $_SERVER["REQUEST_URI"]
);

