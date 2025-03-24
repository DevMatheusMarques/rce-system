<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static int $counter = 6; //deve ser 6, pois no seeder, é criado 5 usuários diretamente
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arrayEnumLevel = ['admin', 'manager', 'operator'];
        $arrayRandomSector = [
            'Administração',
            'Estoquista',
            'Auditoria Interna',
            'Compras',
            'Comunicação Corporativa',
            'Financeiro',
            'Gerência',
            'Gestão de Projetos',
            'Jurídico',
            'Logística',
            'Marketing',
            'Pesquisa e Desenvolvimento',
            'Planejamento Estratégico',
            'Produção',
            'QA',
            'RH - Recursos Humanos',
            'Segurança do Trabalho',
            'Serviço ao Cliente',
            'Sustentabilidade Social',
            'TI',
            'Vendas'
        ];

        $fakerName = $this->faker->unique()->firstName;
        $fakerNameLower = strtolower($fakerName);
        $currentLevel = $this->faker->randomElement($arrayEnumLevel);
        return [
            'name' => $fakerName,
            'email' => "{$fakerNameLower}.{$currentLevel}@example.com",
            'status' => $this->faker->randomElement(['active', 'inactive', 'inactive']), //inactive repetido para ter mais peso
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // password
            'remember_token' => Str::random(10),
            'level' => $currentLevel,
            'registration' => str_pad(static::$counter++, 5, '0', STR_PAD_LEFT),
            'sector' => $this->faker->randomElement($arrayRandomSector),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return $this
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
