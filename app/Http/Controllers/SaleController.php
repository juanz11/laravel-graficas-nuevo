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

        // 2. Determinar el mes seleccionado (por defecto el último disponible, o el actual si no hay datos)
        $selectedMonthVal = $request->input('month');
        if (!$selectedMonthVal && $availableDates->isNotEmpty()) {
            $selectedMonthVal = $availableDates->first()->format('Y-m-d');
        } elseif (!$selectedMonthVal) {
            $selectedMonthVal = now()->startOfMonth()->format('Y-m-d');
        }

        $selectedMonth = Carbon::parse($selectedMonthVal);
        $selectedMonthLabel = $this->getSpanishMonthName($selectedMonth->month) . ' ' . $selectedMonth->year;

        // 3. Consultar los datos filtrados por mes
        $sales = Sale::where('report_date', $selectedMonthVal)->get();

        // 4. Calcular KPIs principales
        $kpis = [
            'total_sales' => $sales->sum('total_sales'),
            'total_cost' => $sales->sum('total_cost'),
            'total_utility' => $sales->sum('total_utility'),
            'total_quantity' => $sales->sum('quantity'),
            'utility_margin' => $sales->sum('total_sales') > 0 
                ? ($sales->sum('total_utility') / $sales->sum('total_sales')) * 100 
                : 0,
        ];

        // 5. Agrupar ventas por Clase de cliente
        $salesByClass = Sale::where('report_date', $selectedMonthVal)
            ->select('client_class', DB::raw('SUM(total_sales) as total_sales'), DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('client_class')
            ->orderBy('total_sales', 'desc')
            ->get();

        // 6. Agrupar ventas por Cliente
        $salesByClient = $sales->groupBy('client_code')->map(function ($clientSales) {
            $first = $clientSales->first();
            return [
                'code' => $first->client_code,
                'name' => $first->client_name,
                'class' => $first->client_class,
                'total_sales' => $clientSales->sum('total_sales'),
                'total_qty' => $clientSales->sum('quantity'),
                'items' => $clientSales
            ];
        })->sortByDesc('total_sales');

        // 7. Calcular tendencia mensual de ventas para el gráfico de línea
        $monthlyTrend = Sale::select(
            DB::raw("DATE_FORMAT(report_date, '%Y-%m-01') as month_date"),
            DB::raw("SUM(total_sales) as total_sales")
        )
        ->groupBy('month_date')
        ->orderBy('month_date', 'asc')
        ->get()
        ->map(function ($item) {
            $carbon = Carbon::parse($item->month_date);
            return [
                'label' => $this->getSpanishMonthName($carbon->month) . ' ' . $carbon->year,
                'total' => (float) $item->total_sales
            ];
        });

        return view('dashboard', [
            'months' => $months,
            'selectedMonthVal' => $selectedMonthVal,
            'selectedMonthLabel' => $selectedMonthLabel,
            'kpis' => $kpis,
            'salesByClass' => $salesByClass,
            'salesByClient' => $salesByClient,
            'monthlyTrend' => $monthlyTrend,
            'hasData' => $sales->isNotEmpty(),
        ]);
    }

    /**
     * Handle the upload and import of the Excel/CSV sales report.
     */
    public function import(Request $request)
    {
        $request->validate([
            'report_file' => 'required|file|mimes:xlsx,xls,csv,txt,html|max:10240',
        ]);

        $file = $request->file('report_file');
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
                        } elseif (str_contains($val, 'total ventas') || ($str_contains($val, 'total') && str_contains($val, 'venta'))) {
                            $colMap['sales'] = $colLetter;
                        } elseif (str_contains($val, 'total costo') || ($str_contains($val, 'total') && str_contains($val, 'costo'))) {
                            $colMap['cost'] = $colLetter;
                        } elseif (str_contains($val, 'total utilidad') || ($str_contains($val, 'total') && str_contains($val, 'utilidad'))) {
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
        if (is_numeric($val)) {
            return (float) $val;
        }
        if (empty($val)) {
            return 0.0;
        }
        $val = trim((string)$val);
        
        $isNegative = false;
        if (str_starts_with($val, '--') || str_starts_with($val, '-')) {
            $isNegative = true;
            $val = ltrim($val, '-');
        }
        
        // Remover símbolos porcentuales y espacios
        $val = str_replace(['%', ' '], '', $val);
        
        // Si tiene coma, es formato en español (ej: 181.748,69 o --1.526.465,08)
        if (str_contains($val, ',')) {
            $val = str_replace('.', '', $val); // Quitar puntos de miles
            $val = str_replace(',', '.', $val); // Reemplazar coma decimal por punto
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
}
