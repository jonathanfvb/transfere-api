<?php

namespace Api\Modules\Transactions\DomainModel\UseCase;

use Api\Modules\Transactions\DomainModel\Repository\TransactionRepositoryInterface;
use Api\Modules\Transactions\DomainModel\Exception\TransactionException;
use Api\Modules\Transactions\DomainModel\Model\TransactionEnum;

class TransactionCancel
{
    private TransactionRepositoryInterface $TransactionRepository;
    
    public function __construct(
        TransactionRepositoryInterface $TransactionRepository
    )
    {
        $this->TransactionRepository = $TransactionRepository;
    }
    
    public function execute(TransactionCancelRequest $Request)
    {
        // busca a transação
        $Transaction = $this->TransactionRepository->findByUuid($Request->transaction_uuid);
        if (!$Transaction) {
            throw new TransactionException('Transaction not found', 404);
        }
        
        // valida se está pendente de autorização
        if (!$Transaction->isAuthorizationPending()) {
            throw new TransactionException(
                "Transaction can not be cancelled. Status: {$Transaction->status_authorization}.",
                400
            );
        }
        
        // cancela a transação
        $Transaction->status_authorization = TransactionEnum::AUTHORIZATION_CANCELLED;
        $Transaction->UpdatedAt = new \DateTimeImmutable();
        $this->TransactionRepository->persist($Transaction);
    }
}
