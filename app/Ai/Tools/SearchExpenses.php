<?php

namespace App\Ai\Tools;

use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchExpenses implements Tool
{
    public function __construct(
        public int $budgetId
    ) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Busca gastos del presupuesto actual. Puedes filtrar por nombre, categoría y ordenar por monto (más caro / más barato) o por fecha (más reciente / más antiguo).';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        // Security: Validate budget ownership
        $budget = Budget::where('user_id', auth()->id())->find($this->budgetId);
        if (! $budget) {
            return 'No se encontró el presupuesto o no tienes autorización para acceder a sus gastos.';
        }

        $query = Expense::where('budget_id', $budget->id);

        if (! empty($request['name'] ?? null)) {
            $query->where('name', 'ilike', '%'.trim((string) $request['name']).'%');
        }

        if (! empty($request['category'] ?? null)) {
            $query->where('category', 'ilike', '%'.trim((string) $request['category']).'%');
        }

        $sortBy = strtolower(trim((string) ($request['sort_by'] ?? '')));

        match ($sortBy) {
            'amount_desc' => $query->orderBy('amount', 'desc'),
            'amount_asc' => $query->orderBy('amount', 'asc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        // Take up to 30 items to prevent context window overflow
        $expenses = $query->take(30)->get(['name', 'amount', 'category', 'created_at']);

        if ($expenses->isEmpty()) {
            return 'No se encontraron gastos con los criterios de búsqueda especificados.';
        }

        $total = $expenses->sum('amount');
        $symbol = $budget->user?->currency?->symbol() ?? '€';

        return "Gastos encontrados ({$expenses->count()}):\n".
            $expenses->map(function (Expense $e) use ($symbol) {
                return "- $e->name: $e->amount$symbol ({$e->categoryLabel()})";
            })->implode("\n").
            "\n\nTotal acumulado de esta consulta: $total$symbol";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Texto para buscar en el nombre o concepto del gasto (ej: Comida, Uber, Netflix)'),
            'category' => $schema->string()->description('Texto o clave para filtrar por categoría (ej: food, transportation, health, entertainment, subscriptions, beauty, clothing, home, education, pets, other)'),
            'sort_by' => $schema->string()->description('Criterio de ordenamiento. Opciones: "amount_desc" (gastos más caros), "amount_asc" (gastos más baratos), "latest" (más recientes), "oldest" (más antiguos).'),
        ];
    }
}
