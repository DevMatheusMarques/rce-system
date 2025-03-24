<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\GetAllOrderRequest;
use App\Http\Requests\Order\OrderSectorComparisonRequest;
use App\Http\Requests\Order\RegisterOrderRequest;
use App\Http\Requests\Order\UpdateOrderRequest;
use App\Http\Requests\Order\UpdateStatusOrderRequest;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function register(RegisterOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->orderService->store($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(UpdateStatusOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->orderService->updateStatus($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage() . 'line: ' . $e->getLine(),
            ], 500);
        }
    }

    public function update(UpdateOrderRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();

            if (empty($data)) {
                throw new \Exception('É necessário enviar pelo menos um valor.');
            }

            $serviceResponse = $this->orderService->updateById($data, $id);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAll(GetAllOrderRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->orderService->getAllWithValidation($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getById($id): JsonResponse
    {
        try {
            $serviceResponse = $this->orderService->getById($id);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getTopProductsAndUsers(OrderSectorComparisonRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->orderService->getTopProductsAndUsers($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function getSectorComparison(OrderSectorComparisonRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->orderService->getSectorComparison($data);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $serviceResponse['message'],
                'data' => $serviceResponse['model'],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
