<?php

namespace Database\Seeders;

use App\Models\Template;
use Illuminate\Database\Seeder;

class TemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'name' => 'Prateleira Simples',
                'slug' => 'prateleira-simples',
                'description' => 'Prateleira de parede simples com suportes',
                'default_options' => [
                    'width' => 1000,
                    'depth' => 250,
                    'shelves_count' => 3,
                ],
                'pieces_definition' => [
                    [
                        'name' => 'Prateleira',
                        'width' => 'width',
                        'height' => 'depth',
                        'thickness' => 15,
                        'qty' => 'shelves_count',
                        'edge_banding' => ['all'],
                    ],
                ],
                'active' => true,
            ],
            [
                'name' => 'Balcão de Atendimento',
                'slug' => 'balcao-atendimento',
                'description' => 'Balcão para atendimento com gavetas e compartimentos',
                'default_options' => [
                    'width' => 1800,
                    'height' => 900,
                    'depth' => 600,
                    'drawers' => 3,
                ],
                'pieces_definition' => [
                    [
                        'name' => 'Tampo',
                        'width' => 'width',
                        'height' => 'depth',
                        'thickness' => 18,
                        'qty' => 1,
                        'edge_banding' => ['all'],
                    ],
                    [
                        'name' => 'Lateral',
                        'width' => 'depth',
                        'height' => 'height - 18',
                        'thickness' => 15,
                        'qty' => 2,
                        'edge_banding' => ['top', 'bottom', 'front'],
                    ],
                    [
                        'name' => 'Prateleira Interna',
                        'width' => 'width - 30',
                        'height' => 'depth - 30',
                        'thickness' => 15,
                        'qty' => 2,
                        'edge_banding' => ['front'],
                    ],
                    [
                        'name' => 'Frente de Gaveta',
                        'width' => '(width - 40) / 3',
                        'height' => 150,
                        'thickness' => 18,
                        'qty' => 'drawers',
                        'edge_banding' => ['all'],
                    ],
                ],
                'active' => true,
            ],
            [
                'name' => 'Expositor de Produtos',
                'slug' => 'expositor-produtos',
                'description' => 'Expositor vertical para produtos com múltiplas prateleiras',
                'default_options' => [
                    'width' => 800,
                    'height' => 1800,
                    'depth' => 400,
                    'shelves' => 5,
                ],
                'pieces_definition' => [
                    [
                        'name' => 'Lateral',
                        'width' => 'depth',
                        'height' => 'height',
                        'thickness' => 15,
                        'qty' => 2,
                        'edge_banding' => ['top', 'front', 'bottom'],
                    ],
                    [
                        'name' => 'Prateleira',
                        'width' => 'width - 30',
                        'height' => 'depth',
                        'thickness' => 15,
                        'qty' => 'shelves',
                        'edge_banding' => ['front'],
                    ],
                    [
                        'name' => 'Fundo',
                        'width' => 'width',
                        'height' => 'height',
                        'thickness' => 6,
                        'qty' => 1,
                        'edge_banding' => [],
                    ],
                ],
                'active' => true,
            ],
            [
                'name' => 'Armário Planejado',
                'slug' => 'armario-planejado',
                'description' => 'Armário com portas e gavetas',
                'default_options' => [
                    'width' => 1200,
                    'height' => 2200,
                    'depth' => 600,
                    'doors' => 2,
                    'drawers' => 4,
                ],
                'pieces_definition' => [
                    [
                        'name' => 'Lateral Externa',
                        'width' => 'depth',
                        'height' => 'height',
                        'thickness' => 18,
                        'qty' => 2,
                        'edge_banding' => ['top', 'front', 'bottom'],
                    ],
                    [
                        'name' => 'Tampo',
                        'width' => 'width',
                        'height' => 'depth',
                        'thickness' => 18,
                        'qty' => 1,
                        'edge_banding' => ['front', 'left', 'right'],
                    ],
                    [
                        'name' => 'Base',
                        'width' => 'width - 36',
                        'height' => 'depth - 18',
                        'thickness' => 18,
                        'qty' => 1,
                        'edge_banding' => [],
                    ],
                    [
                        'name' => 'Porta',
                        'width' => '(width - 10) / doors',
                        'height' => 'height * 0.6',
                        'thickness' => 18,
                        'qty' => 'doors',
                        'edge_banding' => ['all'],
                    ],
                    [
                        'name' => 'Frente de Gaveta',
                        'width' => 'width - 40',
                        'height' => 150,
                        'thickness' => 18,
                        'qty' => 'drawers',
                        'edge_banding' => ['all'],
                    ],
                ],
                'active' => true,
            ],
        ];

        foreach ($templates as $template) {
            Template::create($template);
        }

        $this->command->info('Templates seeded successfully!');
    }
}
