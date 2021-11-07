<?php

namespace Api\Library\Util;

use Api\Library\Contracts\HashPasswordInterface;

class HashPassword implements HashPasswordInterface
{
    private string $alg = PASSWORD_BCRYPT;
    
    private int $cost = 10; 

    public function generateHashedPassword($plain_password)
    {
        return password_hash(
            $plain_password, 
            $this->alg, 
            ['cost' => $this->cost]
        );
    }

    public function verifyPassword($plain_password, $hashed_password)
    {
        return password_verify($plain_password, $hashed_password);
    }
}
