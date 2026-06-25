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

        // 7. Agrupar ventas por Cliente (respetando filtros de cliente, producto y clase si existen)
        $clientQuery = Sale::query();
        if ($selectedMonthVal) {
            $clientQuery->whereDate('report_date', $selectedMonthVal);
        }
        if ($selectedClient) {
            $clientQuery->where('client_code', $selectedClient);
        }
        if ($selectedClass) {
            $clientQuery->where('client_class', $selectedClass);
        }
        if ($selectedProduct) {
            $clientQuery->where(function ($q) use ($selectedProduct) {
                $q->where('product_code', 'like', '%' . $selectedProduct . '%')
                  ->orWhere('product_description', 'like', '%' . $selectedProduct . '%');
            });
        }
        
        $salesByClient = $clientQuery->get()->groupBy('client_code')->map(function ($clientSales) use ($viewType) {
            $first = $clientSales->first();
            
            $groupedItems = $clientSales->groupBy('product_code')->map(function ($productSales) {
                $firstProd = $productSales->first();
                return (object)[
                    'product_code' => $firstProd->product_code,
                    'product_description' => $firstProd->product_description,
                    'quantity' => $productSales->sum('quantity'),
                    'total_sales' => $productSales->sum(fn($s) => $s->total_sales / ($s->exchange_rate ?: 1)),
                ];
            })->filter(function ($item) {
                return $item->quantity > 0;
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
            ->havingRaw('SUM(quantity) > 0')
            ->orderBy($viewType === 'units' ? 'total_qty' : 'total_sales', 'desc')
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
     * Store manually entered sale data.
     */
    public function storeManualEntry(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
            'client_code' => 'required|string',
            'product_code' => 'required|string',
            'quantity' => 'required|numeric|min:0',
            'total_sales' => 'required|numeric|min:0',
        ]);

        \Log::info('Manual Entry Request:', [
            'month' => $request->month,
            'year' => $request->year,
            'client_code' => $request->client_code,
            'product_code' => $request->product_code,
            'quantity' => $request->quantity,
            'total_sales' => $request->total_sales,
            'total_sales_type' => gettype($request->total_sales),
        ]);

        // Get client and product info from existing records
        $existingSale = Sale::where('client_code', $request->client_code)
            ->where('product_code', $request->product_code)
            ->first();

        if (!$existingSale) {
            return back()->withErrors(['error' => 'No se encontró información para este cliente y producto. Asegúrate de que existan en los datos importados.']);
        }

        // Calculate report date (first day of selected month)
        $reportDate = Carbon::create($request->year, $request->month, 1);

        \Log::info('Report Date:', ['report_date' => $reportDate->format('Y-m-d')]);

        // Check if there's already a sale record for this combination
        $existingRecord = Sale::where('report_date', $reportDate)
            ->where('client_code', $request->client_code)
            ->where('product_code', $request->product_code)
            ->first();

        \Log::info('Existing Record Found:', ['found' => $existingRecord ? 'yes' : 'no']);
        if ($existingRecord) {
            \Log::info('Existing Record Values:', [
                'quantity' => $existingRecord->quantity,
                'total_sales' => $existingRecord->total_sales,
            ]);
        }

        if ($existingRecord) {
            // Update existing record
            $existingRecord->quantity += $request->quantity;
            $existingRecord->total_sales += (float) $request->total_sales;
            $existingRecord->total_cost += ((float) $request->total_sales * 0.15); // Assuming 15% cost
            $existingRecord->total_utility = $existingRecord->total_sales - $existingRecord->total_cost;
            $existingRecord->utility_percentage = $existingRecord->total_sales > 0
                ? ($existingRecord->total_utility / $existingRecord->total_sales) * 100
                : 0;
            $existingRecord->is_manual = true;
            $existingRecord->exchange_rate = $existingRecord->exchange_rate ?? 1;
            $existingRecord->save();

            \Log::info('After Save (Update):', [
                'quantity' => $existingRecord->quantity,
                'total_sales' => $existingRecord->total_sales,
                'exchange_rate' => $existingRecord->exchange_rate,
            ]);
        } else {
            // Create new record
            $totalCost = (float) $request->total_sales * 0.15; // Assuming 15% cost
            $totalUtility = (float) $request->total_sales - $totalCost;
            $utilityPercentage = (float) $request->total_sales > 0
                ? ($totalUtility / (float) $request->total_sales) * 100
                : 0;

            Sale::create([
                'report_date' => $reportDate,
                'exchange_rate' => 1,
                'client_code' => $request->client_code,
                'client_name' => $existingSale->client_name,
                'client_class' => $existingSale->client_class,
                'product_code' => $request->product_code,
                'product_description' => $existingSale->product_description,
                'quantity' => $request->quantity,
                'total_sales' => (float) $request->total_sales,
                'total_cost' => $totalCost,
                'total_utility' => $totalUtility,
                'utility_percentage' => $utilityPercentage,
                'is_manual' => true,
            ]);
        }

        $monthLabel = $this->getSpanishMonthName($request->month) . ' ' . $request->year;

        return redirect()->route('dashboard')->with('success', "Venta agregada correctamente a {$monthLabel}.");
    }
}
