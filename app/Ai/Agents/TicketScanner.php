<?php

namespace App\Ai\Agents;

use App\Enums\Currency;
use App\Enums\ExpenseCategory;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

class TicketScanner implements Agent, Conversational, HasStructuredOutput, HasTools
{
    use Promptable;

    public function __construct(public ?Currency $currency = null) {}

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $currencyCode = $this->currency?->value ?? 'EUR';
        $currencySymbol = $this->currency?->symbol() ?? '€';

        return <<<PROMPT
        Eres un asistente experto en visión por computadora especializado en digitalizar tickets de compra y recibos de venta a partir de imágenes.

        Reglas estrictas de extracción:
        1. "store":
           - Extrae el nombre comercial o marca del establecimiento.
           - JAMÁS uses nombres de cajeros, meseros, empleados, usuarios o roles (como "Admin", "Cajero", "Mesero", "Atendió", "Le Atendió", "Vendedor", "Operador") como nombre del negocio.
           - JAMÁS uses metadatos como "Orden N°", "Mesa", "Personas", "Comprobante" o "Fecha" como nombre del negocio.
           - Si el nombre comercial del negocio no está claramente visible en el ticket, usa "Ticket".

        2. "category":
           - Debe ser EXACTAMENTE uno de estos valores: food, transportation, health, entertainment, subscriptions, beauty, clothing, home, education, pets, other.

        3. "items":
           - Busca la columna de importes a la derecha del ticket. Extrae todas las líneas o productos cuyo importe total sea estrictamente MAYOR A 0 (> 0).
           - Si un ítem muestra una cantidad (ej. "3 MENU ALMUERZO 19499,97"), extrae el nombre del producto (ej. "3x Menu Almuerzo") y su importe total impreso a la derecha.
           - Ignora completamente aquellos ítems o sublíneas cuyo importe sea 0.00 (por ejemplo, opciones, guarniciones o bebidas incluidas sin costo adicional dentro de un menú).
           - Convierte siempre cualquier coma decimal (",") a punto decimal (".") para devolver números válidos en el JSON.

        4. Identificación de Moneda y Conversión Obligatoria:
           - La moneda objetivo del usuario es $currencyCode ($currencySymbol).
           - Revisa el ticket para determinar la divisa en la que está impreso (símbolos $, USD, ARS, MXN, €, EUR, etc.).
           - REGLAS DE CONVERSIÓN ARITMÉTICA:
             * Si la moneda del ticket es Pesos (ARS/MXN/CLP) con cifras elevadas en $ (ejemplo: $19.499,97) y la moneda del usuario es EUR (€), convierte el importe aplicando un tipo de cambio realista de moneda local (ejemplo: 1000 ARS ≈ 0.90 EUR, por lo que $19.499,97 se convierte a aproximadamente 17.55 EUR).
             * Si la moneda del ticket es USD ($) con cifras estándar y la moneda del usuario es EUR (€): MULTIPLICA el precio por 0.92 para calcular su valor en EUR (€).
             * Si la moneda del ticket es EUR (€) y la moneda del usuario es USD ($): MULTIPLICA el precio por 1.09 para calcular su valor en USD ($).
             * Si la moneda del ticket coincide con la del usuario ($currencyCode), conserva los montos impresos sin multiplicar.
           - El campo "amount" DEBE ser una cifra numérica final positiva convertida a $currencyCode.

        5. Precisión:
           - No inventes productos ni montos que no estén presentes en el ticket.
        PROMPT;
    }

    public function schema(JsonSchema $schema): array
    {
        $currencyCode = $this->currency?->value ?? 'EUR';
        $currencySymbol = $this->currency?->symbol() ?? '€';

        return [
            'store' => $schema->string()->description('Nombre del negocio o comercio. Si no es claramente visible, usa "Ticket". NUNCA usar nombres de cajeros u empleados como "Admin".'),
            'category' => $schema->string()->enum(ExpenseCategory::cases())->description('Categoría del gasto'),
            'items' => $schema->array()->items(
                $schema->object([
                    'name' => $schema->string()->description('Nombre completo del producto'),
                    'amount' => $schema->number()->description("Precio final del producto mayor a 0 tras aplicar la conversión obligatoria a $currencyCode ($currencySymbol)"),
                ])->required()
            )->description("Lista de productos comprados con precio mayor a 0 y convertidos a $currencyCode"),
        ];
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
        return [];
    }
}
