<?php

namespace App\Ai\Tools;

use App\Enums\ExpenseCategory;
use App\Models\Budget;
use App\Models\Expense;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AddExpense implements Tool
{
    public function __construct(
        public int $budgetId,
    ) {}

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Agrega un nuevo gasto al presupuesto actual. Debes proporcionar el nombre del gasto, el monto y opcionalmente la categoría. Las categorías válidas son: food, transportation, health, entertainment, subscriptions, beauty, clothing, home, education, pets, other.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $name = trim((string) ($request['name'] ?? ''));
        $rawAmount = $request['amount'] ?? null;

        $invalidNames = ['?', '??', '???', 'gasto', 'sin nombre', 'desconocido', 'unnamed', 'none', 'null', 'undefined', 'varios'];
        if (empty($name) || strlen($name) < 2 || in_array(strtolower($name), $invalidNames)) {
            return '[EXPENSE_ERROR] Se requiere un concepto o nombre específico para el gasto (ej: Gasolina, Uber, Almuerzo). Por favor no inventes nombres ni utilices signos de interrogación; pregúntale al usuario qué compró o pagó.';
        }

        if (! is_numeric($rawAmount) || (float) $rawAmount <= 0) {
            return '[EXPENSE_ERROR] El monto debe ser un número positivo mayor que cero.';
        }

        $amount = (float) $rawAmount;

        // Security: Validate budget ownership
        $budget = Budget::where('user_id', auth()->id())->find($this->budgetId);
        if (! $budget) {
            return '[EXPENSE_ERROR] No tienes autorización sobre este presupuesto o el presupuesto no existe.';
        }

        $symbol = $budget->user?->currency?->symbol() ?? '€';

        // Business rule: Check if amount exceeds remaining budget balance
        $currentSpent = (float) $budget->expenses()->sum('amount');
        $remainingBalance = (float) $budget->amount - $currentSpent;

        if ($amount > $remainingBalance) {
            $formattedRemaining = number_format(max(0, $remainingBalance), 2);
            $formattedAmount = number_format($amount, 2);

            return "[EXPENSE_ERROR] El monto de $formattedAmount$symbol excede el saldo disponible en este presupuesto ($formattedRemaining$symbol). No se realizó ningún cargo.";
        }

        // Validate and map category enum
        $categoryInput = strtolower(trim((string) ($request['category'] ?? '')));
        $categoryEnum = ! empty($categoryInput) ? ExpenseCategory::tryFrom($categoryInput) : null;

        if (! $categoryEnum) {
            $categoryEnum = ExpenseCategory::Other;
        }

        /** @var Expense $expense */
        $expense = Expense::create([
            'budget_id' => $budget->id,
            'name' => $name,
            'amount' => $amount,
            'category' => $categoryEnum,
        ]);

        $catLabel = $expense->categoryLabel();

        return "[EXPENSE_CREATED] Gasto agregado exitosamente: $expense->name por $expense->amount$symbol ($catLabel)";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'name' => $schema->string()->description('Nombre o concepto específico del gasto (ej: Gasolina, Uber, Factura luz). NUNCA uses signos de interrogación ("?") ni nombres genéricos. Si el usuario no especificó el concepto concreto, no invoques esta herramienta y pregúntale al usuario primero.')->required(),
            'amount' => $schema->number()->description('Monto del gasto en valor numérico positivo mayor a 0 (ej: 45.50)')->required(),
            'category' => $schema->string()->description('Categoría del gasto. Valores permitidos: food, transportation, health, entertainment, subscriptions, beauty, clothing, home, education, pets, other.'),
        ];
    }
}
