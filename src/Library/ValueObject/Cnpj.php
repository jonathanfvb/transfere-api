<?php

namespace Api\Library\ValueObject;

class Cnpj
{
    private string $clean_cnpj;
    
    public function __construct(string $cnpj)
    {        
        $this->clean_cnpj = $this->cleanCnpj($cnpj);
        $this->validateCnpj($this->clean_cnpj);
    }
    
    public function getCnpjUnmasked(): string
    {
        return $this->clean_cnpj;
    }
    
    public function getCnpjMasked(): string
    {
        return $this->maskCnpj($this->clean_cnpj);
    }
    
    private function cleanCnpj(string $cnpj)
    {
        return preg_replace( '/[^0-9]/', '', $cnpj);
    }
    
    private function validateCnpj(string $clean_cnpj)
    {
        // TODO - Incluir algoritmo para validar o CNPJ
        if (strlen($clean_cnpj) != 14) {
            throw new \InvalidArgumentException('Invalid CNPJ', 400);
        }
    }
    
    private function maskCnpj(string $clean_cnpj)
    {
        return substr($clean_cnpj, 0, 2)
            .'.'.substr($clean_cnpj, 2, 3)
            .'.'.substr($clean_cnpj, 5, 3)
            .'/'.substr($clean_cnpj, 8, 4)
            .'-'.substr($clean_cnpj, 12);
    }
}
