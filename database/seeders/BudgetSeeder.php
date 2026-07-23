<?php

namespace Database\Seeders;

use App\Enums\BudgetType;
use App\Models\User;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            $users = collect([
                User::factory()->create(['name' => 'Andrés Podadera', 'email' => 'andres@example.com']),
                User::factory()->create(['name' => 'María García', 'email' => 'maria@example.com']),
                User::factory()->create(['name' => 'Carlos Rodríguez', 'email' => 'carlos@example.com']),
            ]);
        }

        $budgetsPerUser = [
            // User 1 (10 budgets)
            0 => [
                [
                    'name' => 'Compra Supermercado',
                    'amount' => 450.00,
                    'type' => BudgetType::General,
                    'description' => 'Presupuesto mensual para alimentos, víveres y productos de limpieza del hogar.',
                ],
                [
                    'name' => 'Vacaciones en Japón',
                    'amount' => 3500.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Ahorro programado para el viaje familiar de verano en Tokio y Kioto.',
                ],
                [
                    'name' => 'Fondo de Emergencia',
                    'amount' => 5000.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Reserva de dinero para imprevistos médicos y reparaciones del hogar.',
                ],
                [
                    'name' => 'Suscripciones y Streaming',
                    'amount' => 65.00,
                    'type' => BudgetType::General,
                    'description' => 'Pago mensual de plataformas digitales como Netflix, Spotify y GitHub.',
                ],
                [
                    'name' => 'Mantenimiento del Coche',
                    'amount' => 250.00,
                    'type' => BudgetType::General,
                    'description' => 'Cambio de aceite, revisión técnica periódica y seguro del vehículo.',
                ],
                [
                    'name' => 'Renovación de Portátil',
                    'amount' => 1800.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Ahorro proyectado para comprar la nueva estación de trabajo para desarrollo.',
                ],
                [
                    'name' => 'Gimnasio y Deporte',
                    'amount' => 50.00,
                    'type' => BudgetType::General,
                    'description' => 'Cuota mensual del centro deportivo y suplementación nutricional.',
                ],
                [
                    'name' => 'Cursos y Certificaciones',
                    'amount' => 450.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Fondo destinado a cursos de especialización en Laravel, AWS y arquitectura.',
                ],
                [
                    'name' => 'Cenas y Ocio',
                    'amount' => 200.00,
                    'type' => BudgetType::General,
                    'description' => 'Salidas de fin de semana, restaurantes y eventos culturales.',
                ],
                [
                    'name' => 'Seguro de Hogar y Salud',
                    'amount' => 120.00,
                    'type' => BudgetType::General,
                    'description' => 'Cobro mensual automatizado de la póliza de seguro médico y vivienda.',
                ],
            ],

            // User 2 (5 budgets)
            1 => [
                [
                    'name' => 'Reforma de la Cocina',
                    'amount' => 4200.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Presupuesto reservado para la reforma integral de muebles y electrodomésticos.',
                ],
                [
                    'name' => 'Alquiler de Vivienda',
                    'amount' => 850.00,
                    'type' => BudgetType::General,
                    'description' => 'Pago mensual del arrendamiento del piso.',
                ],
                [
                    'name' => 'Viaje a Roma',
                    'amount' => 1200.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Vuelos, alojamiento y excursiones para la escapada a Italia.',
                ],
                [
                    'name' => 'Facturas de Luz y Gas',
                    'amount' => 140.00,
                    'type' => BudgetType::General,
                    'description' => 'Suministros básicos mensuales de la vivienda.',
                ],
                [
                    'name' => 'Fondo para Libros y Lectura',
                    'amount' => 100.00,
                    'type' => BudgetType::General,
                    'description' => 'Compra de libros técnicos y novelas en formato digital.',
                ],
            ],

            // User 3 (4 budgets)
            2 => [
                [
                    'name' => 'Ahorro para Coche Nuevo',
                    'amount' => 8000.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Entrada para la compra de un vehículo híbrido.',
                ],
                [
                    'name' => 'Gastos Veterinarios Mascota',
                    'amount' => 180.00,
                    'type' => BudgetType::General,
                    'description' => 'Vacunas, revisiones periódicas y alimentación especial del perro.',
                ],
                [
                    'name' => 'Internet y Fibra Óptica',
                    'amount' => 45.00,
                    'type' => BudgetType::General,
                    'description' => 'Conexión a internet de alta velocidad y líneas móviles.',
                ],
                [
                    'name' => 'Regalos de Navidad',
                    'amount' => 500.00,
                    'type' => BudgetType::Goal,
                    'description' => 'Fondo de ahorro para compras navideñas y detalles familiares.',
                ],
            ],
        ];

        foreach ($users->values() as $index => $user) {
            // Assign corresponding budget set, or default set if index > 2
            $budgetsToSeed = $budgetsPerUser[$index % count($budgetsPerUser)];

            // Clear previous budgets to ensure clean separation
            $user->budgets()->forceDelete();

            foreach ($budgetsToSeed as $budgetData) {
                $user->budgets()->create($budgetData);
            }
        }
    }
}
