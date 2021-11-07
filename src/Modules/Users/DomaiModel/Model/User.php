<?php

namespace Api\Modules\Users\DomaiModel\Model;

use Api\Library\Contracts\Arrayable;
use Api\Library\ValueObject\Cpf;
use Api\Library\ValueObject\Cnpj;

class User implements Arrayable
{
    public string $uuid;
    
    public string $full_name;
    
    public string $type;
    
    /** @var Cpf */
    public Cpf $Cpf;
    
    /** @var Cnpj */
    public ?Cnpj $Cnpj = null;
    
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
            'cnpj' => $this->Cnpj ? $this->Cnpj->getCnpjUnmasked() : null,
            'email' => $this->email,
            'pass' => $this->pass
        ];
    }
}
