<?php

namespace Tests\Feature\Service;

use App\Models\Product;
use App\Models\User;
use App\Repositories\ProductRepository;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    protected ProductRepository $productRepository;
    protected ProductService $productService;

    public function setUp(): void
    {
        parent::setUp();

        $this->productRepository = Mockery::mock(ProductRepository::class);
        $this->productService = new ProductService($this->productRepository);

        $this->actingAs(User::first());
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_getbyid_product_service_success()
    {
        $uuid = Uuid::uuid4()->toString();
        $productData = Product::factory()->make();
        $productData['id'] = $uuid;

        $this->productRepository->shouldReceive('getById')
            ->once()
            ->with($uuid)
            ->andReturn($productData);

        $result = $this->productService->getById($uuid);

        $this->assertTrue($result['success']);
        $this->assertEquals('Produto encontrado.', $result['message']);
        $this->assertEquals($productData, $result['model']);
    }

    public function test_getbyid_product_service_failure()
    {
        $uuid = Uuid::uuid4()->toString();
        $productData = Product::factory()->make();
        $productData['id'] = $uuid;

        $this->productRepository->shouldReceive('getById')
            ->once()
            ->with($uuid)
            ->andThrow(new ModelNotFoundException('Produto não encontrado.'));

        $result = $this->productService->getById($uuid);

        $this->assertFalse($result['success']);
        $this->assertEquals('Produto não encontrado.', $result['message']);
        $this->assertNull($result['model']);
    }

    public function test_store_product_service_success()
    {
        $uuid = Uuid::uuid4()->toString();
        $data = Product::factory()->make()->toArray();

        $storedData = $data;
        $storedData['id'] = $uuid;

        $this->productRepository->shouldReceive('store')
            ->once()
            ->with($data)
            ->andReturn($storedData);

        $result = $this->productService->store($data);

        $this->assertTrue($result['success']);
        $this->assertEquals('Produto registrado com sucesso.', $result['message']);
        $this->assertEquals($storedData, $result['model']);
    }

    public function test_store_product_service_failure()
    {
        $data = Product::factory()->make()->toArray();

        $this->productRepository->shouldReceive('store')
            ->once()
            ->with($data)
            ->andThrow(new \Exception('Falha ao registrar produto.'));

        $result = $this->productService->store($data);

        $this->assertFalse($result['success']);
        $this->assertEquals('Falha ao registrar produto.', $result['message']);
        $this->assertNull($result['model']);
    }

    public function test_update_product_service_success()
    {
        $uuid = Uuid::uuid4()->toString();
        $productData = Product::factory()->make(['id' => $uuid]);

        $data = ['name' => 'nome atualizado'];

        $productExpect = clone $productData;
        $productExpect->name = 'nome atualizado';

        $this->productRepository->shouldReceive('updateById')
            ->once()
            ->with($data, $uuid)
            ->andReturn(true);

        $this->productRepository->shouldReceive('getById')
            ->once()
            ->with($uuid)
            ->andReturn($productExpect);

        $result = $this->productService->updateById($data, $uuid);

        $this->assertTrue($result['success']);
        $this->assertEquals('Produto alterado com sucesso', $result['message']);
        $this->assertEquals($productExpect, $result['model']);
    }

    public function test_update_product_service_failure_by_uuid_wrong()
    {
        $uuid = Uuid::uuid4()->toString();
        $productData = Product::factory()->make(['id' => $uuid]);

        $uuidWrong = Uuid::uuid4()->toString();
        $data = ['name' => 'nome atualizado'];

        $this->productRepository->shouldReceive('updateById')
            ->once()
            ->with($data, $uuidWrong)
            ->andReturn(false);

        $this->productRepository->shouldReceive('getById')
            ->never()
            ->with($uuidWrong)
            ->andThrow(new ModelNotFoundException('Produto não encontrado.'));

        $result = $this->productService->updateById($data, $uuidWrong);

        $this->assertFalse($result['success']);
        $this->assertEquals('Falha na atualização.', $result['message']);
        $this->assertNull($result['model']);
    }

    public function test_delete_product_service_success()
    {
        $uuid = Uuid::uuid4()->toString();
        $productData = Product::factory()->make(['id' => $uuid]);

        $this->productRepository->shouldReceive('getById')
            ->once()
            ->with($uuid)
            ->andReturn($productData);

        $this->productRepository->shouldReceive('delete')
            ->once()
            ->with($uuid)
            ->andReturn(true);

        $result = $this->productService->delete($uuid);

        $this->assertTrue($result['success']);
        $this->assertEquals('Produto excluido com sucesso.', $result['message']);
        $this->assertNull($result['model']);
    }

    public function test_delete_product_service_failure()
    {
        $uuid = Uuid::uuid4()->toString();
        $productData = Product::factory()->make(['id' => $uuid]);

        $this->productRepository->shouldReceive('getById')
            ->once()
            ->with($uuid)
            ->andReturn($productData);

        $this->productRepository->shouldReceive('delete')
            ->once()
            ->with($uuid)
            ->andReturn(false);

        $result = $this->productService->delete($uuid);

        $this->assertFalse($result['success']);
        $this->assertEquals('Falha ao excluir produto.', $result['message']);
        $this->assertNull($result['model']);
    }

    public function test_getAllWithValidation_success_records_found()
    {
        $items = ['product1', 'product2', 'product3'];
        $total = count($items);
        $perPage = 10;
        $currentPage = 1;

        $paginatedData = new LengthAwarePaginator($items, $total, $perPage, $currentPage);

        $this->productRepository->shouldReceive('getAllWithValidation')
            ->once()
            ->andReturn($paginatedData);

        $result = $this->productService->getAllWithValidation([]);

        $this->assertTrue($result['success']);
        $this->assertEquals('Registros encontrados', $result['message']);
        $this->assertInstanceOf(LengthAwarePaginator::class, $result['model']);
        $this->assertCount(3, $result['model']);
    }


    public function test_getAllWithValidation_failure_no_records_found()
    {
        $items = [];
        $total = count($items);
        $perPage = 10;
        $currentPage = 1;

        $paginatedData = new LengthAwarePaginator($items, $total, $perPage, $currentPage);

        $this->productRepository->shouldReceive('getAllWithValidation')
            ->once()
            ->andReturn($paginatedData);

        $result = $this->productService->getAllWithValidation([]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Nenhum registro encontrado', $result['message']);
        $this->assertEmpty($result['model']);
    }

    public function test_getAllWithValidation_failure_exception_thrown()
    {
        $this->productRepository->shouldReceive('getAllWithValidation')
            ->once()
            ->andThrow(new \Exception('Erro ao buscar registros'));

        $result = $this->productService->getAllWithValidation([]);

        $this->assertFalse($result['success']);
        $this->assertEquals('Erro ao buscar registros', $result['message']);
        $this->assertNull($result['model']);
    }

}
