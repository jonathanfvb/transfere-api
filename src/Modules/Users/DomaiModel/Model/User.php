<?php

namespace Api\Modules\Users\DomaiModel\Model;

use Api\Library\Contracts\Arrayable;
use Api\Library\ValueObject\Cpf;

class User implements Arrayable
{
    public string $uuid;
    
    public string $full_name;
    
    public string $type;
    
    /** @var Cpf */
    public Cpf $Cpf;
    
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
            'cpf' => $this->Cpf->getCpfUnmasked(),
            'cnpj' => $this->cnpj,
            'email' => $this->email,
            'pass' => $this->pass
        ];
    }
}
