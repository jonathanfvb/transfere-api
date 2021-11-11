<?php

namespace Api\Library\Contracts\Persistence;

interface RepositoryInterface
{
    public function getTransaction();
    public function setTransaction($transaction);
}
