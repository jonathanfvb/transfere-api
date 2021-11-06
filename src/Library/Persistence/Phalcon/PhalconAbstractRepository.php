<?php

namespace Api\Library\Persistence\Phalcon;

use Api\Library\Persistence\Exception\PersistenceException;

abstract class PhalconAbstractRepository
{
    /** @var \Phalcon\Mvc\Model */
    protected ?\Phalcon\Mvc\Model $entity = null;
    
    abstract public static function parsePhalconModelToDomainModel($result);
    
    public function persist($model)
    {
        if ($this->entity == null) {
            throw new PersistenceException('Entity não definida');
        }
        
        $this->entity->assign($model->toArray());
        
        if ($this->entity->save() === false) {
            throw new PersistenceException(implode('. ', $this->entity->getMessages()));
        }
        
        return $this->entity;
    }
}
