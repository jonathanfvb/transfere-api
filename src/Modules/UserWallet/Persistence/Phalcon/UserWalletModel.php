<?php

namespace Api\Modules\UserWallet\Persistence\Phalcon;

use Phalcon\Mvc\Model;
use Api\Modules\Users\Persistence\Phalcon\UserModel;

class UserWalletModel extends Model
{
    public function initialize()
    {
        $this->setSource('user_wallet');
        
        $this->belongsTo('user_uuid', UserModel::class, 'uuid', ['alias' => 'User']);
    }
}
