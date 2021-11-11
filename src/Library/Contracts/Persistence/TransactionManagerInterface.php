<?php

namespace Api\Library\Contracts\Persistence;

interface TransactionManagerInterface
{
    public function getTransaction();
}
