<?php
namespace Api\Modules\Transactions\DomainModel\Model;

class TransactionEnum
{
    CONST STATUS_PENDING_AUTHORIZATION  = 'pending_authorization';
    
    CONST STATUS_PENDING_NOTIFICATION   = 'pending_notification';
    
    CONST STATUS_FINISHED_UNAUTHORIZED  = 'finished_unauthorized';
    
    CONST STATUS_FINISHED_AUTHORIZED    = 'finished_authorized';
}
