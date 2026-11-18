<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\SalesForecaster;
use Illuminate\Http\Request;

class ForecastController extends Controller
{
    /**
     * Sales forecast page: monthly history plus AI-projected months.
     */
    public function index(Request $request, SalesForecaster $forecaster)
    {
        $selectedClient = $request->input('client');
        $selectedProduct = $request->input('product');
        $viewType = $request->input('view_type', 'sales'); // 'units' or 'sales'
        $horizon = (int) $request->input('horizon', 3);
        $horizon = max(1, min(6, $horizon));

        // Client list for the filter dropdown
        $clients = Sale::select('client_code', 'client_name')
            ->distinct()
            ->orderBy('client_name')
            ->get();

        $scopeLabel = 'ventas globales';
        if ($selectedClient) {
            $scopeLabel = 'el cliente ' . ($clients->firstWhere('client_code', $selectedClient)->client_name ?? $selectedClient);
        }
        if ($selectedProduct) {
            $scopeLabel .= ($selectedClient ? ' y ' : 'el producto ') . '"' . $selectedProduct . '"';
        }

        $history = $forecaster->history($selectedClient, $selectedProduct);
        $forecast = $forecaster->forecast($history, $horizon, $scopeLabel);

        $chartRows = $history->map(fn ($r) => $r + ['predicted' => false])
            ->concat($forecast['predictions'])
            ->values();

        return view('forecast', [
            'title' => 'Predicción de Ventas',
            'clients' => $clients,
            'selectedClient' => $selectedClient,
            'selectedProduct' => $selectedProduct,
            'viewType' => $viewType,
            'horizon' => $horizon,
            'history' => $history,
            'predictions' => collect($forecast['predictions']),
            'analysis' => $forecast['analysis'],
            'source' => $forecast['source'],
            'chartLabels' => $chartRows->pluck('label'),
            'chartUnits' => $chartRows->pluck('units'),
            'chartSales' => $chartRows->pluck('sales_usd'),
            'chartPredictedFlags' => $chartRows->pluck('predicted'),
            'hasData' => $history->isNotEmpty(),
        ]);
    }
}
