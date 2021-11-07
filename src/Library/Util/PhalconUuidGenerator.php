<?php

namespace Api\Library\Util;

use Api\Library\Contracts\UuidGeneratorInterface;
use Phalcon\Security\Random;

class PhalconUuidGenerator implements UuidGeneratorInterface
{
    public function generateUuid(): string
    {
        $Random = new Random();
        return $Random->uuid();
    }
}
