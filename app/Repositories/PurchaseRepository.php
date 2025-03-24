<?php

namespace App\Repositories;

use App\Models\Purchase;

class PurchaseRepository
{
    protected Purchase $model;

    public function __construct(Purchase $product)
    {
        $this->model = $product;
    }

    public function getById(string $id): Purchase|array|\LaravelIdea\Helper\App\Models\_IH_Purchase_C
    {
        return $this->model
            ->with(['supplier:id,cnpj,corporate_name'])
            ->with(['purchaseItems.product'])
            ->findOrFail($id);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection|array|\LaravelIdea\Helper\App\Models\_IH_Purchase_C
    {
        return $this->model->all();
    }

    public function getByAttribute(string $field, string $attribute): Purchase
    {
        return $this->model->where($field, $attribute);
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

    public function delete(string $id): ?bool
    {
        return $this->model->where('id', $id)
            ->delete();
    }

    public function getAllWithValidation(array $data): \Illuminate\Pagination\LengthAwarePaginator|array|\LaravelIdea\Helper\App\Models\_IH_Purchase_C
    {
        $query = $this->model->query();
        $query
            ->withCount('purchaseItems')
            ->with('purchaseItems')
            ->with(['supplier:id,cnpj,corporate_name'])
            ->with(['user:id,name']);

        if (isset($data['filters'])) {
            $filters = $data['filters'];

            if (isset($filters['status'])) {
                $query->where('status', '=', $filters['status']);
            }

            if (isset($filters['created_at'])) {
                $query->where('created_at', 'like', $filters['created_at'] .  '%');
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query
                    ->where('id', 'like', '%' . $search . '%')
                    ->orWhere('status', $search)
                    ->orWhere('created_at', 'like', '%' . $search . '%')
                    ->orWhere('updated_at', 'like', '%' . $search . '%');

                $query->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
                $query->orWhereHas('supplier', function ($q) use ($search) {
                    $q->where('cnpj', 'like', '%' . $search . '%')
                    ->orWhere('corporate_name', 'like', '%' . $search . '%');
                });
            }
        }
        if ($data['order_by'] === 'user_name') {
            $query
                ->join('users', 'users.id', '=', 'purchases.user_id')
                ->orderBy('users.name', $data['order_direction']);
        } elseif ($data['order_by'] === 'supplier_cnpj') {
            $query
                ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
                ->orderBy('suppliers.cnpj', $data['order_direction']);
        } else {
            $query->orderBy($data['order_by'], $data['order_direction']);
        }

        return $query
            ->paginate($data['per_page'])
            ->onEachSide(1);

    }
}
