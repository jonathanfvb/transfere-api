<?php

namespace Api\Library\ValueObject;

use \InvalidArgumentException;

class Cnpj
{
    private string $cleanCnpj;
    
    public function __construct(string $cnpj)
    {        
        $this->cleanCnpj = $this->cleanCnpj($cnpj);
        $this->validateCnpj($this->cleanCnpj);
    }
    
    public function getCnpjUnmasked(): string
    {
        return $this->cleanCnpj;
    }
    
    public function getCnpjMasked(): string
    {
        return $this->maskCnpj($this->cleanCnpj);
    }
    
    private function cleanCnpj(string $cnpj)
    {
        return preg_replace( '/[^0-9]/', '', $cnpj);
    }
    
    private function validateCnpj(string $cleanCnpj)
    {
        // TODO - Incluir algoritmo para validar o CNPJ
        if (strlen($cleanCnpj) != 14) {
            throw new InvalidArgumentException('Invalid CNPJ', 400);
        }
    }
    
    private function maskCnpj(string $cleanCnpj)
    {
        return substr($cleanCnpj, 0, 2)
            .'.'.substr($cleanCnpj, 2, 3)
            .'.'.substr($cleanCnpj, 5, 3)
            .'/'.substr($cleanCnpj, 8, 4)
            .'-'.substr($cleanCnpj, 12);
    }
}
