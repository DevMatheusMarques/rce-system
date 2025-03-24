<?php

namespace App\Services;

use App\Models\Purchase;
use App\Repositories\ProductRepository;
use App\Repositories\PurchaseItemRepository;
use App\Repositories\PurchaseRepository;
use App\Traits\HelperTrait;
use App\Traits\LogTrait;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;

class PurchaseService
{
    use HelperTrait;
    use LogTrait;
    protected PurchaseRepository $purchaseRepository;
    protected PurchaseItemRepository $purchaseItemRepository;
    protected ProductRepository $productRepository;

    public function __construct(
        PurchaseRepository     $purchaseRepository,
        PurchaseItemRepository $purchaseItemRepository,
        ProductRepository      $productRepository
    )
    {
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseItemRepository = $purchaseItemRepository;
        $this->productRepository = $productRepository;
    }

    public function getById(string $id)
    {
        //todo: make this method
    }

    public function exportPDF($purchaseId): array
    {
        try {
            $purchaseData = $this->purchaseRepository->getById($purchaseId);

            if ($purchaseData->status === 'refused') {
                throw new Exception('Não é possível gerar PDF para pedidos recusados.');
            }

            $data = [
                'purchaseData' => $purchaseData,
                'user' => auth()->user(),
                'generateAt' => Carbon::now()->format('d/m/Y H:i'),
                'expiresAt' => Carbon::now()->addDays(30)->format('d/m/Y H:i'),
            ];

            $pdf = PDF::loadView('documents.purchase', compact('data'));

            return [
                'success' => true,
                'message' => 'PDF Gerado, aguarde pelo download.',
                'pdf' => $pdf // Retorne o objeto PDF
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }


    public function store(array $data): array
    {
        try {

            DB::beginTransaction();

            $purchaseData = [
                'user_id' => $data['user_id'],
                'supplier_id' => $data['supplier_id'],
            ];

            $purchaseStored = $this->purchaseRepository->store($purchaseData);

            $this->addItemToPurchase($data['items'], $purchaseStored->id);

            if ($this->existsItemsRefused($purchaseStored->id)) {
                $this->purchaseRepository->updateById(['status' => 'refused'], $purchaseStored->id);
            }

            $this->logInfo('User {id} | create purchase {purchaseId} success',
                [
                    'id' => auth()->user()->id,
                    'purchaseId' => $purchaseStored->id,
                ]
            );

            DB::commit();
            return [
                'success' => true,
                'message' => 'Compra registrada com sucesso.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('User {id} | create purchase error | {messageError}',
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

    public function updateStatus(array $data): array
    {
        try {
            DB::beginTransaction();

            $purchaseData = $this->purchaseRepository->getById($data['id']);
            $purchaseItems = $this->purchaseItemRepository->getByAttribute('purchase_id', $purchaseData->id);

            $this->allowUpdateStatus($purchaseData->status, $data['status']);

            switch ($data['status']) {
                case 'approved':
                    $this->handleStatusToApproved($purchaseItems);
                    break;

                case 'refused':
                    break;

                case 'in_progress':
                    $this->handleStatusToInProgress($purchaseItems);
                    break;

                case 'completed':
                    $this->handleStatusToCompleted($purchaseItems);
                    break;

                case 'canceled':
                    $this->handleStatusToCanceled($purchaseItems);
                    break;
            }
            $this->purchaseRepository->updateById(['status' => $data['status']], $purchaseData->id);

            $this->logInfo('User {id} | updated status purchase {purchaseId} to status {status} success',
                [
                    'id' => auth()->user()->id,
                    'purchaseId' => $purchaseData->id,
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
            $this->logError('User {id} | create purchase error | {messageError}',
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

            $purchaseData = $this->purchaseRepository->getById($id);
            $status = $purchaseData->status;

            $this->allowUpdatePurchase($purchaseData);

            $collectionOld = $this->purchaseItemRepository->getByAttribute('purchase_id', $id);
            $arrayOld = $collectionOld->map(function ($item) {
                return collect($item)->only(['product_id', 'product_quantity']);
            });
            $arrayOld = $arrayOld->toArray();
            $result = $this->arraysAreEqual($arrayOld, $data['items']);

            if (!empty($result['delete'])) {
                foreach ($result['delete'] as $item) {
                    $this->purchaseItemRepository->deleteByPurchaseId($id, $item['product_id']);
                }

                if (!$this->existsItemsRefused($id)) {
                    $status = 'approved';
                }
            }
            if (!empty($result['store'])) {
                $this->addItemToPurchase($result['store'], $id);

                if ($this->existsItemsRefused($id)) {
                    $status = 'refused';
                }
            }

            $dataUpdated = [
                'status' => $status,
                'supplier_id' => $data['supplier_id'],
            ];

            $this->purchaseRepository->updateById($dataUpdated, $purchaseData->id);

            $this->logInfo('User {id} | update purchase {purchaseId} success', [
                'id' => auth()->user()->id,
                'purchaseId' => $id,
            ]);

            DB::commit();
            return [
                'success' => true,
                'message' => 'Compra atualizada com sucesso.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('User {id} | update purchase error | {messageError}',
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

    public function delete(string $id)
    {
        //todo: make this method
    }

    public function getAllWithValidation(array $data): array
    {
        try {
            $purchaseData = $this->purchaseRepository->getAllWithValidation($data);
            if (!$purchaseData->total() > 0) {
                return [
                    'success' => true,
                    'message' => 'Nenhum registro encontrado',
                    'model' => $purchaseData
                ];
            }

            return [
                'success' => true,
                'message' => 'Registros encontrados',
                'model' => $purchaseData
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'model' => null
            ];
        }

    }

    /**
     * @throws \Exception
     */
    public function allowUpdatePurchase(Purchase $purchase): bool
    {
        if (in_array($purchase->status, ['approved', 'refused'])) {
            return true;
        }
        throw new \Exception('Não é permitido atualizar um pedido com o status atual.');
    }

    private function addItemToPurchase(array $items, int $purchaseId): void
    {
        foreach ($items as $item) {
            $productData = $this->productRepository->getById($item['product_id']);

            $item['purchase_id'] = $purchaseId;
            $item['product_name'] = $productData->name;

            if ($productData->stock > $productData->minimum) {
                $item['status'] = 'refused';
            }
            $this->purchaseItemRepository->store($item);
        }
    }

    private function existsItemsRefused(string $purchaseId): ?bool
    {
        $purchaseItems = $this->purchaseItemRepository->getByAttribute('purchase_id', $purchaseId);
        foreach ($purchaseItems as $item) {
            if ($item->status === 'refused') {
                return true;
            }
        }
        return false;
    }

    private function handleStatusToApproved(Collection $purchaseItems): void
    {
        foreach ($purchaseItems as $item) {
            $this->purchaseItemRepository->updateById(['status' => 'approved'], $item->id);
        }
    }

    private function handleStatusToInProgress(Collection $purchaseItems): void
    {
        foreach ($purchaseItems as $item) {
            $productData = $this->productRepository->getById($item['product_id']);
            $quantity = $productData->processing + $item['product_quantity'];
            $this->productRepository->updateById(['processing' => $quantity], $item['product_id']);
        }
    }

    private function handleStatusToCanceled(Collection $purchaseItems): void
    {
        foreach ($purchaseItems as $item) {
            $productData = $this->productRepository->getById($item['product_id']);
            $quantity = $productData->processing - $item['product_quantity'];
            $this->productRepository->updateById(['processing' => $quantity], $item['product_id']);
        }
    }

    private function handleStatusToCompleted(Collection $purchaseItems): void
    {
        foreach ($purchaseItems as $item) {
            $productData = $this->productRepository->getById($item['product_id']);

            $quantity = $productData->processing - $item['product_quantity'];
            $this->productRepository->updateById([
                'processing' => $quantity,
                'stock' => $productData->stock + $item['product_quantity']
            ], $item['product_id']);
        }
    }

    /**
     * @throws \Exception
     */
    private function allowUpdateStatus(string $oldStatus, string $newStatus): void
    {
        switch ($oldStatus) {
            case 'approved':
                if (in_array($newStatus, ['canceled', 'refused', 'in_progress'])) {
                    return;
                }
                throw new \Exception('Status não permitido');

            case 'refused':
                if (in_array($newStatus, ['approved', 'canceled'])) {
                    return;
                }
                throw new \Exception('Pedido cancelado, aprove ou cancele');

            case 'in_progress':
                if (in_array($newStatus, ['completed', 'canceled'])) {
                    return;
                }
                throw new \Exception('Pedido em andamento, finalize ou cancele');

            case 'completed':
                throw new \Exception('Pedido finalizado não permite atualização de status');

            case 'canceled':
                throw new \Exception('Pedido cancelado não permite atualização de status');
        }
    }
}
