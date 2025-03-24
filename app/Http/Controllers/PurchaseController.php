<?php

namespace App\Http\Controllers;

use App\Http\Requests\Purchase\GetAllPurchaseRequest;
use App\Http\Requests\Purchase\RegisterPurchaseRequest;
use App\Http\Requests\Purchase\UpdatePurchaseRequest;
use App\Http\Requests\Purchase\UpdateStatusPurchaseRequest;
use App\Services\PurchaseService;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    public function exportPDF($purchaseId)
    {
        try {
            $serviceResponse = $this->purchaseService->exportPDF($purchaseId);

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }

            // Retorne o PDF para download
            return $serviceResponse['pdf']->download("Pedido de Compra - {$purchaseId}.pdf");

        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function register(RegisterPurchaseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['user_id'] = auth()->user()->id;
            $serviceResponse = $this->purchaseService->store($data);

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

    public function update(UpdatePurchaseRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->purchaseService->updateById($data, $id);

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

    public function getAll(GetAllPurchaseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->purchaseService->getAllWithValidation($data);

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
            $serviceResponse = $this->purchaseService->getById($id);

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
            $serviceResponse = $this->purchaseService->delete($id);

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
    public function updateStatus(UpdateStatusPurchaseRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->purchaseService->updateStatus($data);

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
}
