<?php
namespace Api\Modules\Users\DomaiModel\UseCase;

class SellerUserRegisterRequest
{
    public string $full_name;
    
    public string $cpf;
    
    public string $cnpj;
    
    public string $email;
    
    public string $pass;
    
    public function __construct(
        string $full_name,
        string $cpf,
        string $cnpj,
        string $email,
        string $pass
        )
    {
        $this->full_name = $full_name;
        $this->cpf = $cpf;
        $this->cnpj = $cnpj;
        $this->email = $email;
        $this->pass = $pass;
    }
}
