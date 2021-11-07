<?php

namespace Api\Modules\Users\Persistence\Phalcon;

use Phalcon\Mvc\Model;
use Api\Modules\UserWallet\Persistence\Phalcon\UserWalletModel;

class UserModel extends Model
{
    public function initialize()
    {
        $this->setSource('user');
        
        $this->hasOne('uuid', UserWalletModel::class, 'user_uuid', ['alias' => 'UserWallet']);
    }
}
