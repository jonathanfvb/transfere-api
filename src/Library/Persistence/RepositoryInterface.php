<?php

namespace Api\Library\Persistence;

interface RepositoryInterface
{
    public function getTransaction();
    public function setTransaction($transaction);
}
