<?php

namespace Api\Library\Contracts;

interface UuidGeneratorInterface
{
    public function generateUuid(): string;
}
