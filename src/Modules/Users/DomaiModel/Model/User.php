<?php

namespace Api\Modules\Users\DomaiModel\Model;

use Api\Library\Contracts\Arrayable;

class User implements Arrayable
{
    public string $uuid;
    
    public string $full_name;
    
    public string $type;
    
    public string $cpf = null;
    
    public ?string $cnpj = null;
    
    public string $email;
    
    public string $pass;
    
    
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'full_name' => $this->full_name,
            'type' => $this->type,
            'cpf' => $this->cpf,
            'cnpj' => $this->cnpj,
            'email' => $this->email,
            'pass' => $this->pass
        ];
    }
}
