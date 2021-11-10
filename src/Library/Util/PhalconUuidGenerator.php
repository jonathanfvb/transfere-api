<?php

namespace Api\Library\Util;

use Api\Library\Contracts\UuidGeneratorInterface;
use Phalcon\Security\Random;

class PhalconUuidGenerator implements UuidGeneratorInterface
{
    public function generateUuid(): string
    {
        $random = new Random();
        return $random->uuid();
    }
}
