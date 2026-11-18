<?php

namespace App\Services;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SalesForecaster
{
    public function __construct(private LlmClient $llm)
    {
    }

    /**
     * Monthly aggregated history: units sold (discount rows excluded) and
     * sales in USD, ordered ascending by period.
     */
    public function history(?string $clientCode = null, ?string $product = null, int $months = 24): Collection
    {
        $unitsSql = Sale::unitsExcludingDiscountsSql();
        $salesSql = Sale::signedAmountSql('total_sales') . ' / COALESCE(NULLIF(exchange_rate, 0), 1)';

        $query = Sale::query()
            ->selectRaw("DATE_FORMAT(report_date, '%Y-%m') as period")
            ->selectRaw("SUM({$unitsSql}) as units")
            ->selectRaw("SUM({$salesSql}) as sales_usd")
            ->groupBy('period')
            ->orderBy('period');

        if ($clientCode) {
            $query->where('client_code', $clientCode);
        }

        if ($product) {
            $query->where(function ($q) use ($product) {
                $q->where('product_code', 'like', '%' . $product . '%')
                    ->orWhere('product_description', 'like', '%' . $product . '%');
            });
        }

        $rows = $query->get()->keyBy('period');

        if ($rows->isEmpty()) {
            return collect();
        }

        // Fill gaps so months without sales count as 0
        $periods = collect();
        $cursor = Carbon::createFromFormat('Y-m', $rows->keys()->first())->startOfMonth();
        $end = Carbon::createFromFormat('Y-m', $rows->keys()->last())->startOfMonth();
        while ($cursor <= $end) {
            $key = $cursor->format('Y-m');
            $row = $rows->get($key);
            $periods->push([
                'period' => $key,
                'label' => ucfirst($cursor->translatedFormat('M Y')),
                'units' => (int) ($row->units ?? 0),
                'sales_usd' => round((float) ($row->sales_usd ?? 0), 2),
            ]);
            $cursor->addMonth();
        }

        return $periods->take(-$months)->values();
    }

    /**
     * Forecast the next $horizon months. Tries Gemini, then OpenAI, then
     * falls back to a local trend estimate when no key is configured.
     *
     * @return array{predictions: array, analysis: string, source: string}
     */
    public function forecast(Collection $history, int $horizon = 3, string $scopeLabel = 'ventas globales'): array
    {
        if ($history->count() < 2) {
            return [
                'predictions' => [],
                'analysis' => 'No hay suficiente historial para proyectar.',
                'source' => 'none',
            ];
        }

        $system = 'Eres un analista de ventas farmacéuticas. Proyectas ventas futuras a partir de series mensuales históricas, considerando tendencia y estacionalidad. Respondes SOLO con JSON válido.';

        $series = $history->map(fn ($r) => "{$r['period']}: {$r['units']} unidades, \${$r['sales_usd']} USD")->implode("\n");
        $lastPeriod = Carbon::createFromFormat('Y-m', $history->last()['period']);
        $nextPeriods = collect(range(1, $horizon))
            ->map(fn ($i) => $lastPeriod->copy()->addMonths($i)->format('Y-m'))
            ->implode(', ');

        $user = "Serie mensual histórica de {$scopeLabel} (las unidades excluyen filas de descuento/ajuste):\n{$series}\n\nProyecta los próximos periodos: {$nextPeriods}.\nResponde JSON con esta estructura exacta:\n{\"predictions\":[{\"period\":\"YYYY-MM\",\"units\":<entero>,\"sales_usd\":<numero>}],\"analysis\":\"<análisis breve en español, 2-4 oraciones, mencionando tendencia y riesgos>\"}";

        $result = $this->llm->generate($system, $user, true);
        if ($result) {
            $parsed = $this->parsePredictions($result['text'], $horizon, $result['provider']);
            if ($parsed) {
                return $parsed;
            }
        }

        return $this->forecastLocally($history, $horizon);
    }

    /**
     * Parse the provider's JSON text into normalized predictions + analysis.
     */
    private function parsePredictions(?string $content, int $horizon, string $provider): ?array
    {
        $data = json_decode($content ?? '', true);
        if (! is_array($data) || empty($data['predictions']) || ! is_array($data['predictions'])) {
            return null;
        }

        $predictions = collect($data['predictions'])
            ->filter(fn ($p) => isset($p['period']))
            ->take($horizon)
            ->map(fn ($p) => [
                'period' => $p['period'],
                'label' => ucfirst(Carbon::createFromFormat('Y-m', $p['period'])->translatedFormat('M Y')),
                'units' => max(0, (int) round((float) ($p['units'] ?? 0))),
                'sales_usd' => max(0, round((float) ($p['sales_usd'] ?? 0), 2)),
                'predicted' => true,
            ])
            ->values()
            ->all();

        if (empty($predictions)) {
            return null;
        }

        return [
            'predictions' => $predictions,
            'analysis' => (string) ($data['analysis'] ?? ''),
            'source' => $provider,
        ];
    }

    /**
     * Local fallback: least-squares linear trend over the recent months.
     */
    private function forecastLocally(Collection $history, int $horizon): array
    {
        $recent = $history->take(-12)->values();
        $n = $recent->count();
        $xs = range(0, $n - 1);

        $project = function (string $key) use ($recent, $xs, $n) {
            $ys = $recent->pluck($key)->map(fn ($v) => (float) $v)->all();
            $sumX = array_sum($xs);
            $sumY = array_sum($ys);
            $sumXY = 0;
            $sumXX = 0;
            foreach ($xs as $i => $x) {
                $sumXY += $x * $ys[$i];
                $sumXX += $x * $x;
            }
            $denom = $n * $sumXX - $sumX * $sumX;
            $slope = $denom != 0 ? ($n * $sumXY - $sumX * $sumY) / $denom : 0;
            $intercept = ($sumY - $slope * $sumX) / $n;
            return [$slope, $intercept];
        };

        [$uSlope, $uIntercept] = $project('units');
        [$sSlope, $sIntercept] = $project('sales_usd');

        $lastPeriod = Carbon::createFromFormat('Y-m', $recent->last()['period']);
        $predictions = [];
        for ($i = 1; $i <= $horizon; $i++) {
            $x = $n - 1 + $i;
            $period = $lastPeriod->copy()->addMonths($i);
            $predictions[] = [
                'period' => $period->format('Y-m'),
                'label' => ucfirst($period->translatedFormat('M Y')),
                'units' => max(0, (int) round($uIntercept + $uSlope * $x)),
                'sales_usd' => max(0, round($sIntercept + $sSlope * $x, 2)),
                'predicted' => true,
            ];
        }

        return [
            'predictions' => $predictions,
            'analysis' => 'Proyección estadística por tendencia lineal de los últimos ' . $n . ' meses (configura GEMINI_API_KEY para análisis con IA gratis).',
            'source' => 'local',
        ];
    }
}
