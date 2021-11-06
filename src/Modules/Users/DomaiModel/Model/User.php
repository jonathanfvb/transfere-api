<?php

namespace Api\Modules\Users\DomaiModel\Model;

use Api\Library\Contracts\Arrayable;

class User implements Arrayable
{
    public string $uuid;
    
    public string $full_name;
    
    public string $type;
    
    public string $cpf;
    
    public ?string $cnpj = null;
    
    public string $email;
    
    public string $pass;
    
    public function getType(): string
    {
        return empty($this->cnpj) ? UserEnum::TYPE_COMMON : UserEnum::TYPE_SELLER;
    }
    
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'full_name' => $this->full_name,
            'type' => $this->getType(),
            'cpf' => $this->cpf,
            'cnpj' => $this->cnpj,
            'email' => $this->email,
            'pass' => $this->pass
        ];
    }
}
