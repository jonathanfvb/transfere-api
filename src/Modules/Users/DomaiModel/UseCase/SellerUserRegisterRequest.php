<?php
namespace Api\Modules\Users\DomaiModel\UseCase;

class SellerUserRegisterRequest
{
    public string $fullName;
    
    public string $cpf;
    
    public string $cnpj;
    
    public string $email;
    
    public string $pass;
    
    public function __construct(
        string $fullName,
        string $cpf,
        string $cnpj,
        string $email,
        string $pass
        )
    {
        $this->fullName = $fullName;
        $this->cpf = $cpf;
        $this->cnpj = $cnpj;
        $this->email = $email;
        $this->pass = $pass;
    }
}
