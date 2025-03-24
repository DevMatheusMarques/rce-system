<?php

namespace App\Services;

use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Traits\LogTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderItemService
{

    use LogTrait;
    protected OrderRepository $orderRepository;
    protected ProductRepository $productRepository;
    protected OrderItemRepository $orderItemRepository;

    public function __construct(OrderItemRepository $orderItemRepository,
                                ProductRepository   $productRepository,
                                OrderRepository     $orderRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
    }

    public function store(array $data): array
    {
        try {
            $this->checkOrderStatusForEditing($data['order_id']);

            $productData = $this->productRepository->getById($data['product_id']);
            $data['product_name'] = $productData['name'];

            $serviceResponse = $this->updateToStock($data['product_id'], $data['quantity_product']);

            if (!$serviceResponse['success']) {
                throw new Exception($serviceResponse['message']);
            }

            $successData = $this->orderItemRepository->store($data);

            $this->logInfo('User {id} | create orderItem {orderItemId} success',
                [
                    'id' => auth()->user()->id,
                    'orderItemId' => $successData['id'],
                ]
            );

            return [
                'success' => true,
                'message' => 'Item registrado com sucesso.',
                'model' => $successData
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | create orderItem error | {messageError}',
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

    public function getById(string $id): array
    {
        try {
            $orderItemData = $this->orderItemRepository->getById($id);
        } catch (ModelNotFoundException) {
            return ['success' => false, 'message' => 'Item não encontrado.'];
        }
        try {
            return [
                'success' => true,
                'message' => 'Item encontrado.',
                'model' => $orderItemData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            $orderItemData = $this->orderItemRepository->getById($id);
            $this->checkOrderStatusForEditing($orderItemData['order_id']);

            $productData = $this->productRepository->getById($orderItemData['product_id']);
            $currentQtyProduct = $productData->stock;

            $newQtyProduct = $orderItemData['quantity_product'] + $currentQtyProduct;

            $this->productRepository->updateById(['stock' => $newQtyProduct, 'processing' => 0], $orderItemData['product_id']);
            $result = $this->orderItemRepository->delete($id);

            if (!$result) {
                throw new Exception('Falha ao excluir produto.');
            }

            $this->logInfo('User {id} | delete orderItem {orderItemId} success',
                [
                    'id' => auth()->user()->id,
                    'orderItemId' => $id,
                ]
            );
            return [
                'success' => true,
                'message' => 'Item excluido com sucesso.',
                'model' => null
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | delete orderItem {orderItemId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'orderItemId' => $id,
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function getAllByOrderId(string $id, array $data): array
    {
        try {
            $this->orderRepository->getById($id);
            $orderItemData = $this->orderItemRepository->getAllByOrderId($id, $data['order_by'], $data['order_direction']);

            return [
                'success' => true,
                'message' => 'Registros encontrados.',
                'model' => $orderItemData
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }


    /**
     * @throws Exception
     */
    private function checkOrderStatusForEditing($orderId): void
    {
        $orderData = $this->orderRepository->getById($orderId);

        if ($orderData['status'] !== 'in_progress') {
            throw new Exception('Pedido bloqueado para edição, atualize-o para "em andamento" para continuar');
        }
    }

    private function updateToStock(string $productId, int $quantityProduct): array
    {
        try {
            $productData = $this->productRepository->getById($productId);

            if ($productData['stock'] < $quantityProduct) {
                throw new Exception('O produto não possui a quantidade suficiente para o pedido.');
            }
            $stockValue = $productData['stock'] - $quantityProduct;
            $processing = $productData['processing'] + ($quantityProduct * -1);
            $this->productRepository->updateById(['stock' => $stockValue, 'processing' => $processing], $productId);

            return [
                'success' => true,
                'message' => 'Quantidade de produto atualizado com sucesso.',
                'model' => $this->productRepository->getById($productId)
            ];

        } catch (Exception $e) {
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
            $orderData = $this->orderItemRepository->getAllWithValidation($data);
            if ($orderData->total() > 0) {
                return [
                    'success' => true,
                    'message' => 'Registros encontrados',
                    'model' => $orderData
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Nenhum registro encontrado',
                    'model' => []
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

}
