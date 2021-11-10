<?php

namespace Api\Library\ValueObject;

class Cpf
{
    private string $cleanCpf;
    
    public function __construct(string $cpf)
    {        
        $this->cleanCpf = $this->cleanCpf($cpf);
        $this->validateCpf($this->cleanCpf);
    }
    
    public function getCpfUnmasked(): string
    {
        return $this->cleanCpf;
    }
    
    public function getCpfMasked(): string
    {
        return $this->maskCpf($this->cleanCpf);
    }
    
    private function cleanCpf(string $cpf)
    {
        return preg_replace( '/[^0-9]/', '', $cpf);
    }
    
    private function validateCpf(string $cleanCpf)
    {
        // TODO - Incluir algoritmo para validar o CPF
        if (strlen($cleanCpf) != 11) {
            throw new \InvalidArgumentException('Invalid CPF', 400);
        }
    }
    
    private function maskCpf(string $cleanCpf)
    {
        return substr($cleanCpf, 0, 3)
            .'.'.substr($cleanCpf, 3, 3)
            .'.'.substr($cleanCpf, 6, 3)
            .'-'.substr($cleanCpf, 9);
    }
}
