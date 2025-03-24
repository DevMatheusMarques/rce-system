<?php

namespace App\Repositories;

use App\Models\Product;

class ProductRepository
{
    protected Product $model;

    public function __construct(Product $product)
    {
        $this->model = $product;
    }

    public function getById(string $id): \LaravelIdea\Helper\App\Models\_IH_Product_C|Product|array
    {
        return $this->model->findOrFail($id);
    }

    public function all(): \LaravelIdea\Helper\App\Models\_IH_Product_C|\Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model->all();
    }

    public function getByAttribute(string $field, string $attribute): Product
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

    public function getAllWithValidation(array $data): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($data['filters'])) {
            $filters = $data['filters'];

            if (isset($filters['in_stock'])) {
                $operator = $filters['in_stock'] ? '>' : '<=';
                $query->where('stock', $operator, 0);
            }

            if (isset($filters['status'])) {
                $query->where('status', '=', $filters['status']);
            }

            if (isset($filters['search'])) {
                $query->where(function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('description', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('category', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('minimum', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('stock', 'like', '%' . $filters['search'] . '%')
                        ->orWhere('processing', 'like', '%' . $filters['search'] . '%');
                });
            }
        }


        return $query
            ->orderBy($data['order_by'], $data['order_direction'])
            ->paginate($data['per_page'])
            ->onEachSide(1);
    }

    public function countStatuses(): array
    {
        return [
            'active' => $this->model->where('status', 'active')->count(),
            'inactive' => $this->model->where('status', 'inactive')->count(),
            'total' => $this->model->count(),
        ];
    }
}
