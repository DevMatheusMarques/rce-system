<?php

namespace App\Services;

use App\Repositories\ProductRepository;
use App\Traits\LogTrait;
use DB;
use Exception;

class ProductService
{
    use LogTrait;
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function updateStatuses(array $data): array
    {
        try {

            $productIds = $data['product_ids'];

            DB::beginTransaction();
            foreach ($productIds as $productId) {
                $result = $this->updateById(['status' => $data['status']], $productId);

                if (!$result['success']) {
                    throw new Exception($result['message']);
                }
            }
            $count = count($productIds);
            $status = $data['status'] === 'active' ? 'Ativo' : 'Inativo';
            DB::commit();
            return [
                'success' => true,
                'message' => "$count itens definidos como $status.",
                'model' => $this->productRepository->countStatuses()
            ];
        } catch (Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage() . 'line: ' . $e->getLine(),
                'model' => null
            ];
        }
    }

    public function updateById(array $data, string $id): array
    {
        try {
            $result = $this->productRepository->updateById($data, $id);

            if (!$result) {
                throw new Exception('Falha na atualização.');
            }

            $this->logInfo('User {id} | update product {idProduct} success',
                [
                    'id' => auth()->user()->id,
                    'idProduct' => $id,
                ]
            );
            return [
                'success' => true,
                'message' => 'Produto alterado com sucesso',
                'model' => $this->productRepository->getById($id)
            ];
        } catch (Exception $e) {
            $this->logError('User {id} | update product {idProduct} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'idProduct' => $id,
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage() . 'line: ' . $e->getLine(),
                'model' => null
            ];
        }
    }

    public function handleProfilePicture(?string $picturePath, string $id): array
    {
        try {
            $result = $this->productRepository->updateById(['picture_path' => $picturePath], $id);

            if (!$result) {
                throw new Exception('Falha na atualização.');
            }
            $this->logInfo('User {id} | update product picture {productId} success',
                [
                    'id' => auth()->user()->id,
                    'productId' => $id,
                ]
            );

            return [
                'success' => true,
                'message' => 'Imagem do produto atualizada',
                'model' => $this->productRepository->getById($id)
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | update product picture {productId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'productId' => $id,
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function getById(string $id): array
    {
        try {
            $productData = $this->productRepository->getById($id);
            return [
                'success' => true,
                'message' => 'Produto encontrado.',
                'model' => $productData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function store(array $data): array
    {
        try {
            $successData = $this->productRepository->store($data);
            $this->logInfo('User {id} | create product {idProduct} success',
                [
                    'id' => auth()->user()->id,
                    'idProduct' => $successData['id'],
                ]
            );

            return [
                'success' => true,
                'message' => 'Produto registrado com sucesso.',
                'model' => $successData
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | create product error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function delete(array $data): array
    {
        try {
            $productIds = $data['product_ids'];
            foreach ($productIds as $id) {
                $productData = $this->productRepository->getById($id);

                $result = $this->productRepository->delete($id);

                if (!$result) {
                    throw new Exception('Falha ao excluir produto.');
                }
                $this->logInfo('User {id} | delete product {product} success',
                    [
                        'id' => auth()->user()->id,
                        'product' => $productData,
                    ]
                );
            }

            return [
                'success' => true,
                'message' => 'Produto(s) excluido(s) com sucesso.',
                'model' => ['count' => $this->productRepository->countStatuses()]
            ];
        } catch (Exception $e) {
            $this->logError('User {id} | delete products {idProducts} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'idProducts' => $productIds,
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function getAllWithValidation(array $data): array
    {
        try {
            $productData = $this->productRepository->getAllWithValidation($data);
            if (!$productData->total() > 0) {
                $productData['count'] = $this->countStatusesRelative($productData->items());
                return [
                    'success' => true,
                    'message' => 'Nenhum registro encontrado',
                    'model' => $productData
                ];
            }
            if (isset($data['filters']['search'])) {
                $productData['count'] = $this->countStatusesRelative($productData->items());
            } else {
                $productData['count'] = $this->productRepository->countStatuses();
            }
            return [
                'success' => true,
                'message' => 'Registros encontrados',
                'model' => $productData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    private function countStatusesRelative($data): array
    {
        $active = 0;
        foreach ($data as $item) {
            if ($item['status'] === 'active') {
                $active++;
            }
        }
        return [
            'active' => $active,
            'inactive' => count($data) - $active,
            'total' => count($data),
        ];
    }
}
