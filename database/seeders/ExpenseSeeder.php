<?php

namespace Database\Seeders;

use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $expensesByBudgetName = [
            // User 1 Budgets (Andrés Podadera)
            'Compra Supermercado' => [ // Budget = 450.00
                ['name' => 'Mercadona compra semanal', 'amount' => 124.50, 'category' => ExpenseCategory::Food->value],
                ['name' => 'Carrefour víveres', 'amount' => 85.20, 'category' => ExpenseCategory::Food->value],
                ['name' => 'Frutería de barrio', 'amount' => 23.40, 'category' => ExpenseCategory::Food->value],
                ['name' => 'Carnicería tradicional', 'amount' => 42.80, 'category' => ExpenseCategory::Food->value],
                ['name' => 'Productos de limpieza', 'amount' => 35.60, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Lidl compra mensual', 'amount' => 95.10, 'category' => ExpenseCategory::Food->value],
                ['name' => 'Panadería y repostería', 'amount' => 14.50, 'category' => ExpenseCategory::Food->value],
            ],
            'Vacaciones en Japón' => [ // Budget = 3500.00
                ['name' => 'Billetes de avión Madrid-Tokio', 'amount' => 1150.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Reserva hotel Shinjuku', 'amount' => 680.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Japan Rail Pass 7 días', 'amount' => 240.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Entradas Universal Studios Osaka', 'amount' => 110.00, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Seguro médico de viaje', 'amount' => 85.00, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Reserva ryokan en Kioto', 'amount' => 420.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Entrada museo teamLab Planets', 'amount' => 32.00, 'category' => ExpenseCategory::Entertainment->value],
            ],
            'Fondo de Emergencia' => [ // Budget = 5000.00
                ['name' => 'Reparación fuga fontanería', 'amount' => 185.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Consulta médica especialista', 'amount' => 120.00, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Sustitución batería coche', 'amount' => 145.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Medicamentos de urgencia', 'amount' => 64.50, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Reparación cristal ventana', 'amount' => 95.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Servicio cerrajería', 'amount' => 110.00, 'category' => ExpenseCategory::Home->value],
            ],
            'Suscripciones y Streaming' => [ // Budget = 65.00
                ['name' => 'Netflix Plan Premium 4K', 'amount' => 17.99, 'category' => ExpenseCategory::Subscriptions->value],
                ['name' => 'Spotify Family', 'amount' => 14.99, 'category' => ExpenseCategory::Subscriptions->value],
                ['name' => 'GitHub Pro', 'amount' => 4.00, 'category' => ExpenseCategory::Subscriptions->value],
                ['name' => 'iCloud+ 200GB', 'amount' => 2.99, 'category' => ExpenseCategory::Subscriptions->value],
                ['name' => 'Amazon Prime', 'amount' => 4.99, 'category' => ExpenseCategory::Subscriptions->value],
                ['name' => 'ChatGPT Plus', 'amount' => 20.00, 'category' => ExpenseCategory::Subscriptions->value],
            ],
            'Mantenimiento del Coche' => [ // Budget = 250.00
                ['name' => 'Cambio de aceite y filtro', 'amount' => 85.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Inspección técnica ITV', 'amount' => 48.50, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Líquido de frenos y limpia', 'amount' => 22.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Lavado completo', 'amount' => 25.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Sustitución bombilla faro', 'amount' => 18.50, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Reparación pinchazo neumático', 'amount' => 20.00, 'category' => ExpenseCategory::Transportation->value],
            ],
            'Renovación de Portátil' => [ // Budget = 1800.00
                ['name' => 'Fondo inicial MacBook Pro', 'amount' => 500.00, 'category' => ExpenseCategory::Other->value],
                ['name' => 'Adaptador USB-C multipuerto', 'amount' => 45.00, 'category' => ExpenseCategory::Other->value],
                ['name' => 'Funda protectora', 'amount' => 35.00, 'category' => ExpenseCategory::Other->value],
                ['name' => 'Soporte ergonómico', 'amount' => 40.00, 'category' => ExpenseCategory::Other->value],
                ['name' => 'Teclado mecánico', 'amount' => 110.00, 'category' => ExpenseCategory::Other->value],
                ['name' => 'Monitor externo 4K', 'amount' => 380.00, 'category' => ExpenseCategory::Other->value],
            ],
            'Gimnasio y Deporte' => [ // Budget = 50.00
                ['name' => 'Cuota mensual Gimnasio', 'amount' => 24.99, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Bebidas isotónicas', 'amount' => 8.50, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Toalla de microfibra', 'amount' => 6.50, 'category' => ExpenseCategory::Beauty->value],
                ['name' => 'Suplementación barritas', 'amount' => 5.00, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Cinta deportiva', 'amount' => 5.00, 'category' => ExpenseCategory::Clothing->value],
            ],
            'Cursos y Certificaciones' => [ // Budget = 450.00
                ['name' => 'Certificación AWS Solutions Architect', 'amount' => 140.00, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Suscripción Laracasts', 'amount' => 99.00, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Libro Refactoring UI', 'amount' => 39.00, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Workshop Vue & Nuxt', 'amount' => 75.00, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Curso Kubernetes Udemy', 'amount' => 14.99, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Licencia JetBrains', 'amount' => 82.01, 'category' => ExpenseCategory::Education->value],
            ],
            'Cenas y Ocio' => [ // Budget = 200.00
                ['name' => 'Cena en restaurante italiano', 'amount' => 54.00, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Entradas de cine y palomitas', 'amount' => 24.50, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Tapas fin de semana', 'amount' => 32.00, 'category' => ExpenseCategory::Food->value],
                ['name' => 'Concierto música en directo', 'amount' => 45.00, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Escape Room amigos', 'amount' => 22.00, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Cocktails en terraza', 'amount' => 22.50, 'category' => ExpenseCategory::Entertainment->value],
            ],
            'Seguro de Hogar y Salud' => [ // Budget = 120.00
                ['name' => 'Seguro médico privado', 'amount' => 55.00, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Seguro multiriesgo hogar', 'amount' => 32.50, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Copago odontológico', 'amount' => 12.50, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Revisión vista', 'amount' => 10.00, 'category' => ExpenseCategory::Health->value],
                ['name' => 'Farmacia tratamiento', 'amount' => 10.00, 'category' => ExpenseCategory::Health->value],
            ],

            // User 2 Budgets (María García)
            'Reforma de la Cocina' => [ // Budget = 4200.00
                ['name' => 'Encimera de granito', 'amount' => 1150.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Muebles de cocina a medida', 'amount' => 1850.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Placa de inducción', 'amount' => 380.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Grifería y fregadero inox', 'amount' => 145.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Pintura antihumedad', 'amount' => 65.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Azulejos porcelánicos', 'amount' => 420.00, 'category' => ExpenseCategory::Home->value],
            ],
            'Alquiler de Vivienda' => [ // Budget = 850.00
                ['name' => 'Pago mensual alquiler', 'amount' => 750.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Gastos comunidad', 'amount' => 45.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Mantenimiento caldera', 'amount' => 15.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Seguro de impago', 'amount' => 28.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Tasa de basuras', 'amount' => 12.00, 'category' => ExpenseCategory::Home->value],
            ],
            'Viaje a Roma' => [ // Budget = 1200.00
                ['name' => 'Vuelos ida y vuelta', 'amount' => 210.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Hotel Trastevere 4 noches', 'amount' => 460.00, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Entradas Coliseo y Foro', 'amount' => 36.00, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Visita Museos Vaticanos', 'amount' => 65.00, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Tren Leonardo Express', 'amount' => 28.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Cena en Trattoria', 'amount' => 58.00, 'category' => ExpenseCategory::Food->value],
            ],
            'Facturas de Luz y Gas' => [ // Budget = 140.00
                ['name' => 'Factura electricidad', 'amount' => 72.40, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Factura gas natural', 'amount' => 48.10, 'category' => ExpenseCategory::Home->value],
                ['name' => 'Bombillas LED bajo consumo', 'amount' => 19.50, 'category' => ExpenseCategory::Home->value],
            ],
            'Fondo para Libros y Lectura' => [ // Budget = 100.00
                ['name' => 'Suscripción Kindle Unlimited', 'amount' => 9.99, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Novela ficción tapa dura', 'amount' => 22.50, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Libro diseño UX/UI', 'amount' => 34.00, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Funda e-reader Kindle', 'amount' => 15.00, 'category' => ExpenseCategory::Education->value],
                ['name' => 'Luz LED de lectura', 'amount' => 11.50, 'category' => ExpenseCategory::Education->value],
            ],

            // User 3 Budgets (Carlos Rodríguez)
            'Ahorro para Coche Nuevo' => [ // Budget = 8000.00
                ['name' => 'Señal reserva concesionario', 'amount' => 500.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Estudio tasación coche antiguo', 'amount' => 60.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Informe DGT historial', 'amount' => 20.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Fondo mensual entrada coche', 'amount' => 800.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Gestoría trámites', 'amount' => 150.00, 'category' => ExpenseCategory::Transportation->value],
                ['name' => 'Presupuesto seguro todo riesgo', 'amount' => 450.00, 'category' => ExpenseCategory::Transportation->value],
            ],
            'Gastos Veterinarios Mascota' => [ // Budget = 180.00
                ['name' => 'Consulta revisión anual', 'amount' => 45.00, 'category' => ExpenseCategory::Pets->value],
                ['name' => 'Vacuna trivalente y rabia', 'amount' => 38.00, 'category' => ExpenseCategory::Pets->value],
                ['name' => 'Pienso alta gama 12kg', 'amount' => 54.90, 'category' => ExpenseCategory::Pets->value],
                ['name' => 'Pastillas desparasitantes', 'amount' => 22.00, 'category' => ExpenseCategory::Pets->value],
                ['name' => 'Juguetes y chuches', 'amount' => 20.10, 'category' => ExpenseCategory::Pets->value],
            ],
            'Internet y Fibra Óptica' => [ // Budget = 45.00
                ['name' => 'Fibra 1Gbps + móvil 5G', 'amount' => 35.00, 'category' => ExpenseCategory::Subscriptions->value],
                ['name' => 'Bono datos roaming', 'amount' => 4.00, 'category' => ExpenseCategory::Subscriptions->value],
                ['name' => 'Cable ethernet Cat 7', 'amount' => 6.00, 'category' => ExpenseCategory::Home->value],
            ],
            'Regalos de Navidad' => [ // Budget = 500.00
                ['name' => 'Perfume para pareja', 'amount' => 85.00, 'category' => ExpenseCategory::Beauty->value],
                ['name' => 'Juegos de mesa familia', 'amount' => 45.00, 'category' => ExpenseCategory::Entertainment->value],
                ['name' => 'Smartwatch hermano', 'amount' => 130.00, 'category' => ExpenseCategory::Other->value],
                ['name' => 'Cesta de Navidad gourmet', 'amount' => 75.00, 'category' => ExpenseCategory::Food->value],
                ['name' => 'Papel y tarjetas de regalo', 'amount' => 14.50, 'category' => ExpenseCategory::Other->value],
                ['name' => 'Jersey lana navideño', 'amount' => 32.00, 'category' => ExpenseCategory::Clothing->value],
            ],
        ];

        $budgets = Budget::all();

        foreach ($budgets as $budget) {
            // Delete existing expenses for a clean re-seed
            $budget->expenses()->forceDelete();

            if (isset($expensesByBudgetName[$budget->name])) {
                $currentSpent = 0.0;
                foreach ($expensesByBudgetName[$budget->name] as $expenseData) {
                    if ($currentSpent + $expenseData['amount'] <= (float) $budget->amount) {
                        $budget->expenses()->create($expenseData);
                        $currentSpent += $expenseData['amount'];
                    }
                }
            } else {
                // Fallback: create random expenses strictly within budget capacity
                $remainingAmount = (float) $budget->amount;
                $count = min(5, max(1, (int) floor($remainingAmount / 20)));

                for ($i = 0; $i < $count; $i++) {
                    if ($remainingAmount <= 0.01) {
                        break;
                    }
                    $chunk = ($i === $count - 1) ? $remainingAmount : round(fake()->randomFloat(2, 5, $remainingAmount / 2), 2);
                    if ($chunk > 0) {
                        Expense::factory()->create([
                            'budget_id' => $budget->id,
                            'amount' => $chunk,
                        ]);
                        $remainingAmount -= $chunk;
                    }
                }
            }
        }
    }
}
