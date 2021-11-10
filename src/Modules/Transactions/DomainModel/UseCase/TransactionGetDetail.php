<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\DTO\TransactionGetDetailDTO;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;

class TransactionGetDetail
{
    private TransactionRepositoryInterface $transactionRepository;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository
    )
    {
        $this->transactionRepository = $transactionRepository;
    }
    
    public function execute(TransactionGetDetailrequest $request): TransactionGetDetailDTO
    {
        // busca a transação
        $transaction = $this->transactionRepository->findByUuid($request->transactionUuid);
        if (!$transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        return new TransactionGetDetailDTO($transaction);
    }
}
