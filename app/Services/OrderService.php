<?php

namespace App\Services;

use App\Repositories\OrderItemRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Traits\HelperTrait;
use App\Traits\LogTrait;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderService
{
    use HelperTrait;
    use LogTrait;
    protected ProductRepository $productRepository;
    protected OrderItemRepository $orderItemRepository;
    protected UserRepository $userRepository;
    protected OrderRepository $orderRepository;

    public function __construct(
        OrderItemRepository $orderItemRepository,
        ProductRepository   $productRepository,
        OrderRepository     $orderRepository,
        UserRepository      $userRepository)
    {
        $this->orderItemRepository = $orderItemRepository;
        $this->productRepository = $productRepository;
        $this->orderRepository = $orderRepository;
        $this->userRepository = $userRepository;
    }

    public function store(array $data): array
    {
        try {
            DB::beginTransaction();

            $data['user_id'] = auth()->guard()->user()->id;
            $data['status'] = 'in_progress';

            $successData = $this->orderRepository->store($data);
            $this->addItemToOrder($data['items'], $successData->id);
            $proofPath = $this->handlePicture($data['proof_file'], $data['requester_user_id'], $successData->id);

            $this->orderRepository->updateById(['order_proof_path' => $proofPath], $successData->id);

            $this->logInfo('User {id} | create order {orderId} success',
                [
                    'id' => auth()->user()->id,
                    'orderId' => $successData['id'],
                ]
            );
            DB::commit();
            return [
                'success' => true,
                'message' => 'Pedido registrado com sucesso.',
                'model' => $successData
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | create order error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                ]
            );
            DB::rollBack();
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
            $userData = $this->orderRepository->getById($id);
            return [
                'success' => true,
                'message' => 'Pedido encontrado.',
                'model' => $userData
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    public function updateStatus(array $data): array
    {
        try {
            DB::beginTransaction();

            $orderData = $this->orderRepository->getById($data['id']);
            $this->allowUpdateStatus($orderData);

            $orderItems = $this->orderItemRepository->getByAttribute('order_id', $orderData->id);

            if ($data['status'] == 'canceled') {
                $this->handleStatusToCanceled($orderItems);
            } else if ($data['status'] == 'completed') {
                $this->handleStatusToCompleted($orderItems);
            }

            $this->orderRepository->updateById(['status' => $data['status']], $orderData->id);

            $this->logInfo('User {id} | updated status order {orderId} to status {status} success',
                [
                    'id' => auth()->user()->id,
                    'orderId' => $orderData->id,
                    'status' => $data['status']
                ]
            );

            DB::commit();
            return [
                'success' => true,
                'message' => 'Status do pedido atualizado.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('User {id} | create order error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

    }

    public function updateById(array $data, string $id): array
    {
        try {
            DB::beginTransaction();

            $orderData = $this->orderRepository->getById($id);
            $this->allowUpdateStatus($orderData);

            $arrayOld = $this->getItemsFiltered($id);
            $result = $this->arraysAreEqual($arrayOld, $data['items']);

            if (!empty($result['delete']) || !empty($result['store'])) {
                if (!isset($data['proof_file'])) {
                    throw new Exception('Ao atualizar itens, um novo comprovante é obrigatório');
                }
            }
            if (isset($data['proof_file'])) {
                $data['order_proof_path'] = $this->handlePicture($data['proof_file'], $orderData->requester_user_id, $orderData->id);
            }

            if (!empty($result['delete'])) {
                $this->handleStatusToCanceled(collect($result['delete']));
                foreach ($result['delete'] as $item) {
                    $this->orderItemRepository->deleteByOrderId($id, $item['product_id']);
                }
            }
            if (!empty($result['store'])) {
                $this->addItemToOrder($result['store'], $id);
            }

            unset($data['items'], $data['proof_file']);
            $this->orderRepository->updateById($data, $orderData->id);

            $this->logInfo('User {id} | update order {orderId} success', [
                'id' => auth()->user()->id,
                'orderId' => $id,
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Pedido atualizado com sucesso.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('User {id} | update order error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                ]
            );
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function delete(string $id): array
    {
        try {
            $orderData = $this->orderRepository->delete($id);

            $this->logInfo('User {id} | delete order {order} success',
                [
                    'id' => auth()->user()->id,
                    'order' => $orderData,
                ]
            );
            return [
                'success' => true,
                'message' => 'Pedido excluído com sucesso.',
                'model' => null
            ];
        } catch (Exception $e) {
            $this->logError('User {id} | delete order {orderId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'orderId' => $id,
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
            $orderData = $this->orderRepository->getAllWithValidation($data);
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
    public function getSectorComparison(array $data): array
    {
        try {
            $orderData = $this->orderRepository->getSectorComparison($data);
            $message = count($orderData['sectors_usages']) === 0 ? 'Nenhum registro encontrado' : 'Registros encontrados';

            return [
                'success' => true,
                'message' => $message,
                'model' => count($orderData['sectors_usages']) === 0 ? null : $orderData
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }
    public function getTopProductsAndUsers(array $data): array
    {
        try {
            $orderData = $this->orderRepository->getTopProductsAndUsers($data);
            $message = $orderData['count_orders'] === 0 ? 'Nenhum registro encontrado' : 'Registros encontrados';

            return [
                'success' => true,
                'message' => $message,
                'model' => $orderData['count_orders'] === 0 ? null : $orderData
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }
    }

    private function handlePicture($file, $requesterId, $orderId): ?string
    {
        $filePath = "public/proof_files/requester_$requesterId/order_$orderId";
        $files = Storage::allFiles($filePath);

        Storage::delete($files);
        $absoluteFilePath = $file->storeAs($filePath, 'comprovante-' . strtolower(\Str::random(3)) . '.' . $file->getClientOriginalExtension());

        return Storage::url($absoluteFilePath);
    }

    private function addItemToOrder(array $items, string $orderId): void
    {
        foreach ($items as $item) {
            $productData = $this->productRepository->getById($item['product_id']);

            $item['order_id'] = $orderId;
            $item['product_name'] = $productData->name;

            if ($item['product_quantity'] > $productData->stock - $productData->reserved) {
                throw new Exception("O produto '{$productData->name}' não possui estoque disponível ou está reservado.");
            }
            $this->orderItemRepository->store($item);
        }
        $orderItems = $this->orderItemRepository->getByAttribute('order_id', $orderId);
        $this->handleNewItemsAdded($orderItems);
    }

    private function handleNewItemsAdded(Collection $items): void
    {
        foreach ($items as $item) {
            $productData = $this->productRepository->getById($item['product_id']);
            $quantity = $productData->reserved + $item['product_quantity'];
            $this->productRepository->updateById(['reserved' => $quantity], $item['product_id']);
        }
    }

    private function handleStatusToCanceled(Collection $orderItems): void
    {
        foreach ($orderItems as $item) {
            $productData = $this->productRepository->getById($item['product_id']);
            $quantity = $productData->reserved - $item['product_quantity'];

            $this->productRepository->updateById(['reserved' => $quantity], $item['product_id']);
        }
    }

    private function handleStatusToCompleted(Collection $orderItems): void
    {
        foreach ($orderItems as $item) {
            $productData = $this->productRepository->getById($item['product_id']);

            $this->productRepository->updateById([
                'reserved' => $productData->reserved - $item['product_quantity'],
                'stock' => $productData->stock - $item['product_quantity']
            ], $item['product_id']);

        }
    }

    private function getItemsFiltered(string $id): array
    {
        $collectionOld = $this->orderItemRepository->getByAttribute('order_id', $id);
        $arrayOld = $collectionOld->map(function ($item) {
            return collect($item)->only(['product_id', 'product_quantity']);
        });
        return $arrayOld->toArray();
    }

    private function allowUpdateStatus($order): void
    {
        if ($order->status === 'completed' || $order->status === 'canceled') {
            throw new Exception('Pedido bloqueado para edição.');
        }

        $authUser = auth()->guard()->user();
        $authorOrder = $this->userRepository->getById($order->user_id);

        if ($authUser['level'] === 'manager' && $authorOrder['level'] === 'admin') {
            throw new Exception('Um usuário \'manager\' não pode editar pedidos de um \'admin\'');
        } elseif ($authUser['level'] === 'operator' && $order->user_id !== $authUser['id']) {
            throw new Exception('Você só pode alterar os seus próprios pedidos.');
        }
    }

}
