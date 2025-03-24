<?php

namespace App\Repositories;

use App\Models\OrderItem;

class OrderItemRepository
{

    protected OrderItem $model;

    public function __construct(OrderItem $order)
    {
        $this->model = $order;
    }
    public function getByAttribute(string $field, string $attribute)
    {
        return $this->model->where($field, $attribute)->get();
    }

    public function getById(string $id): OrderItem|array|\LaravelIdea\Helper\App\Models\_IH_OrderItem_C
    {
        return $this->model->findOrFail($id);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection|array|\LaravelIdea\Helper\App\Models\_IH_OrderItem_C
    {
        return $this->model->all();
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

    public function deleteByOrderId($orderId, $productId): ?bool
    {
        return $this->model
            ->where('order_id', $orderId)
            ->where('product_id', $productId)
            ->delete();
    }

    public function delete(string $id): ?bool
    {
        return $this->model->where('id', $id)
            ->delete();
    }

    public function getAllWithValidation(array $data): \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Pagination\LengthAwarePaginator|array|\LaravelIdea\Helper\App\Models\_IH_OrderItem_C
    {
        return $this->model
            ->with(['order'])
            ->orderBy($data['order_by'], $data['order_direction'])
            ->paginate($data['per_page']);
    }

    public function getAllByOrderId(string $id, string $orderBy, string $orderDirection): array|\LaravelIdea\Helper\App\Models\_IH_OrderItem_C
    {
        return $this->model
            ->where('order_id', $id)
            ->orderBy($orderBy, $orderDirection)
            ->get();
    }
}
