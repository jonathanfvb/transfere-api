<?php

namespace Api\Library\ValueObject;

class Cpf
{
    private string $clean_cpf;
    
    public function __construct(string $cpf)
    {        
        $this->clean_cpf = $this->cleanCpf($cpf);
        $this->validateCpf($this->clean_cpf);
    }
    
    public function getCpfUnmasked(): string
    {
        return $this->clean_cpf;
    }
    
    public function getCpfMasked(): string
    {
        return $this->maskCpf($this->clean_cpf);
    }
    
    private function cleanCpf(string $cpf)
    {
        return preg_replace( '/[^0-9]/', '', $cpf);
    }
    
    private function validateCpf(string $clean_cpf)
    {
        if (strlen($clean_cpf) != 11) {
            throw new \InvalidArgumentException('Invalid CPF', 400);
        }
    }
    
    private function maskCpf(string $clean_cpf)
    {
        return substr($clean_cpf, 0, 3)
            .'.'.substr($clean_cpf, 3, 3)
            .'.'.substr($clean_cpf, 6, 3)
            .'-'.substr($clean_cpf, 9);
    }
}
