<?php

namespace App\Repositories;

use App\Models\PurchaseItem;

class PurchaseItemRepository
{
    protected PurchaseItem $model;

    public function __construct(PurchaseItem $product)
    {
        $this->model = $product;
    }

    public function getById(string $id): PurchaseItem|array|\LaravelIdea\Helper\App\Models\_IH_PurchaseItem_C
    {
        return $this->model->findOrFail($id);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection|array|\LaravelIdea\Helper\App\Models\_IH_PurchaseItem_C
    {
        return $this->model->all();
    }

    public function getByAttribute(string $field, string $attribute)
    {
        return $this->model->where($field, $attribute)->get();
    }

    public function store(array $data)
    {
        return $this->model->create($data);
    }

    public function updateById(array $data, string $id): bool
    {
        return $this->model
            ->where('id', $id)
            ->update($data);
    }

    public function deleteByPurchaseId($purchaseId, $productId): ?bool
    {
        return $this->model
            ->where('purchase_id', $purchaseId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function delete(string $id): ?bool
    {
        return $this->model->where('id', $id)
            ->delete();
    }

    public function getAllWithValidation(array $data): \Illuminate\Pagination\LengthAwarePaginator|array|\LaravelIdea\Helper\App\Models\_IH_PurchaseItem_C
    {
        return $this->model
            ->orderBy($data['order_by'], $data['order_direction'])
            ->paginate($data['per_page']);
    }
}
