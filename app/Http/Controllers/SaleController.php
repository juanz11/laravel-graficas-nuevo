<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display the sales statistics dashboard.
     */
    public function index(Request $request)
    {
        // 1. Obtener todos los meses con reportes disponibles
        $availableDates = Sale::select('report_date')
            ->distinct()
            ->orderBy('report_date', 'desc')
            ->pluck('report_date');

        // Formatear los meses para el selector
        $months = $availableDates->map(function ($date) {
            $carbon = Carbon::parse($date);
            return [
                'val' => $carbon->format('Y-m-d'),
                'label' => $this->getSpanishMonthName($carbon->month) . ' ' . $carbon->year,
            ];
        });

        // 2. Determinar el mes seleccionado
        $selectedMonthVal = null;
        if ($request->has('month')) {
            $selectedMonthVal = $request->input('month'); // puede ser "" para "Todos los meses"
        } else {
            // Por defecto en la primera carga, mostramos el último mes disponible
            if ($availableDates->isNotEmpty()) {
                $selectedMonthVal = $availableDates->first()->format('Y-m-d');
            }
        }

        if ($selectedMonthVal) {
            $selectedMonth = Carbon::parse($selectedMonthVal);
            $selectedMonthLabel = $this->getSpanishMonthName($selectedMonth->month) . ' ' . $selectedMonth->year;
        } else {
            $selectedMonthLabel = 'Todos los meses';
        }

        // 3. Obtener filtros
        $selectedClient = $request->input('client');
        $selectedClass = $request->input('class');
        $selectedProduct = $request->input('product');
        $viewType = $request->input('view_type', 'units'); // 'units' or 'sales'

        // 4. Consultar los datos filtrados por mes y filtros adicionales
        $query = Sale::query();
        if ($selectedMonthVal) {
            $query->whereDate('report_date', $selectedMonthVal);
        }

        if ($selectedClient) {
            $query->where('client_code', $selectedClient);
        }

        if ($selectedClass) {
            $query->where('client_class', $selectedClass);
        }

        if ($selectedProduct) {
            $query->where(function ($q) use ($selectedProduct) {
                $q->where('product_code', 'like', '%' . $selectedProduct . '%')
                  ->orWhere('product_description', 'like', '%' . $selectedProduct . '%');
            });
        }

        // Include all products including discounts (negative values will subtract from totals)
        $sales = $query->get();

        // 5. Calcular KPIs principales
        $totalSalesUsd = $sales->sum(fn($s) => $s->total_sales / ($s->exchange_rate ?: 1));
        $totalUtilityUsd = $sales->sum(fn($s) => $s->total_utility / ($s->exchange_rate ?: 1));

        $kpis = [
            'total_sales' => $totalSalesUsd,
            'total_cost' => $sales->sum(fn($s) => $s->total_cost / ($s->exchange_rate ?: 1)),
            'total_utility' => $totalUtilityUsd,
            'total_quantity' => $sales->sum('quantity'),
            'utility_margin' => $totalSalesUsd > 0
                ? ($totalUtilityUsd / $totalSalesUsd) * 100
                : 0,
        ];

        // 6. Agrupar ventas por Clase de cliente (sin filtros de cliente/producto para mostrar distribución general)
        $classQuery = Sale::query();
        if ($selectedMonthVal) {
            $classQuery->whereDate('report_date', $selectedMonthVal);
        }
        $salesByClass = $classQuery
            ->select('client_class', DB::raw('SUM(total_sales / COALESCE(exchange_rate, 1)) as total_sales'), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('client_class')
            ->orderBy($viewType === 'units' ? 'total_qty' : 'total_sales', 'desc')
            ->get();

        // 7. Agrupar ventas por Cliente (usando la misma consulta base para consistencia)
        $salesByClient = $sales->groupBy('client_code')->map(function ($clientSales) use ($viewType) {
            $first = $clientSales->first();

            $groupedItems = $clientSales->groupBy('product_code')->map(function ($productSales) {
                $firstProd = $productSales->first();
                return (object)[
                    'product_code' => $firstProd->product_code,
                    'product_description' => $firstProd->product_description,
                    'quantity' => $productSales->sum('quantity'),
                    'total_sales' => $productSales->sum(fn($s) => $s->total_sales / ($s->exchange_rate ?: 1)),
                ];
            })->sortByDesc($viewType === 'units' ? 'quantity' : 'total_sales')->values();

            return [
                'code' => $first->client_code,
                'name' => $first->client_name,
                'class' => $first->client_class,
                'total_sales' => $clientSales->sum(fn($s) => $s->total_sales / ($s->exchange_rate ?: 1)),
                'total_qty' => $clientSales->sum('quantity'),
                'items' => $groupedItems
            ];
        })->sortByDesc($viewType === 'units' ? 'total_qty' : 'total_sales')->values();

        // 8. Agrupar ventas por Producto (para gráfica de productos top, respetando filtros de cliente, clase y producto si existen)
        $productQuery = Sale::query();
        if ($selectedMonthVal) {
            $productQuery->whereDate('report_date', $selectedMonthVal);
        }
        if ($selectedClient) {
            $productQuery->where('client_code', $selectedClient);
        }
        if ($selectedClass) {
            $productQuery->where('client_class', $selectedClass);
        }
        if ($selectedProduct) {
            $productQuery->where(function ($q) use ($selectedProduct) {
                $q->where('product_code', 'like', '%' . $selectedProduct . '%')
                  ->orWhere('product_description', 'like', '%' . $selectedProduct . '%');
            });
        }
        $salesByProduct = $productQuery
            ->select('product_code', 'product_description', DB::raw('SUM(total_sales / COALESCE(exchange_rate, 1)) as total_sales'), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_code', 'product_description')
            ->orderBy($viewType === 'units' ? 'total_qty' : 'total_sales', 'desc')
            ->limit(15)
            ->get();

        // Separate query for Costo Promedio chart — always ordered by total_sales, independent of viewType
        $avgCostProductQuery = Sale::query();
        if ($selectedMonthVal) {
            $avgCostProductQuery->whereDate('report_date', $selectedMonthVal);
        }
        if ($selectedClient) {
            $avgCostProductQuery->where('client_code', $selectedClient);
        }
        if ($selectedClass) {
            $avgCostProductQuery->where('client_class', $selectedClass);
        }
        if ($selectedProduct) {
            $avgCostProductQuery->where(function ($q) use ($selectedProduct) {
                $q->where('product_code', 'like', '%' . $selectedProduct . '%')
                  ->orWhere('product_description', 'like', '%' . $selectedProduct . '%');
            });
        }
        $salesByProductForAvgCost = $avgCostProductQuery
            ->select('product_code', 'product_description', DB::raw('SUM(total_sales / COALESCE(exchange_rate, 1)) as total_sales'), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_code', 'product_description')
            ->having(DB::raw('SUM(quantity)'), '>', 0)
            ->orderBy('total_qty', 'desc')
            ->limit(15)
            ->get();

        // 9. Calcular tendencia mensual de ventas para el gráfico de línea (respetando filtros de cliente, clase y producto si existen)
        $monthDateFormat = DB::connection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m-01', report_date)"
            : "DATE_FORMAT(report_date, '%Y-%m-01')";

        $trendQuery = Sale::select(
            DB::raw("$monthDateFormat as month_date"),
            DB::raw("SUM(total_sales / COALESCE(exchange_rate, 1)) as total_sales"),
            DB::raw("SUM(quantity) as total_qty")
        );
        if ($selectedClient) {
            $trendQuery->where('client_code', $selectedClient);
        }
        if ($selectedClass) {
            $trendQuery->where('client_class', $selectedClass);
        }
        if ($selectedProduct) {
            $trendQuery->where(function ($q) use ($selectedProduct) {
                $q->where('product_code', 'like', '%' . $selectedProduct . '%')
                  ->orWhere('product_description', 'like', '%' . $selectedProduct . '%');
            });
        }
        $monthlyTrend = $trendQuery->groupBy('month_date')
            ->orderBy('month_date', 'asc')
            ->get()
            ->map(function ($item) {
                $carbon = Carbon::parse($item->month_date);
                return [
                    'label' => $this->getSpanishMonthName($carbon->month) . ' ' . $carbon->year,
                    'total_sales' => (float) $item->total_sales,
                    'total_qty' => (float) $item->total_qty
                ];
            });

        // 10. Obtener lista de clientes para el filtro (respetando la clase seleccionada si existe)
        $clientsQuery = Sale::query();
        if ($selectedMonthVal) {
            $clientsQuery->whereDate('report_date', $selectedMonthVal);
        }
        if ($selectedClass) {
            $clientsQuery->where('client_class', $selectedClass);
        }
        $clientsList = $clientsQuery->select('client_code', 'client_name')
            ->distinct()
            ->orderBy('client_name')
            ->get()
            ->map(function ($client) {
                return [
                    'code' => $client->client_code,
                    'name' => $client->client_name
                ];
            });

        // 11. Obtener lista de clases únicas para el selector de filtros
        $classesQuery = Sale::query();
        if ($selectedMonthVal) {
            $classesQuery->whereDate('report_date', $selectedMonthVal);
        }
        $classesList = $classesQuery
            ->whereNotNull('client_class')
            ->where('client_class', '!=', '')
            ->select('client_class')
            ->distinct()
            ->orderBy('client_class')
            ->pluck('client_class');

        return view('dashboard', [
            'months' => $months,
            'selectedMonthVal' => $selectedMonthVal,
            'selectedMonthLabel' => $selectedMonthLabel,
            'selectedClient' => $selectedClient,
            'selectedClass' => $selectedClass,
            'selectedProduct' => $selectedProduct,
            'viewType' => $viewType,
            'kpis' => $kpis,
            'salesByClass' => $salesByClass,
            'salesByClient' => $salesByClient,
            'salesByProduct' => $salesByProduct,
            'salesByProductForAvgCost' => $salesByProductForAvgCost,
            'monthlyTrend' => $monthlyTrend,
            'clientsList' => $clientsList,
            'classesList' => $classesList,
            'hasData' => $sales->isNotEmpty(),
        ]);
    }

    /**
     * Handle the upload and import of the Excel/CSV sales report.
     */
    public function import(Request $request)
    {
        $request->validate([
            'report_file' => 'required|file|max:10240',
            'exchange_rate' => 'required|numeric|gt:0',
        ]);

        $exchangeRate = (float) $request->input('exchange_rate');
        $file = $request->file('report_file');
        
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls', 'csv', 'txt', 'html'])) {
            return back()->withErrors(['report_file' => 'El archivo debe tener una extensión válida: xlsx, xls, csv, txt, html.']);
        }

        $filePath = $file->getRealPath();

        try {
            // Cargar el archivo usando PhpSpreadsheet (detecta automáticamente el formato)
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $reportDate = null;
            $salesToInsert = [];
            
            // Mapeo inicial por defecto
            $colMap = [
                'code' => 'A',
                'desc' => 'B',
                'qty' => 'C',
                'sales' => 'D',
                'cost' => 'E',
                'utility' => 'F',
                'utility_percent' => 'G',
            ];

            $currentClientCode = null;
            $currentClientName = null;
            $currentClientClass = null;
            $isNextRowClientData = false;

            // 1. Primera pasada para buscar la fecha del reporte
            foreach ($rows as $row) {
                $rowText = implode(' ', array_filter(array_map('strval', $row)));
                // Intentar buscar "Reporte de operaciones desde DD/MM/YYYY Hasta DD/MM/YYYY"
                if (preg_match('/desde\s+(\d{2})\/(\d{2})\/(\d{4})\s+Hasta\s+(\d{2})\/(\d{2})\/(\d{4})/i', $rowText, $matches)) {
                    // Usar el mes y año de la fecha de inicio del reporte
                    $day = $matches[1];
                    $month = $matches[2];
                    $year = $matches[3];
                    $reportDate = "{$year}-{$month}-01";
                    break;
                }
            }

            // Si no se encuentra en el formato largo, buscar cualquier patrón de fechas en filas de cabecera
            if (!$reportDate) {
                foreach ($rows as $row) {
                    foreach ($row as $val) {
                        if (is_string($val) && preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $val, $matches)) {
                            $reportDate = "{$matches[3]}-{$matches[2]}-01";
                            break 2;
                        }
                    }
                }
            }

            // Si sigue sin fecha, asignar el primer día del mes actual
            if (!$reportDate) {
                $reportDate = now()->startOfMonth()->format('Y-m-d');
            }

            // 2. Segunda pasada para procesar los clientes y sus productos
            foreach ($rows as $row) {
                $rowText = implode(' ', array_filter(array_map('strval', $row)));

                // Detectar indicador de cliente
                if (str_contains($rowText, 'Datos del Cliente') && str_contains($rowText, 'Clase')) {
                    $isNextRowClientData = true;
                    continue;
                }

                if ($isNextRowClientData) {
                    $nonEmptyCells = [];
                    foreach ($row as $cell) {
                        $val = trim((string)$cell);
                        if ($val !== '') {
                            $nonEmptyCells[] = $val;
                        }
                    }
                    if (count($nonEmptyCells) >= 2) {
                        $currentClientCode = $nonEmptyCells[0];
                        $currentClientName = $nonEmptyCells[1];
                        $currentClientClass = $nonEmptyCells[2] ?? 'GENERAL';
                    }
                    $isNextRowClientData = false;
                    continue;
                }

                // Detectar cabecera de tabla de productos para re-mapear columnas si es necesario
                if (str_contains($rowText, 'Código') && str_contains($rowText, 'Descripción') && str_contains($rowText, 'Cantidad')) {
                    foreach ($row as $colLetter => $cellValue) {
                        $val = strtolower(trim((string)$cellValue));
                        if (str_contains($val, 'código') || str_contains($val, 'codigo')) {
                            $colMap['code'] = $colLetter;
                        } elseif (str_contains($val, 'descripción') || str_contains($val, 'descripcion')) {
                            $colMap['desc'] = $colLetter;
                        } elseif (str_contains($val, 'cantidad')) {
                            $colMap['qty'] = $colLetter;
                        } elseif (str_contains($val, 'total ventas') || (str_contains($val, 'total') && str_contains($val, 'venta'))) {
                            $colMap['sales'] = $colLetter;
                        } elseif (str_contains($val, 'total costo') || (str_contains($val, 'total') && str_contains($val, 'costo'))) {
                            $colMap['cost'] = $colLetter;
                        } elseif (str_contains($val, 'total utilidad') || (str_contains($val, 'total') && str_contains($val, 'utilidad'))) {
                            $colMap['utility'] = $colLetter;
                        } elseif (str_contains($val, '% utilidad')) {
                            $colMap['utility_percent'] = $colLetter;
                        }
                    }
                    continue;
                }

                // Si tenemos un cliente activo, procesar filas de productos
                if ($currentClientCode !== null) {
                    $prodCode = trim((string)($row[$colMap['code'] ?? 'A'] ?? ''));

                    // Ignorar cabeceras, totales de cliente, páginas o metadatos
                    if (empty($prodCode) || 
                        str_contains(strtolower($prodCode), 'código') || 
                        str_contains(strtolower($prodCode), 'codigo') || 
                        str_contains(strtolower($prodCode), 'total') || 
                        str_contains(strtolower($prodCode), 'datos') || 
                        str_contains(strtolower($prodCode), 'snc pharma') || 
                        str_contains(strtolower($prodCode), 'página') || 
                        str_contains(strtolower($prodCode), 'pagina') || 
                        str_contains(strtolower($prodCode), 'av.') ||
                        str_contains(strtolower($prodCode), 'reporte') ||
                        str_contains(strtolower($prodCode), 'clase')) {
                        continue;
                    }

                    $prodDesc = trim((string)($row[$colMap['desc'] ?? 'B'] ?? ''));
                    $qtyVal = $row[$colMap['qty'] ?? 'C'] ?? '0';
                    $salesVal = $row[$colMap['sales'] ?? 'D'] ?? '0';
                    $costVal = $row[$colMap['cost'] ?? 'E'] ?? '0';
                    $utilityVal = $row[$colMap['utility'] ?? 'F'] ?? '0';
                    $utilPctVal = $row[$colMap['utility_percent'] ?? 'G'] ?? '0';

                    // Limpieza e interpretación de números
                    $qty = (int) str_replace(['.', ',', ' ', '-'], '', (string)$qtyVal);
                    // Si el valor original tenía signo negativo "--" o "-"
                    if (str_starts_with(trim((string)$qtyVal), '--') || str_starts_with(trim((string)$qtyVal), '-')) {
                        $qty = -$qty;
                    }

                    $totalSales = $this->cleanAmount($salesVal);
                    $totalCost = $this->cleanAmount($costVal);
                    $totalUtility = $this->cleanAmount($utilityVal);
                    $utilityPct = $this->cleanAmount($utilPctVal);

                    // Si el costo viene vacío, calcularlo
                    if ($totalCost == 0.0 && $totalSales != 0.0 && $totalUtility != 0.0) {
                        $totalCost = $totalSales - $totalUtility;
                    }

                    $salesToInsert[] = [
                        'report_date' => $reportDate,
                        'exchange_rate' => $exchangeRate,
                        'client_code' => $currentClientCode,
                        'client_name' => $currentClientName,
                        'client_class' => $currentClientClass,
                        'product_code' => $prodCode,
                        'product_description' => $prodDesc,
                        'quantity' => $qty,
                        'total_sales' => $totalSales,
                        'total_cost' => $totalCost,
                        'total_utility' => $totalUtility,
                        'utility_percentage' => $utilityPct,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }

            if (count($salesToInsert) > 0) {
                // Iniciar transacción de base de datos
                DB::transaction(function () use ($reportDate, $salesToInsert) {
                    // Evitar duplicación de reportes para el mismo mes
                    Sale::where('report_date', $reportDate)->delete();

                    // Insertar registros en lotes de 500 para evitar límites del driver
                    $chunks = array_chunk($salesToInsert, 500);
                    foreach ($chunks as $chunk) {
                        Sale::insert($chunk);
                    }
                });

                $carbonDate = Carbon::parse($reportDate);
                $monthLabel = $this->getSpanishMonthName($carbonDate->month) . ' ' . $carbonDate->year;

                return back()->with('success', "Se importaron correctamente " . count($salesToInsert) . " registros de venta correspondientes a {$monthLabel}.");
            } else {
                return back()->withErrors(['report_file' => 'No se encontraron registros de ventas procesables en el archivo. Verifica el formato.']);
            }

        } catch (\Exception $e) {
            return back()->withErrors(['report_file' => 'Error al procesar el archivo: ' . $e->getMessage()]);
        }
    }

    /**
     * Clean and parse monetary amounts formatted in Spanish/European style.
     */
    private function cleanAmount($val)
    {
        // Si ya es numérico (PhpSpreadsheet ya lo convirtió), retornarlo directamente
        if (is_numeric($val)) {
            return (float) $val;
        }
        if (empty($val) || $val === '' || $val === null) {
            return 0.0;
        }
        $val = trim((string)$val);
        
        $isNegative = false;
        if (str_starts_with($val, '--') || str_starts_with($val, '-')) {
            $isNegative = true;
            $val = ltrim($val, '-');
        }
        
        // Remover símbolos porcentuales y espacios
        $val = str_replace(['%', ' ', 'Bs', 'Bs.'], '', $val);
        
        // Si está vacío después de limpiar
        if (empty($val)) {
            return 0.0;
        }
        
        // Si tiene coma, es formato en español (ej: 181.748,69 o --1.526.465,08)
        if (str_contains($val, ',')) {
            // Verificar si la coma es decimal o separador de miles
            $parts = explode(',', $val);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                // La coma es decimal (ej: 181,75)
                $val = str_replace('.', '', $val); // Quitar puntos de miles si existen
                $val = str_replace(',', '.', $val); // Reemplazar coma decimal por punto
            } else {
                // La coma es separador de miles (ej: 1,234,567.89)
                $val = str_replace(',', '', $val); // Quitar comas de miles
            }
        } else {
            // Si tiene más de un punto, son separadores de miles
            if (substr_count($val, '.') > 1) {
                $val = str_replace('.', '', $val);
            }
        }
        
        $num = (float) $val;
        return $isNegative ? -$num : $num;
    }

    /**
     * Return the Spanish name of a month.
     */
    private function getSpanishMonthName($monthNum)
    {
        $months = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        return $months[$monthNum] ?? 'Mes';
    }

    /**
     * Show the manual entry form.
     */
    public function showManualEntry()
    {
        // Get all unique clients
        $clients = Sale::select('client_code', 'client_name')
            ->distinct()
            ->orderBy('client_name')
            ->get();

        // Get all unique products
        $products = Sale::select('product_code', 'product_description')
            ->distinct()
            ->orderBy('product_description')
            ->get();

        return view('manual-entry', compact('clients', 'products'));
    }

    /**
     * Store manually entered sale data (multiple entries).
     */
    public function storeManualEntry(Request $request)
    {
        $request->validate([
            'month'          => 'required|integer|min:1|max:12',
            'year'           => 'required|integer|min:2020|max:2099',
            'exchange_rate'  => 'required|numeric|gt:0',
            'entries'        => 'required|array|min:1',
            'entries.*.client_code'  => 'required|string',
            'entries.*.product_code' => 'required|string',
            'entries.*.quantity'     => 'required|numeric',
            'entries.*.total_sales_bs' => 'required|numeric',
        ]);

        $reportDate   = Carbon::create($request->year, $request->month, 1);
        $exchangeRate = (float) $request->exchange_rate;
        $savedCount   = 0;
        $errors       = [];

        foreach ($request->entries as $index => $entry) {
            $clientCode   = $entry['client_code'];
            $productCode  = $entry['product_code'];
            $quantity     = (float) $entry['quantity'];
            $totalSalesBs = (float) $entry['total_sales_bs'];

            // Get reference info: client and product can exist separately; allow new combinations
            $clientRef  = Sale::where('client_code', $clientCode)->first();
            $productRef = Sale::where('product_code', $productCode)->first();

            if (!$clientRef || !$productRef) {
                $errors[] = "Fila " . ($index + 1) . ": No se encontró información para el cliente/producto seleccionado.";
                continue;
            }

            // Check if a record already exists for this month+client+product
            $existing = Sale::where('report_date', $reportDate)
                ->where('client_code', $clientCode)
                ->where('product_code', $productCode)
                ->first();

            // Totals are stored in Bs (same as Excel import); dashboard divides by exchange_rate
            $totalCostBs    = $totalSalesBs * 0.15;
            $totalUtilityBs = $totalSalesBs - $totalCostBs;

            if ($existing) {
                $newQuantity       = $existing->quantity + $quantity;
                $newTotalSalesBs   = $existing->total_sales + $totalSalesBs;
                $newTotalCostBs    = $newTotalSalesBs * 0.15;
                $newTotalUtilityBs = $newTotalSalesBs - $newTotalCostBs;

                $existing->quantity           = $newQuantity;
                $existing->total_sales        = $newTotalSalesBs;
                $existing->total_cost         = $newTotalCostBs;
                $existing->total_utility      = $newTotalUtilityBs;
                $existing->utility_percentage = $newTotalSalesBs > 0
                    ? ($newTotalUtilityBs / $newTotalSalesBs) * 100
                    : 0;
                $existing->is_manual          = true;
                $existing->exchange_rate      = $exchangeRate;
                $existing->save();
            } else {
                Sale::create([
                    'report_date'        => $reportDate,
                    'exchange_rate'      => $exchangeRate,
                    'client_code'        => $clientCode,
                    'client_name'        => $clientRef->client_name,
                    'client_class'       => $clientRef->client_class,
                    'product_code'       => $productCode,
                    'product_description'=> $productRef->product_description,
                    'quantity'           => $quantity,
                    'total_sales'        => $totalSalesBs,
                    'total_cost'         => $totalCostBs,
                    'total_utility'      => $totalUtilityBs,
                    'utility_percentage' => $totalSalesBs > 0 ? ($totalUtilityBs / $totalSalesBs) * 100 : 0,
                    'is_manual'          => true,
                ]);
            }

            $savedCount++;
        }

        if (!empty($errors)) {
            return back()->withErrors($errors);
        }

        $monthLabel = $this->getSpanishMonthName($request->month) . ' ' . $request->year;

        return redirect()->route('dashboard')->with('success', "{$savedCount} venta(s) agregada(s) correctamente a {$monthLabel}.");
    }

    /**
     * Show the manual entry form pre-filled with a full month of sales.
     */
    public function editMonth($date)
    {
        try {
            $carbonDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return redirect()->route('dashboard')->withErrors(['date' => 'La fecha del mes no es válida.']);
        }

        $sales = Sale::whereDate('report_date', $carbonDate->format('Y-m-d'))->get();

        if ($sales->isEmpty()) {
            return redirect()->route('dashboard')->withErrors(['month' => 'No hay ventas registradas para el mes seleccionado.']);
        }

        $exchangeRate = $sales->first()->exchange_rate;

        $existingEntries = $sales->groupBy('client_code')->map(function ($items) {
            return [
                'client_code' => $items->first()->client_code,
                'client_name' => $items->first()->client_name,
                'products' => $items->map(function ($s) {
                    return [
                        'product_code' => $s->product_code,
                        'product_description' => $s->product_description,
                        'quantity' => $s->quantity,
                        'total_sales_bs' => $s->total_sales,
                    ];
                })->values(),
            ];
        })->values();

        $clients = Sale::select('client_code', 'client_name')->distinct()->orderBy('client_name')->get();
        $products = Sale::select('product_code', 'product_description')->distinct()->orderBy('product_description')->get();

        $editMonthLabel = $this->getSpanishMonthName($carbonDate->month) . ' ' . $carbonDate->year;

        return view('manual-entry', [
            'editDate' => $carbonDate->format('Y-m-d'),
            'editMonth' => $carbonDate->month,
            'editYear' => $carbonDate->year,
            'editMonthLabel' => $editMonthLabel,
            'editExchangeRate' => $exchangeRate,
            'existingEntries' => $existingEntries,
            'clients' => $clients,
            'products' => $products,
        ]);
    }

    /**
     * Replace all sales for a given month using the manual entry form data.
     */
    public function updateMonth(Request $request, $date)
    {
        $request->validate([
            'exchange_rate' => 'required|numeric|gt:0',
            'entries' => 'required|array|min:1',
            'entries.*.client_code' => 'required|string',
            'entries.*.product_code' => 'required|string',
            'entries.*.quantity' => 'required|numeric',
            'entries.*.total_sales_bs' => 'required|numeric',
        ]);

        try {
            $reportDate = Carbon::parse($date);
        } catch (\Exception $e) {
            return back()->withErrors(['date' => 'La fecha del mes no es válida.'])->withInput();
        }

        $exchangeRate = (float) $request->exchange_rate;

        // Load client/product reference names before clearing the month
        $clientCodes = collect($request->entries)->pluck('client_code')->unique()->filter()->values();
        $productCodes = collect($request->entries)->pluck('product_code')->unique()->filter()->values();

        $clientMap = Sale::whereIn('client_code', $clientCodes)->get()->keyBy('client_code');
        $productMap = Sale::whereIn('product_code', $productCodes)->get()->keyBy('product_code');

        $records = [];
        foreach ($request->entries as $index => $entry) {
            $clientCode = $entry['client_code'];
            $productCode = $entry['product_code'];
            $quantity = (float) $entry['quantity'];
            $totalSalesBs = (float) $entry['total_sales_bs'];

            $clientRef = $clientMap->get($clientCode);
            $productRef = $productMap->get($productCode);

            if (!$clientRef || !$productRef) {
                return back()->withErrors(['entries' => 'Fila ' . ($index + 1) . ': No se encontró información para el cliente/producto seleccionado.'])->withInput();
            }

            $totalCostBs = $totalSalesBs * 0.15;
            $totalUtilityBs = $totalSalesBs - $totalCostBs;

            $records[] = [
                'report_date' => $reportDate->format('Y-m-d'),
                'exchange_rate' => $exchangeRate,
                'client_code' => $clientCode,
                'client_name' => $clientRef->client_name,
                'client_class' => $clientRef->client_class,
                'product_code' => $productCode,
                'product_description' => $productRef->product_description,
                'quantity' => $quantity,
                'total_sales' => $totalSalesBs,
                'total_cost' => $totalCostBs,
                'total_utility' => $totalUtilityBs,
                'utility_percentage' => $totalSalesBs > 0 ? ($totalUtilityBs / $totalSalesBs) * 100 : 0,
                'is_manual' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::transaction(function () use ($reportDate, $records) {
            Sale::whereDate('report_date', $reportDate->format('Y-m-d'))->delete();
            Sale::insert($records);
        });

        $monthLabel = $this->getSpanishMonthName($reportDate->month) . ' ' . $reportDate->year;

        return redirect()->route('dashboard', ['month' => $reportDate->format('Y-m-d')])->with('success', "Se reemplazaron " . count($records) . " registros de {$monthLabel}.");
    }

    /**
     * List sales with filtering and pagination.
     */
    public function list(Request $request)
    {
        $query = Sale::query();

        // 1. Filtrado por mes
        $selectedMonthVal = $request->input('month');
        if ($selectedMonthVal) {
            $query->whereDate('report_date', $selectedMonthVal);
        }

        // 2. Filtrado por cliente
        $selectedClient = $request->input('client');
        if ($selectedClient) {
            $query->where('client_code', $selectedClient);
        }

        // 3. Búsqueda por producto o texto general
        $search = $request->input('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('product_code', 'like', '%' . $search . '%')
                  ->orWhere('product_description', 'like', '%' . $search . '%')
                  ->orWhere('client_name', 'like', '%' . $search . '%')
                  ->orWhere('client_code', 'like', '%' . $search . '%');
            });
        }

        // Obtener los registros paginados (50 por página)
        $sales = $query->orderBy('report_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(50)
            ->withQueryString();

        // Obtener meses disponibles para el filtro
        $availableDates = Sale::select('report_date')
            ->distinct()
            ->orderBy('report_date', 'desc')
            ->pluck('report_date');

        $months = $availableDates->map(function ($date) {
            $carbon = Carbon::parse($date);
            return [
                'val' => $carbon->format('Y-m-d'),
                'label' => $this->getSpanishMonthName($carbon->month) . ' ' . $carbon->year,
            ];
        });

        // Obtener lista completa de clientes únicos
        $clients = Sale::select('client_code', 'client_name')
            ->distinct()
            ->orderBy('client_name')
            ->get();

        // Obtener lista completa de productos únicos
        $products = Sale::select('product_code', 'product_description')
            ->distinct()
            ->orderBy('product_description')
            ->get();

        return view('sales', compact('sales', 'months', 'clients', 'products', 'selectedMonthVal', 'selectedClient', 'search'));
    }

    /**
     * Get sale details in JSON format.
     */
    public function editJson($id)
    {
        $sale = Sale::findOrFail($id);
        
        $carbonDate = Carbon::parse($sale->report_date);
        $sale->month = $carbonDate->format('n');
        $sale->year = $carbonDate->format('Y');

        return response()->json($sale);
    }

    /**
     * Update an existing sale.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'month'          => 'required|integer|min:1|max:12',
            'year'           => 'required|integer|min:2020|max:2099',
            'exchange_rate'  => 'required|numeric|gt:0',
            'client_code'    => 'required|string',
            'product_code'   => 'required|string',
            'quantity'       => 'required|numeric',
            'total_sales'    => 'required|numeric',
            'total_cost'     => 'required|numeric',
            'total_utility'  => 'required|numeric',
        ]);

        $sale = Sale::findOrFail($id);

        $clientRef  = Sale::where('client_code', $request->client_code)->first();
        $productRef = Sale::where('product_code', $request->product_code)->first();

        $reportDate = Carbon::create($request->year, $request->month, 1);

        $sale->report_date        = $reportDate;
        $sale->exchange_rate      = (float) $request->exchange_rate;
        $sale->client_code        = $request->client_code;
        $sale->client_name        = $clientRef ? $clientRef->client_name : $sale->client_name;
        $sale->client_class       = $clientRef ? $clientRef->client_class : $sale->client_class;
        $sale->product_code       = $request->product_code;
        $sale->product_description = $productRef ? $productRef->product_description : $sale->product_description;
        $sale->quantity           = (float) $request->quantity;
        $sale->total_sales        = (float) $request->total_sales;
        $sale->total_cost         = (float) $request->total_cost;
        $sale->total_utility      = (float) $request->total_utility;
        $sale->utility_percentage = $request->total_sales > 0 ? ($sale->total_utility / $sale->total_sales) * 100 : 0;
        
        $sale->save();

        return back()->with('success', 'El registro de venta ha sido actualizado correctamente.');
    }

    /**
     * Delete a sale.
     */
    public function destroy($id)
    {
        $sale = Sale::findOrFail($id);
        $sale->delete();

        return back()->with('success', 'El registro de venta ha sido eliminado correctamente.');
    }
}
