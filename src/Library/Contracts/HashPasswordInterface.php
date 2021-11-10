<?php

namespace Api\Library\Contracts;

interface HashPasswordInterface
{
    public function generateHashedPassword(string $plainPassword);
    
    public function verifyPassword(string $plainPassword, string $hashedPassword);
}
