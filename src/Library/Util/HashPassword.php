<?php

namespace Api\Library\Util;

use Api\Library\Contracts\HashPasswordInterface;

class HashPassword implements HashPasswordInterface
{
    private string $alg = PASSWORD_BCRYPT;
    
    private int $cost = 10; 

    public function generateHashedPassword($plainPassword)
    {
        return password_hash(
            $plainPassword, 
            $this->alg, 
            ['cost' => $this->cost]
        );
    }

    public function verifyPassword($plainPassword, $hashedPassword)
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
