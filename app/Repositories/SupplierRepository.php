<?php

namespace App\Repositories;

use App\Models\Supplier;

class SupplierRepository
{
    protected Supplier $model;
    public function __construct(Supplier $supplier)
    {
        $this->model = $supplier;
    }
    public function getById(string $id): \LaravelIdea\Helper\App\Models\_IH_Supplier_C|array|Supplier
    {
        return $this->model->findOrFail($id);
    }

    public function all(): \LaravelIdea\Helper\App\Models\_IH_Supplier_C|\Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model->all();
    }

    public function getByAttribute(string $field, string $attribute): Supplier
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

    public function getAllWithValidation(array $data): \LaravelIdea\Helper\App\Models\_IH_Supplier_C|array|\Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->query();

        if (isset($data['filters'])) {
            $filters = $data['filters'];
            $query
                ->where('cnpj', 'like', '%' . $filters['search'] . '%')
                ->orWhere('corporate_name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('trade_name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                ->orWhere('cep', 'like', '%' . $filters['search'] . '%')
                ->orWhere('phone', 'like', '%' . $filters['search'] . '%')
                ->orWhere('address_city', 'like', '%' . $filters['search'] . '%')
                ->orWhere('address_state', 'like', '%' . $filters['search'] . '%');
        }

        return $query
            ->orderBy($data['order_by'], $data['order_direction'])
            ->paginate($data['per_page'])
            ->onEachSide(1);
    }
}
