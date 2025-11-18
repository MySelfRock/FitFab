<?php

namespace Database\Seeders;

use App\Models\Professional;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        User::create([
            'name' => 'Admin FitFab',
            'email' => 'admin@fitfab.com.br',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'admin',
            'profile' => [
                'phone' => '(11) 99999-9999',
            ],
        ]);

        // Regular users
        $user1 = User::create([
            'name' => 'João Silva',
            'email' => 'joao@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'user',
            'profile' => [
                'phone' => '(11) 98888-8888',
                'address' => [
                    'street' => 'Rua das Flores, 123',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zipcode' => '01234-567',
                ],
            ],
        ]);

        $user2 = User::create([
            'name' => 'Maria Santos',
            'email' => 'maria@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'user',
            'profile' => [
                'phone' => '(11) 97777-7777',
                'address' => [
                    'street' => 'Av. Paulista, 1000',
                    'city' => 'São Paulo',
                    'state' => 'SP',
                    'zipcode' => '01310-100',
                ],
            ],
        ]);

        // Professional users
        $pro1 = User::create([
            'name' => 'Carlos Marceneiro',
            'email' => 'carlos@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'professional',
            'profile' => [
                'phone' => '(11) 96666-6666',
            ],
        ]);

        Professional::create([
            'user_id' => $pro1->id,
            'business_name' => 'Marcenaria Carlos',
            'specialty' => 'Móveis Planejados',
            'description' => 'Especialista em móveis planejados para ambientes residenciais e comerciais. 15 anos de experiência.',
            'portfolio_url' => 'https://example.com/carlos',
            'lat' => -23.5505,
            'lng' => -46.6333,
            'service_radius_km' => 50,
            'rating' => 4.8,
            'total_reviews' => 42,
            'total_jobs' => 38,
            'certifications' => ['SENAI - Marcenaria', 'NR-12'],
            'equipment' => ['Serra Esquadrejadeira', 'Tupia', 'Lixadeira'],
            'verified' => true,
            'active' => true,
        ]);

        $pro2 = User::create([
            'name' => 'Ana Fabricante',
            'email' => 'ana@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'professional',
            'profile' => [
                'phone' => '(11) 95555-5555',
            ],
        ]);

        Professional::create([
            'user_id' => $pro2->id,
            'business_name' => 'AnaFab Móveis',
            'specialty' => 'Móveis Corporativos',
            'description' => 'Fabricação de móveis sob medida para escritórios e espaços comerciais. Qualidade e prazo garantidos.',
            'portfolio_url' => 'https://example.com/anafab',
            'lat' => -23.5629,
            'lng' => -46.6544,
            'service_radius_km' => 30,
            'rating' => 4.9,
            'total_reviews' => 67,
            'total_jobs' => 58,
            'certifications' => ['SENAI - Móveis', 'ISO 9001'],
            'equipment' => ['CNC Router', 'Serra Esquadrejadeira', 'Coladora de Bordas'],
            'verified' => true,
            'active' => true,
        ]);

        $pro3 = User::create([
            'name' => 'Pedro Montador',
            'email' => 'pedro@example.com',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
            'role' => 'professional',
            'profile' => [
                'phone' => '(11) 94444-4444',
            ],
        ]);

        Professional::create([
            'user_id' => $pro3->id,
            'business_name' => 'Pedro Móveis & Design',
            'specialty' => 'Design Personalizado',
            'description' => 'Criação e fabricação de móveis exclusivos com design personalizado. Atendimento consultivo.',
            'portfolio_url' => 'https://example.com/pedro',
            'lat' => -23.5475,
            'lng' => -46.6361,
            'service_radius_km' => 40,
            'rating' => 4.7,
            'total_reviews' => 28,
            'total_jobs' => 25,
            'certifications' => ['Design de Móveis - Belas Artes'],
            'equipment' => ['Serra Circular', 'Tupia Manual', 'Kit Ferramentas'],
            'verified' => false,
            'active' => true,
        ]);

        $this->command->info('Users and professionals seeded successfully!');
    }
}
