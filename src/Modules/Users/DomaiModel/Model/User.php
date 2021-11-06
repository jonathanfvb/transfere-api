<?php

namespace Api\Modules\Users\DomaiModel\Model;

use Api\Library\Contracts\Arrayable;

class User implements Arrayable
{
    public ?int $id = null;
    
    public string $name;
    
    public string $type;
    
    public ?string $cpf = null;
    
    public ?string $cnpj = null;
    
    public string $email;
    
    public string $pass;
    
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'cpf' => $this->cpf,
            'cnpj' => $this->cnpj,
            'email' => $this->email,
            'pass' => $this->pass
        ];
    }
}
