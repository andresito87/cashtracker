<?php

namespace App\Http\Controllers;

use App\Ai\Agents\BudgetAssistant;
use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BudgetChatController extends Controller
{
    public function store(Request $request, Budget $budget)
    {
        Gate::authorize('view', $budget);

        $messages = $request->input('messages', []);
        $lastMessages = collect($messages)->last();
        $prompt = collect(data_get($lastMessages, 'parts', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode(' ')
			?: data_get($lastMessages, 'content', '');

        if (blank($prompt)) {
            return response()->json(['error' => 'Empty prompt.'], 422);
        }

        $agent = app(BudgetAssistant::class);
        $agent->budgetId = $budget->id;
        $formattedBudgetAmount = $budget->formattedAmount();
        $agent->budgetContext = "Este presupuesto es de tipo '{$budget->type->value}' llamado '$budget->name' con un monto total de $formattedBudgetAmount. Los gastos tienen nombre, monto y categoría.";

        return $agent->stream(
            $prompt,
            provider: config('ai.chat.provider', 'openrouter'),
            model: config('ai.chat.model', 'openrouter/free'),
        )->usingVercelDataProtocol();
    }
}
