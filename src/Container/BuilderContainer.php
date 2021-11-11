<?php

namespace Api\Container;

use DI\Container;
use Api\Container\Modules\TransactionsContainer;
use Api\Container\Modules\UsersContainer;
use Api\Container\Modules\UserWalletContainer;

class BuilderContainer extends Container
{
    public function __construct()
    {
        parent::__construct();
        
        $this->initializeModuleUsers();
        $this->initializeModuleUserWallet();
        $this->initializeModuleTransactions();
        $this->initializeLibraries();
    }
    
    private function initializeModuleUsers()
    {
        $container = new UsersContainer($this);
        $container->initialize();
    }
    
    private function initializeModuleUserWallet()
    {
        $container = new UserWalletContainer($this);
        $container->initialize();
    }
    
    private function initializeModuleTransactions()
    {
        $container = new TransactionsContainer($this);
        $container->initialize();
    }
    
    private function initializeLibraries()
    {
        $container = new LibraryContainer($this);
        $container->initialize();
    }
}
