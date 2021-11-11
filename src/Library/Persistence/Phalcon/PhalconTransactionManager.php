<?php

namespace Api\Library\Persistence\Phalcon;

use Api\Library\Contracts\Persistence\TransactionManagerInterface;
use Phalcon\Mvc\Model\Transaction\Manager;

class PhalconTransactionManager implements TransactionManagerInterface
{
    private $transaction;
    
    public function __construct()
    {
        $txManager = new Manager();
        $transaction = $txManager->get(false);
        $transaction->setRollbackOnAbort(false);
        
        $this->transaction = $transaction;
    }
    
    public function getTransaction()
    {
        return $this->transaction;
    }
}
