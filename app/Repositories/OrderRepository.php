<?php

namespace App\Repositories;

use App\Models\Order;
use Carbon\Carbon;

class OrderRepository
{

    protected Order $model;

    public function __construct(Order $order)
    {
        $this->model = $order;
    }

    public function getOrderItems(string $id): \Illuminate\Database\Eloquent\Collection|array|\LaravelIdea\Helper\App\Models\_IH_OrderItem_C
    {
        $order = $this->getById($id);
        return $order->orderItems()->get();
    }

    public function getById(string $id): Order|array|\LaravelIdea\Helper\App\Models\_IH_Order_C
    {
        return $this->model->findOrFail($id);
    }

    public function all(): \Illuminate\Database\Eloquent\Collection|array|\LaravelIdea\Helper\App\Models\_IH_Order_C
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

    public function delete(string $id): ?bool
    {
        return $this->model->where('id', $id)
            ->delete();
    }

    public function getSectorComparison(array $data): array
    {
        $data['start_at'] = $data['start_at'] ?? Carbon::now()->subMonths(3)->startOfMonth()->format('Y-m');
        $data['end_at'] = $data['end_at'] ?? Carbon::now()->endOfMonth()->format('Y-m');
        $category = $data['category'] ?? 'all';

        $startAt = Carbon::parse($data['start_at'])->startOfMonth();
        $endAt = Carbon::parse($data['end_at'])->endOfMonth();

        $query = $this->model->query();
        $query->whereBetween('orders.created_at', [$startAt, $endAt])
            ->join('users', 'orders.requester_user_id', '=', 'users.id')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id');

        if ($category !== 'all') {
            $query->where('products.category', $category);
        }

        $mostSectorUsage = $query
            ->where('orders.status', 'completed')
            ->selectRaw('
                users.sector,
                SUM(order_items.product_quantity) as total_consumption')
            ->groupBy('users.sector')
            ->orderByDesc('total_consumption')
            ->get();

        return [
            'sectors_usages' => $mostSectorUsage,
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
            'category' => $category,
        ];
    }
    public function getTopProductsAndUsers(array $data): array
    {
        $data['start_at'] = $data['start_at'] ?? Carbon::now()->subMonths(3)->startOfMonth()->format('Y-m');
        $data['end_at'] = $data['end_at'] ?? Carbon::now()->endOfMonth()->format('Y-m');

        $startAt = Carbon::parse($data['start_at'])->startOfMonth();
        $endAt = Carbon::parse($data['end_at'])->endOfMonth();

        $query = $this->model->query();

        return [
            'count_orders' => $query->whereBetween('orders.created_at', [$startAt, $endAt])->count(),

            'most_category_usage' => $this->model->whereBetween('orders.created_at', [$startAt, $endAt])
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->where('orders.status', 'completed')
                    ->selectRaw('products.category, COUNT(order_items.id) as total')
                    ->groupBy('products.category')
                    ->orderByDesc('total')
                    ->first()->category ?? null, // Retorna a categoria mais usada no período

            'ranking_requester' => $this->model->whereBetween('orders.created_at', [$startAt, $endAt])
                ->join('users', 'orders.requester_user_id', '=', 'users.id')
                ->where('orders.status', 'completed')
                ->selectRaw('users.id, users.name, COUNT(orders.id) as total')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->take(3)
                ->get(), // Retorna os 3 maiores solicitantes (ID e nome)

            'ranking_product' => $this->model->whereBetween('orders.created_at', [$startAt, $endAt])
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->where('orders.status', 'completed')
                ->selectRaw('products.id, products.name, COUNT(order_items.id) as total')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total')
                ->take(3)
                ->get(), // Retorna os 3 produtos mais usados (ID e nome)
            'start_at' => $data['start_at'],
            'end_at' => $data['end_at'],
        ];
    }


    public function getProductUsageStatistics_old(array $data)
    {
        $data['start_at'] = '2023-01';
        $data['end_at'] = '2023-12';

        $query = $this->model->query();
        $startAt = Carbon::parse($data['start_at'])->startOfMonth();
        $endAt = Carbon::parse($data['end_at'])->endOfMonth();

        return [
            'count_orders' => $query->whereBetween('orders.created_at', [$startAt, $endAt])->count(),

            'most_category_usage' => $this->model->whereBetween('orders.created_at', [$startAt, $endAt])
                    ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'order_items.product_id', '=', 'products.id')
                    ->selectRaw('products.category, COUNT(order_items.id) as total')
                    ->groupBy('products.category')
                    ->orderByDesc('total')
                    ->first()->category ?? null, // Retorna a categoria mais usada no período

            'ranking_requester' => $this->model->whereBetween('orders.created_at', [$startAt, $endAt])
                ->join('users', 'orders.requester_user_id', '=', 'users.id')
                ->selectRaw('users.id, users.name, COUNT(orders.id) as total')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->take(3)
                ->get(), // Retorna os 3 maiores solicitantes (ID e nome)

            'ranking_product' => $this->model->whereBetween('orders.created_at', [$startAt, $endAt])
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->selectRaw('products.id, products.name, COUNT(order_items.id) as total')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total')
                ->take(3)
                ->get(), // Retorna os 3 produtos mais usados (ID e nome)
        ];
    }


    public function getAllWithValidation(array $data): array|\LaravelIdea\Helper\App\Models\_IH_Order_C|\Illuminate\Pagination\LengthAwarePaginator
    {

        $query = $this->model->query();
        $query
            ->withCount('orderItems')
            ->with('orderItems')
            ->with(['user:id,name', 'requesterUser:id,name,email,phone,sector']);

        if (isset($data['filters'])) {
            $filters = $data['filters'];

            if (isset($filters['status'])) {
                $query->where('status', '=', $filters['status']);
            }

            if (isset($filters['created_at'])) {
                $query->where('created_at', 'like', $filters['created_at'] . '%');
            }

            if (isset($filters['search'])) {
                $search = $filters['search'];
                $query
                    ->where('id', 'like', '%' . $search . '%')
                    ->orWhere('status', $search);

                $query->orWhereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
                $query->orWhereHas('requesterUser', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            }
        }
        if ($data['order_by'] === 'user_name') {
            $query
                ->join('users', 'users.id', '=', 'orders.user_id')
                ->orderBy('users.name', $data['order_direction']);
        } else {
            $query->orderBy($data['order_by'], $data['order_direction']);
        }

        return $query
            ->paginate($data['per_page'])
            ->onEachSide(1);
    }
}
