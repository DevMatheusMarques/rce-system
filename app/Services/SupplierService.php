<?php

namespace App\Services;

use App\Repositories\SupplierRepository;
use App\Services\ExternalApi\FindCnpjService;
use App\Traits\LogTrait;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SupplierService
{
    use LogTrait;
    protected SupplierRepository $supplierRepository;

    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }

    public function getById(string $id): array
    {
        try {
            $supplierData = $this->supplierRepository->getById($id);
            return [
                'success' => true,
                'message' => 'Fornecedor encontrado.',
                'model' => $supplierData
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
            $successData = $this->supplierRepository->store($data);

            $this->logInfo('User {id} | create supplier {supplierId} success',
                [
                    'id' => auth()->user()->id,
                    'supplierId' => $successData,
                ]
            );

            return [
                'success' => true,
                'message' => 'Fornecedor cadastrado com sucesso.',
                'model' => $successData
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | create supplier {supplierId} error | {messageError}',
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

    public function updateById(array $data, string $id): array
    {
        try {
            $this->supplierRepository->getById($id);
            $result = $this->supplierRepository->updateById($data, $id);

            if (!$result) {
                throw new Exception('Falha na atualização.');
            }

            $this->logInfo('User {id} | update supplier {supplierId} success',
                [
                    'id' => auth()->user()->id,
                    'supplierId' => $id,
                ]
            );

            return [
                'success' => true,
                'message' => 'Fornecedor atualizado com sucesso',
                'model' => $this->supplierRepository->getById($id)
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | update supplier {supplierId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'messageError' => $e->getMessage(),
                    'supplierId' => $id,
                ]
            );
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
            $supplierData = $this->supplierRepository->getById($id);
            $result = $this->supplierRepository->delete($id);

            if (!$result) {
                throw new Exception('Falha ao excluir produto.');
            }

            $this->logInfo('User {id} | delete supplier {supplier} success',
                [
                    'id' => auth()->user()->id,
                    'supplier' => $supplierData,
                ]
            );

            return [
                'success' => true,
                'message' => 'Fornecedor excluido com sucesso.',
                'model' => null
            ];

        } catch (Exception $e) {
            $this->logError('User {id} | delete supplier {supplierId} error | {messageError}',
                [
                    'id' => auth()->user()->id,
                    'supplierId' => $id,
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

    public function getAllWithValidation(array $data): array
    {
        try {
            $supplierData = $this->supplierRepository->getAllWithValidation($data);
            if ($supplierData->total() > 0) {
                return [
                    'success' => true,
                    'message' => 'Registros encontrados',
                    'model' => $supplierData
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
