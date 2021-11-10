<?php

namespace Api\Modules\Users\DomaiModel\UseCase;

class CommonUserRegisterRequest
{
    public string $fullName;
    
    public string $cpf;
    
    public string $email;
    
    public string $pass;
    
    public function __construct(
        string $fullName,
        string $cpf,
        string $email,
        string $pass
    )
    {
        $this->fullName = $fullName;
        $this->cpf = $cpf;
        $this->email = $email;
        $this->pass = $pass;
    }
}
