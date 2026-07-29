<?php

namespace App\Ai\Agents;

use App\Ai\Tools\AddExpense;
use App\Ai\Tools\SearchExpenses;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

#[MaxSteps(5)]
class BudgetAssistant implements Agent, Conversational, HasTools
{
    use Promptable;

    public int $budgetId = 0;

    public string $budgetContext = '';

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): string
    {
        return <<<PROMPT
				Eres un asistente financiero personal para un presupuesto específico.
				Tu función es responder preguntas sobre los gastos y también agregar nuevos gastos.
				{$this->budgetContext}
				Reglas para consultar gastos:
				- Si el usuario pregunta sobre gastos, montos, lo más caro, lo más barato, totales o cualquier consulta sobre su presupuesto, usa la herramienta SearchExpenses.
				Reglas para agregar gastos:
				- NUNCA invoques la herramienta AddExpense si el usuario NO ha proporcionado un concepto o nombre específico del gasto (ej: Gasolina, Uber, Almuerzo).
				- Por ejemplo, si el usuario dice "Añade un gasto en transporte de 50€", NO llames a la herramienta AddExpense. En su lugar, PREGÚNTALE PRIMERO al usuario: "¿En qué consistió exactamente este gasto de transporte? (ej. Gasolina, Uber, Metro, etc.)".
				- NUNCA inventes nombres genéricos como "?", "Gasto", "Transporte" ni utilices signos de interrogación como concepto.
				- Si el usuario indica el concepto concreto (ej: "Gasté 50€ en gasolina"), entonces SÍ usa la herramienta AddExpense deduciendo la categoría correspondiente.
				- Las categorías válidas son ÚNICAMENTE: food, transportation, health, entertainment, subscriptions, beauty, clothing, home, education, pets, other.
				Reglas generales:
				- Si el usuario pregunta algo que NO tiene que ver con sus gastos o presupuesto, responde amablemente que solo puedes ayudar con consultas sobre sus gastos.
				- Nunca inventes datos de gastos existentes. Solo responde con la información que devuelven las herramientas.
				- Responde siempre en el mismo idioma en el que te escriba el usuario. Si el usuario te saluda o pregunta en español, responde obligatoriamente en español. Por defecto, responde en español.
				- NUNCA incluyas tus razonamientos internos, pensamientos, análisis en inglés ni etiquetas especiales (<|end|>, <think>, etc.) en la respuesta. Responde únicamente con el mensaje final limpio.
				- IMPORTANTE: Cuando la herramienta AddExpense confirme que un gasto fue agregado, tu respuesta DEBE comenzar con [EXPENSE_CREATED]. Ejemplo: "[EXPENSE_CREATED] El gasto de Uber por $30 fue registrado en Transporte." Nunca omitas este prefijo cuando la operación sea exitosa.
				- Si la herramienta AddExpense devuelve [EXPENSE_ERROR] (por ejemplo, si supera el saldo disponible), NUNCA uses la etiqueta [EXPENSE_CREATED]. Explícale al usuario con amabilidad la razón del error reportado.
				PROMPT;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            new SearchExpenses(budgetId: $this->budgetId),
            new AddExpense(budgetId: $this->budgetId),
        ];
    }
}
