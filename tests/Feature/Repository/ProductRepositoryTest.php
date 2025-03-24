<?php

namespace Tests\Feature\Repository;

use App\Models\Product;
use App\Repositories\ProductRepository;
use Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected ProductRepository $productRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->productRepository = new ProductRepository(new Product());
    }

    public function test_store_product_repository_success()
    {
        $product = Product::factory()->make()->toArray();

        $storedProduct = $this->productRepository->store($product)->toArray();

        dd($product, $storedProduct);

        $this->assertDatabaseHas('products', $storedProduct);
    }
}
