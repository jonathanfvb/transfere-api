<?php

namespace Api\Modules\Users\DomaiModel\Model;

use Api\Library\Contracts\Arrayable;
use Api\Library\ValueObject\Cpf;
use Api\Library\ValueObject\Cnpj;

class User implements Arrayable
{
    public string $uuid;
    
    public string $fullName;
    
    public string $type;
    
    /** @var Cpf */
    public Cpf $cpf;
    
    /** @var Cnpj */
    public ?Cnpj $cnpj = null;
    
    public string $email;
    
    public string $pass;
    
    
    public function getType(): string
    {
        return empty($this->cnpj) ? UserEnum::TYPE_COMMON : UserEnum::TYPE_SELLER;
    }
    
    public function isSeller(): string
    {
        return $this->getType() == UserEnum::TYPE_SELLER;
    }
    
    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'full_name' => $this->fullName,
            'type' => $this->getType(),
            'cpf' => $this->cpf->getCpfUnmasked(),
            'cnpj' => $this->cnpj ? $this->cnpj->getCnpjUnmasked() : null,
            'email' => $this->email,
            'pass' => $this->pass
        ];
    }
}
