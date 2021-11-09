<?php
namespace Api\Modules\Transactions\DomainModel\Model;

class TransactionEnum
{
    CONST AUTHORIZATION_PENDING = 'pending';
    
    CONST AUTHORIZATION_SUCCESS = 'authorized';
    
    CONST AUTHORIZATION_FAILED = 'unauthorized';
    
    CONST AUTHORIZATION_CANCELLED = 'cancelled';
    
    CONST NOTIFICATION_PENDING = 'pending';
    
    CONST NOTIFICATION_SENT = 'sent';
}
