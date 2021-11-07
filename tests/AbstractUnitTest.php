<?php

declare(strict_types=1);

namespace Tests;

use Phalcon\Di;
use Phalcon\Di\FactoryDefault;
use Phalcon\Db\Adapter\Pdo\Mysql;
use Phalcon\Incubator\Test\PHPUnit\UnitTestCase;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Session\Adapter\Stream as SessionStream;
use PHPUnit\Framework\IncompleteTestError;
use Api\Container\BuilderContainer;

abstract class AbstractUnitTest extends UnitTestCase
{
    private bool $loaded = false;
    
    /** @var \DI\Container */
    protected $diContainer;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $di = new FactoryDefault();
        
        Di::reset();
        Di::setDefault($di);
        
//         $configFile =  APP_PATH . '/config/config.php';
//         $config = new \Phalcon\Config\Adapter\Php($configFile);
//         $di->set('config', $config);
        
        $di->setShared('db', function () {
            $params = [
                'host'      => 'db',
                'username'  => 'root',
                'password'  => 'root',
                'dbname'    => 'transfere',
                'port'      => '3306'
            ];
            
            $connection = new Mysql($params);
            
            return $connection;
        });
        
        $di->setShared('sessionBag', function () {
            $session = new SessionManager();
            $files = new SessionStream([
                'savePath' => '/var/lib/php/sessions',
                'prefix'   => 'sess_'
            ]);
            $session->setAdapter($files);
            $session->start();
            return $session;
        });
        
        $di->setShared('session',function(){
            $session = new SessionManager();
            $files = new SessionStream([
                'savePath' => '/var/lib/php/sessions',
                'prefix'   => 'sess_'
            ]);
            $session->setAdapter($files)->start();
            return $session;
        });
                
        $di->set('container', function() {
            $container = new BuilderContainer();
            return $container;
        });
            
        $this->diContainer = $di->getDefault()->get('container');
        
        $this->loaded = true;
    }
    
    public function __destruct()
    {
        if (!$this->loaded) {
            throw new IncompleteTestError(
                "Please run parent::setUp()."
            );
        }
    }
}
