<?php

namespace Api\Modules\Users\Persistence\Phalcon;

use Phalcon\Mvc\Model;

class UserModel extends Model
{
    public function initialize()
    {
        $this->setSource('user');
    }
}

