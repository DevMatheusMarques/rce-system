<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository
{
    protected User $model;
    public function __construct(User $user)
    {
        $this->model = $user;
    }
    public function getById(string $id): array|User|\LaravelIdea\Helper\App\Models\_IH_User_C
    {
        return $this->model->findOrFail($id);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection|array|\LaravelIdea\Helper\App\Models\_IH_User_C
    {
        return $this->model->all();
    }

    public function getByAttribute(string $field, string $attribute): User
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

    public function lastUser(): ?User
    {
        return $this->model->orderBy('registration', 'desc')->first();
    }

    public function existsByAttribute(string $field, $value): bool
    {
        return $this->model->where($field, '=', $value)->exists();
    }

    public function getAllWithValidation(array $data): array|\Illuminate\Pagination\LengthAwarePaginator|\LaravelIdea\Helper\App\Models\_IH_User_C
    {
        $query = $this->model->query();

        if (isset($data['filters'])) {
            $filters = $data['filters'];
            $query
                ->where('name', 'like', '%' . $filters['search'] . '%')
                ->orWhere('status', '=', $filters['search'])
                ->orWhere('email', 'like', '%' . $filters['search'] . '%')
                ->orWhere('level', 'like', '%' . $filters['search'] . '%')
                ->orWhere('registration', 'like', '%' . $filters['search'] . '%')
                ->orWhere('sector', 'like', '%' . $filters['search'] . '%')
                ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
        }

        return $query
            ->orderBy($data['order_by'], $data['order_direction'])
            ->paginate($data['per_page'])
            ->onEachSide(1);
    }
}
