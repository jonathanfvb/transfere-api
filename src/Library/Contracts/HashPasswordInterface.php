<?php

namespace Api\Library\Contracts;

interface HashPasswordInterface
{
    public function generateHashedPassword(string $plain_password);
    
    public function verifyPassword(string $plain_password, string $hashed_password);
}
