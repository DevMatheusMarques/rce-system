<?php

namespace App\Http\Controllers;

use App\Http\Requests\Supplier\GetAllSupplierRequest;
use App\Http\Requests\Supplier\RegisterSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Services\SupplierService;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    public function register(RegisterSupplierRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->supplierService->store($data);

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

    public function getAll(GetAllSupplierRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->supplierService->getAllWithValidation($data);

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
            $serviceResponse = $this->supplierService->getById($id);

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

    public function update(UpdateSupplierRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();

            if (empty($data)) {
                throw new \Exception('É necessário enviar pelo menos um valor.');
            }

            $serviceResponse = $this->supplierService->updateById($data, $id);

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

    public function delete(string $id): JsonResponse
    {
        try {
            $serviceResponse = $this->supplierService->delete($id);

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
