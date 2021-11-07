<?php

namespace Api\Library\Contracts\Service;

use Api\Modules\Users\DomaiModel\Model\User;

interface NotificationServiceInterface
{
    public function sendNotification(User $Receiver): bool;
}
