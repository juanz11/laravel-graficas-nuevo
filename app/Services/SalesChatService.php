<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Natural-language chat over the sales table. The LLM proposes a read-only
 * SELECT, we validate it, run it, and ask the LLM to answer with the data.
 */
class SalesChatService
{
    public function __construct(private LlmClient $llm)
    {
    }

    /**
     * @return array{answer: string, sql: ?string, rows: ?array, source: string}
     */
    public function answer(string $question, array $history = []): array
    {
        if (! $this->llm->available()) {
            return [
                'answer' => 'Para usar el asistente configura `GEMINI_API_KEY` en el archivo `.env` (gratis en Google AI Studio).',
                'sql' => null,
                'rows' => null,
                'source' => 'none',
            ];
        }

        $historyText = collect($history)->take(-8)->map(
            fn ($m) => (($m['role'] ?? '') === 'user' ? 'Usuario' : 'Asistente') . ': ' . ($m['content'] ?? '')
        )->implode("\n");

        // Paso 1: generar la consulta SQL
        $sql = $this->generateSql($question, $historyText);

        // Paso 2: ejecutar si es válida
        $rows = null;
        $sqlError = null;
        if ($sql !== null) {
            try {
                $rows = array_map(fn ($r) => (array) $r, DB::select($sql));
                $rows = array_slice($rows, 0, 80);
            } catch (\Throwable $e) {
                $sqlError = $e->getMessage();
                Log::warning('SalesChatService: SQL rechazado por MySQL', ['sql' => $sql, 'error' => $sqlError]);
            }
        }

        // Paso 3: respuesta final en lenguaje natural
        return $this->composeAnswer($question, $historyText, $sql, $rows, $sqlError);
    }

    private function generateSql(string $question, string $historyText): ?string
    {
        $system = <<<'PROMPT'
Eres el generador de consultas del asistente de datos de SNC Pharma (ventas farmacéuticas). Tu ÚNICA salida es JSON.

TABLA DISPONIBLE: `sales` (una fila por producto vendido a un cliente en un mes)
- report_date DATE — primer día del mes del reporte (2026-10-01 = octubre 2026)
- exchange_rate DECIMAL — tasa de cambio del periodo; montos en divisas → USD = monto / exchange_rate
- client_code VARCHAR, client_name VARCHAR, client_class VARCHAR — cliente y clasificación comercial
- product_code VARCHAR, product_description VARCHAR
- quantity INT — unidades vendidas (negativo = nota de crédito/devolución)
- total_sales, total_cost, total_utility DECIMAL — montos en divisas del periodo
- utility_percentage DECIMAL — margen %
- is_manual TINYINT, is_improvised TINYINT, invoice_number VARCHAR

REGLAS DE NEGOCIO:
- Filas de descuento/ajuste: UPPER(product_description) LIKE '%DESCUENTO%' OR LIKE '%FONDO PROMOCIONAL%' OR quantity < 0 OR total_sales < 0.
- "Ventas USD" = SUM(CASE WHEN <condición descuento> THEN -ABS(total_sales) ELSE total_sales END / COALESCE(NULLIF(exchange_rate,0),1))
- "Unidades" = SUM(CASE WHEN <condición descuento> THEN 0 ELSE quantity END)
- "Mes" = DATE_FORMAT(report_date, '%Y-%m'). El año/mes de una pregunta se filtra con YEAR()/MONTH() o rangos de report_date.

Responde SOLO JSON: {"sql": "SELECT ..."} — o {"sql": null} si la pregunta no requiere datos (saludos, explicaciones) o no se puede responder con esta tabla.
PROMPT;

        $user = "Historial de conversación:\n{$historyText}\n\nPregunta actual: {$question}";

        $result = $this->llm->generate($system, $user, true);
        $spec = json_decode($result['text'] ?? '', true);
        $sql = $spec['sql'] ?? null;

        return is_string($sql) ? $this->validateSql($sql) : null;
    }

    private function composeAnswer(string $question, string $historyText, ?string $sql, ?array $rows, ?string $sqlError): array
    {
        $system = <<<'PROMPT'
Eres el asistente de datos de SNC Pharma. Respondes en español, de forma concisa y directa, usando los resultados de la consulta.
- Formatea montos como $ 1.234.567 y cantidades con separador de miles.
- Si la lista es larga, menciona los 5-10 más relevantes.
- Puedes usar markdown ligero (**negrita**, listas con -, saltos de línea). No uses tablas markdown.
- Si hubo un error en la consulta o no hay datos, explícalo en una frase y sugiere cómo reformular la pregunta.
- Nunca inventes cifras: solo lo que devuelven los resultados.
PROMPT;

        $dataText = match (true) {
            $sqlError !== null => "La consulta falló: {$sqlError}",
            $rows === null && $sql === null => 'No fue necesario consultar la base de datos.',
            empty($rows) => 'La consulta no devolvió filas.',
            default => json_encode($rows, JSON_UNESCAPED_UNICODE),
        };

        $user = "Pregunta del usuario: {$question}\n\nHistorial:\n{$historyText}\n\nConsulta ejecutada: " . ($sql ?? 'ninguna') . "\n\nResultados (JSON):\n{$dataText}";

        $result = $this->llm->generate($system, $user);

        return [
            'answer' => $result['text'] ?? 'No pude generar una respuesta en este momento. Intenta de nuevo.',
            'sql' => $sql,
            'rows' => $rows,
            'source' => $result['provider'] ?? 'none',
        ];
    }

    /**
     * Allow only a single read-only SELECT over the sales table.
     */
    private function validateSql(string $sql): ?string
    {
        $sql = trim($sql, " \t\n\r;`");

        if (! preg_match('/^(select|with)\b/i', $sql)) {
            return null;
        }
        if (str_contains($sql, ';')) {
            return null;
        }
        if (preg_match('/\b(insert|update|delete|drop|alter|truncate|create|replace|grant|revoke|exec|execute|call|attach|detach|pragma|load_file|outfile|dumpfile|into)\b/i', $sql)) {
            return null;
        }
        if (! preg_match('/from\s+[`"\[]?sales[`"\]]?/i', $sql)) {
            return null;
        }
        if (! preg_match('/\blimit\b/i', $sql)) {
            $sql .= ' LIMIT 80';
        }

        return $sql;
    }
}
