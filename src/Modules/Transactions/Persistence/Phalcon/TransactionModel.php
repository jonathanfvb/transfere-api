<?php

namespace Api\Modules\Transactions\Persistence\Phalcon;

use Phalcon\Mvc\Model;
use Api\Modules\Users\Persistence\Phalcon\UserModel;

class TransactionModel extends Model
{
    public function initialize()
    {
        $this->setSource('transaction');
        
        $this->hasOne('user_payer_uuid', UserModel::class, 'uuid', ['alias' => 'Payer']);
        $this->hasOne('user_payee_uuid', UserModel::class, 'uuid', ['alias' => 'Payee']);
    }
}
