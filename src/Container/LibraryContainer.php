<?php

namespace Api\Container;

use Api\Library\Contracts\HashPasswordInterface;
use Api\Library\Util\HashPassword;
use Api\Library\Contracts\UuidGeneratorInterface;
use Api\Library\Util\PhalconUuidGenerator;
use Api\Library\Contracts\Service\AuthorizeServiceInterface;
use Api\Library\Service\ExternalAuthorizationService;
use Api\Library\Contracts\Service\NotificationServiceInterface;
use Api\Library\Service\ExternalNotificationService;
use Api\Library\Persistence\TransactionManagerInterface;
use Api\Library\Persistence\Phalcon\PhalconTransactionManager;

class LibraryContainer extends AbstractContainer
{
    public function initialize()
    {
        $this->diContainer->set(HashPasswordInterface::class, \DI\create(HashPassword::class));
        $this->diContainer->set(UuidGeneratorInterface::class, \DI\create(PhalconUuidGenerator::class));
        $this->diContainer->set(AuthorizeServiceInterface::class, \DI\create(ExternalAuthorizationService::class));
        $this->diContainer->set(NotificationServiceInterface::class, \DI\create(ExternalNotificationService::class));
        $this->diContainer->set(TransactionManagerInterface::class, \DI\create(PhalconTransactionManager::class));
    }
}
