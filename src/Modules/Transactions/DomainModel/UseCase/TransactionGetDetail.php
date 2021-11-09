<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionGetDetailDTO;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;

class TransactionGetDetail
{
    private TransactionRepositoryInterface $TransactionRepository;
    
    public function __construct(
        TransactionRepositoryInterface $TransactionRepository
    )
    {
        $this->TransactionRepository = $TransactionRepository;
    }
    
    public function execute(TransactionGetDetailRequest $Request): TransactionGetDetailDTO
    {
        // busca a transação
        $Transaction = $this->TransactionRepository->findByUuid($Request->transaction_uuid);
        if (!$Transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        return new TransactionGetDetailDTO($Transaction);
    }
}
