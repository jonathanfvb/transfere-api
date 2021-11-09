<?php

namespace Api\Container\Modules;

use Api\Container\AbstractContainer;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionStart;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionAuthorize;
use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\Persistence\Phalcon\TransactionRepository;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionNotificationSend;
use Api\Modules\Transactions\DomainModel\UseCase\TransactionGetDetail;

class TransactionsContainer extends AbstractContainer
{
    public function initialize()
    {
        // Repositories
        $this->diContainer->set(
            TransactionRepositoryInterface::class, 
            \DI\create(TransactionRepository::class)
        );
        
        // Use Cases
        $this->diContainer->set('TransactionStart', \DI\autowire(TransactionStart::class));
        $this->diContainer->set('TransactionAuthorize', \DI\autowire(TransactionAuthorize::class));
        $this->diContainer->set('TransactionNotificationSend', \DI\autowire(TransactionNotificationSend::class));
        $this->diContainer->set('TransactionGetDetail', \DI\autowire(TransactionGetDetail::class));
    }
}
