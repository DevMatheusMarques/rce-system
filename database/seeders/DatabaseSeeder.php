<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    protected \Faker\Generator $faker;

    public function __construct()
    {
        $this->faker = FakerFactory::create();
    }

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->generateUsersDefault();
        User::factory(10)->create();
        $this->generateProductsData();;
        $this->generateSupplierData();
        $this->generateOrders();
    }

    private function generateUsersDefault(): void
    {
        User::create([
            'name' => 'Jordan Douglas Rosa de Melo',
            'email' => 'jordan.melo@fatec.sp.gov.br',
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'level' => 'admin',
            'registration' => '00001',
            'sector' => 'TI',
            'phone' => '(18)99745-5265',
            'sidebar' => 'close-lock',
            'first_access' => false
        ]);

        User::create([
            'name' => 'Matheus Henrique Couto Marques',
            'email' => 'matheus.marques16@fatec.sp.gov.br',
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'level' => 'operator',
            'registration' => '00002',
            'sector' => 'Estoquista',
            'phone' => '(18)99153-2494',
        ]);
        User::create([
            'name' => 'Vanessa dos Anjos Borges',
            'email' => 'vanessa.borges2@fatec.sp.gov.br',
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'level' => 'admin',
            'registration' => '00003',
            'sector' => 'Administração',
            'phone' => '(18)99999-7777',
            'first_access' => false
        ]);
        User::create([
            'name' => 'Giovanna Angelica Ros Miola',
            'email' => 'giovana.miola@fatec.sp.gov.br',
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'level' => 'manager',
            'registration' => '00004',
            'sector' => 'Pesquisa e Desenvolvimento',
            'phone' => '(18)99999-7777',
            'first_access' => false
        ]);
        User::create([
            'name' => 'Marcelo Buscioli Tenorio',
            'email' => 'marcelo.tenorio@fatec.sp.gov.br',
            'status' => 'active',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'level' => 'manager',
            'registration' => '00005',
            'sector' => 'Gestão de Projetos',
            'phone' => '(18)99999-7777',
            'first_access' => false
        ]);

        $users = User::all();

        foreach ($users as $user) {
            $parsedName = explode(' ', $user->name);
            $firstName = $parsedName[0];

            $imgProfile = new File(public_path("assets/img/users/$firstName.jpg"));

            $absoluteFilePath = Storage::putFileAs("public/profile_pictures/{$user->id}", $imgProfile, 'image.png');
            $symbolicLink = Storage::url($absoluteFilePath);
            $user->update(['profile_picture_path' => $symbolicLink]);
        }
    }

    private function generateProductsData(): void
    {
        $jsonPath = resource_path('data/products-data.json');
        $jsonString = file_get_contents($jsonPath);
        $productData = json_decode($jsonString, true);

        foreach ($productData as $product) {
            $pictureName = str_replace(' ', '-', $product['name']);
            Product::create([
                'name' => $product['name'],
                'description' => $this->faker->text(50),
                'category' => $product['category'],
                'minimum' => $this->faker->numberBetween(1, 7),
                'stock' => $this->faker->numberBetween(2, 30),
                'processing' => 0,
                'picture_path' => '/assets/img/products/' . $pictureName . '.webp',
                'status' => $this->faker->randomElement(['active', 'active', 'inactive']), //active repetido para ter mais peso
            ]);
        }
    }

    private function generateOrders(): void
    {
        $usersActive = User::where('status', 'active')->get();
        $users = User::all();
        $products = Product::all();

        foreach ($usersActive as $user) {
            $date = Carbon::now();

            for ($i = 0; $i < 80; $i++) {
                $productQty = $this->faker->numberBetween(3, 9);
                $order = Order::create([
                    'internal_information' => $this->faker->sentence(),
                    'user_id' => $user->id,
                    'requester_user_id' => $users->random()->id,
                    'status' => 'completed',
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                for ($j = 0; $j < $productQty; $j++) {
                    $product = $products->random();

                    OrderItem::create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_quantity' => $this->faker->randomDigit(),
                        'order_id' => $order->id,
                        'status' => 'approved',
                        'created_at' => $date,
                        'updated_at' => $date,
                    ]);
                }
                $date->subDays(7);
            }
        }
    }


    private function generateSupplierData(): void
    {
        $jsonPath = resource_path('data/suppliers-data.json');
        $jsonString = file_get_contents($jsonPath);
        $supplierData = json_decode($jsonString, true);

        foreach ($supplierData as $supplier) {

            Supplier::create([
                'cnpj' => $supplier["cnpj"],
                'corporate_name' => $supplier["corporate_name"],
                'trade_name' => $supplier["trade_name"],
                'email' => $supplier["email"],
                'cep' => $supplier["cep"],
                'phone' => $supplier["phone"],
                'address_city' => $supplier["address_city"],
                'address_state' => $supplier["address_state"],
            ]);
        }
    }
}
