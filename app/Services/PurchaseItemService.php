<?php

namespace App\Services;

use App\Repositories\PurchaseItemRepository;

class PurchaseItemService
{
    protected PurchaseItemRepository $purchaseItemRepository;

    public function __construct(PurchaseItemRepository $purchaseItemRepository)
    {
        $this->purchaseItemRepository = $purchaseItemRepository;
    }

    public function getById(string $id)
    {
        //todo: make this method
    }

    public function store(array $data)
    {
        //todo: make this method
    }

    public function updateById(array $data, string $id)
    {
        //todo: make this method
    }

    public function delete(string $id)
    {
        //todo: make this method
    }

    public function getAllWithValidation(array $data)
    {
        //todo: make this method
    }
}
