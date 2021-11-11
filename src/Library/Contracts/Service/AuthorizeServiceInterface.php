<?php

namespace Api\Library\Contracts\Service;

use Api\Modules\Users\DomaiModel\Model\User;

interface AuthorizeServiceInterface
{
    public function authorize(User $payer, float $value): bool;    
}
