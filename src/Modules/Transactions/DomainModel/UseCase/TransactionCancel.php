<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;
use \DateTimeImmutable;

class TransactionCancel
{
    private TransactionRepositoryInterface $transactionRepository;
    
    public function __construct(
        TransactionRepositoryInterface $transactionRepository
    )
    {
        $this->transactionRepository = $transactionRepository;
    }
    
    public function execute(TransactionCancelrequest $request)
    {
        // busca a transação
        $transaction = $this->transactionRepository->findByUuid($request->transactionUuid);
        if (!$transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // valida se está pendente de autorização
        if (!$transaction->isAuthorizationPending()) {
            throw new TransactionException(
                "Transaction can not be cancelled. Status: {$transaction->statusAuthorization}.",
                400
            );
        }
        
        // cancela a transação
        $transaction->statusAuthorization = TransactionEnum::AUTHORIZATION_CANCELLED;
        $transaction->updatedAt = new DateTimeImmutable();
        $this->transactionRepository->persist($transaction);
    }
}
