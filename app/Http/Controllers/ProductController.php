<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\DeleteProductRequest;
use App\Http\Requests\Product\GetAllProductRequest;
use App\Http\Requests\Product\RegisterProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Requests\Product\UpdateStatusProductsRequest;
use App\Http\Requests\StorePictureRequest;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function register(RegisterProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $serviceResponse = $this->productService->store($data);
            $message = $serviceResponse['message'];

            $idProduct = $serviceResponse['model']['id'];
            $symbolicLink = $this->handlePicture($request, $idProduct);

            if ($symbolicLink) {
                $serviceResponse = $this->productService->handleProfilePicture($symbolicLink, $idProduct);
            }

            if (!$serviceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $serviceResponse['message']
                ], 400);
            }
            return response()->json([
                'status' => 200,
                'message' => $message,
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

    private function handlePicture($request, $id)
    {
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');

            $filePath = "public/product_pictures/{$id}";

            $files = Storage::allFiles($filePath);
            Storage::delete($files);

            $absoluteFilePath = $file->storeAs($filePath, strtolower(\Str::random(3)) . '-image.' . $file->getClientOriginalExtension());

            $symbolicLink = Storage::url($absoluteFilePath);
        } else {
            $symbolicLink = null;
        }
        return $symbolicLink;
    }

    public function getAll(GetAllProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->productService->getAllWithValidation($data);
            $count = $serviceResponse['model']['count'];
            unset($serviceResponse['model']['count']);

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
                'count' => $count ?? null
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Erro interno do servidor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function addPicture(StorePictureRequest $request, $id): JsonResponse
    {
        try {
            $request->validated();
            $userId = auth()->user()->id;

            $symbolicLink = $this->handlePicture($request, $id);

            $serviceResponse = $this->productService->handleProfilePicture($symbolicLink, $userId);

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
            $serviceResponse = $this->productService->getById($id);

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

    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        try {
            $data = $request->validated();

            if (empty($data)) {
                throw new \Exception('É necessário enviar pelo menos um valor.');
            }
            $productServiceResponse = $this->productService->getById($id);
            if (!$productServiceResponse['success']) {
                return response()->json([
                    'status' => 400,
                    'message' => $productServiceResponse['message']
                ], 400);
            }

            if (array_key_exists('picture', $data)) {
                unset($data['picture']);
                $idProduct = $productServiceResponse['model']['id'];
                $data['picture_path'] = $this->handlePicture($request, $idProduct);
            }

            $serviceResponse = $this->productService->updateById($data, $id);

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

    public function delete(DeleteProductRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();
            $data['product_ids'] = array_unique($data['product_ids']);

            $serviceResponse = $this->productService->delete($data);

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

    public function updateStatuses(UpdateStatusProductsRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $serviceResponse = $this->productService->updateStatuses($data);

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
