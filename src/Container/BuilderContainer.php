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
        $Container = new UsersContainer($this);
        $Container->initialize();
    }
    
    private function initializeModuleUserWallet()
    {
        $Container = new UserWalletContainer($this);
        $Container->initialize();
    }
    
    private function initializeModuleTransactions()
    {
        $Container = new TransactionsContainer($this);
        $Container->initialize();
    }
    
    private function initializeLibraries()
    {
        $Container = new LibraryContainer($this);
        $Container->initialize();
    }
}
