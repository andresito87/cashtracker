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

        set_time_limit(120);

        $messages = $request->input('messages', []);
        $lastMessages = collect($messages)->last();
        $prompt = collect(data_get($lastMessages, 'parts', []))
            ->where('type', 'text')
            ->pluck('text')
            ->implode(' ')
			?: data_get($lastMessages, 'content', '');

        $agent = app(BudgetAssistant::class);
        $agent->budgetId = $budget->id;
        $formattedBudgetAmount = $budget->formattedAmount();
        $agent->budgetContext = "Este presupuesto es de tipo '{$budget->type->value}' llamado '$budget->name' con un monto total de $formattedBudgetAmount. Los gastos tienen nombre, monto y categoría.";

        return $agent->stream(
            $prompt,
            provider: 'openrouter',
            model: 'openrouter/free', // Auto-router oficial de OpenRouter (selecciona dinámicamente el mejor gratuito con tools)
            // model: 'inclusionai/ling-3.0-flash:free', // Ling 3.0 Flash 124B (MoE optimizado para agentes)
            // model: 'poolside/laguna-s-2.1:free', // Laguna S 2.1 118B (Modelo de agentes de código de Poolside)
            // model: 'qwen/qwen-2.5-coder-32b-instruct:free', // Qwen 2.5 Coder 32B (Especializado en código y tools)
            // model: 'meta-llama/llama-3.1-8b-instruct:free', // Llama 3.1 8B Instruct (Gratuito con tools)
        )->usingVercelDataProtocol();

    }
}
