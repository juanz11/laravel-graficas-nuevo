<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_it_aggregates_sales_of_the_same_product_across_multiple_months_when_viewing_all_months()
    {
        // 1. Create a user and authenticate
        $user = User::factory()->create();

        // 2. Create duplicate sales records for the same product and client but on different dates (months)
        Sale::create([
            'report_date' => '2026-05-01',
            'client_code' => 'CLI001',
            'client_name' => 'Test Client',
            'client_class' => 'A',
            'product_code' => 'PROD123',
            'product_description' => 'Test Product',
            'quantity' => 10,
            'total_sales' => 100.00,
            'total_cost' => 80.00,
            'total_utility' => 20.00,
            'utility_percentage' => 20.00,
        ]);

        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Test Client',
            'client_class' => 'A',
            'product_code' => 'PROD123',
            'product_description' => 'Test Product',
            'quantity' => 15,
            'total_sales' => 150.00,
            'total_cost' => 120.00,
            'total_utility' => 30.00,
            'utility_percentage' => 20.00,
        ]);

        // 3. Make the request to the dashboard route with no month (all months)
        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '']));

        $response->assertStatus(200);

        // 4. Extract salesByClient view data
        $salesByClient = $response->viewData('salesByClient');

        // 5. Assert the sales list is grouped by client
        $this->assertCount(1, $salesByClient);
        
        $clientSales = $salesByClient[0];
        $this->assertEquals('CLI001', $clientSales['code']);
        
        // Assert the total client quantities and sales are summed
        $this->assertEquals(25, $clientSales['total_qty']);
        $this->assertEquals(250.00, $clientSales['total_sales']);

        // Assert items are grouped by product (so only 1 item exists instead of 2)
        $items = $clientSales['items'];
        $this->assertCount(1, $items);

        $productItem = $items[0];
        $this->assertEquals('PROD123', $productItem->product_code);
        $this->assertEquals('Test Product', $productItem->product_description);
        $this->assertEquals(25, $productItem->quantity);
        $this->assertEquals(250.00, $productItem->total_sales);
    }

    /** @test */
    public function test_it_filters_out_negative_and_zero_quantity_products_and_clients()
    {
        $user = User::factory()->create();

        // Client with positive overall qty, but contains one negative product
        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_POS',
            'product_description' => 'Positive Item',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);

        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_NEG',
            'product_description' => 'Negative/Discount Item',
            'quantity' => -5,
            'total_sales' => -50.00,
        ]);

        // Client with negative overall qty
        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI002',
            'client_name' => 'Client Two',
            'client_class' => 'A',
            'product_code' => 'PROD_NEG_ONLY',
            'product_description' => 'Negative Only Item',
            'quantity' => -2,
            'total_sales' => -20.00,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '']));

        $response->assertStatus(200);
        $salesByClient = $response->viewData('salesByClient');

        // Both CLI001 and CLI002 should be returned, because we do not filter clients themselves
        $this->assertCount(2, $salesByClient);
        
        $clientOne = collect($salesByClient)->firstWhere('code', 'CLI001');
        $this->assertNotNull($clientOne);

        // The items inside CLI001 should only contain PROD_POS, because PROD_NEG has -5 (<= 0)
        $items = $clientOne['items'];
        $this->assertCount(1, $items);
        $this->assertEquals('PROD_POS', $items[0]->product_code);

        $clientTwo = collect($salesByClient)->firstWhere('code', 'CLI002');
        $this->assertNotNull($clientTwo);

        // The items inside CLI002 should be empty because it only had negative quantity products
        $itemsTwo = $clientTwo['items'];
        $this->assertCount(0, $itemsTwo);
    }

    /** @test */
    public function test_it_converts_bs_to_usd_using_exchange_rate()
    {
        $user = User::factory()->create();

        // 100.00 Bs / 40.00 rate = $2.50 USD
        Sale::create([
            'report_date' => '2026-06-01',
            'exchange_rate' => 40.00,
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_POS',
            'product_description' => 'Positive Item',
            'quantity' => 10,
            'total_sales' => 100.00,
            'total_cost' => 80.00,
            'total_utility' => 20.00,
            'utility_percentage' => 20.00,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '']));

        $response->assertStatus(200);

        // Verify KPIs are converted to USD
        $kpis = $response->viewData('kpis');
        $this->assertEquals(2.50, $kpis['total_sales']);
        $this->assertEquals(2.00, $kpis['total_cost']);
        $this->assertEquals(0.50, $kpis['total_utility']);

        // Verify clients are converted to USD
        $salesByClient = $response->viewData('salesByClient');
        $this->assertCount(1, $salesByClient);
        $clientOne = $salesByClient[0];
        $this->assertEquals(2.50, $clientOne['total_sales']);
        $this->assertEquals(2.50, $clientOne['items'][0]->total_sales);
    }

    /** @test */
    public function test_it_filters_sales_by_client_and_product_in_dashboard()
    {
        $user = User::factory()->create();

        // Create sales for Client A and Client B
        Sale::create([
            'report_date' => '2026-01-01',
            'client_code' => 'CLI_A',
            'client_name' => 'Client A',
            'client_class' => 'CLASS_X',
            'product_code' => 'PROD_1',
            'product_description' => 'Product 1',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);

        Sale::create([
            'report_date' => '2026-01-01',
            'client_code' => 'CLI_B',
            'client_name' => 'Client B',
            'client_class' => 'CLASS_X',
            'product_code' => 'PROD_2',
            'product_description' => 'Product 2',
            'quantity' => 20,
            'total_sales' => 200.00,
        ]);


        // 1. Filter by client CLI_A
        $response = $this->actingAs($user)->get(route('dashboard', [
            'month' => '2026-01-01',
            'client' => 'CLI_A'
        ]));

        $response->assertStatus(200);
        $salesByClient = $response->viewData('salesByClient');
        // Now only CLI_A should be present in salesByClient
        $this->assertCount(1, $salesByClient);
        $this->assertEquals('CLI_A', $salesByClient[0]['code']);

        // 2. Filter by product PROD_2
        $response = $this->actingAs($user)->get(route('dashboard', [
            'month' => '2026-01-01',
            'product' => 'PROD_2'
        ]));

        $response->assertStatus(200);
        $salesByProduct = $response->viewData('salesByProduct');
        // Now only PROD_2 should be in salesByProduct
        $this->assertCount(1, $salesByProduct);
        $this->assertEquals('PROD_2', $salesByProduct[0]->product_code);
    }

    /** @test */
    public function test_discounts_reduce_sales_but_not_unit_totals()
    {
        $user = User::factory()->create();

        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD1',
            'product_description' => 'Real Product',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);

        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => '4112025E',
            'product_description' => 'DESCUENTO POR PRONTO PAGO',
            'quantity' => -3,
            'total_sales' => -50.00,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '2026-06-01']));

        $response->assertStatus(200);

        // Units: discount quantity must NOT subtract (10, not 7)
        $kpis = $response->viewData('kpis');
        $this->assertEquals(10, $kpis['total_quantity']);
        // Sales: discount amount still subtracts (100 - 50 = 50)
        $this->assertEquals(50.00, $kpis['total_sales']);

        $salesByClient = $response->viewData('salesByClient');
        $client = collect($salesByClient)->firstWhere('code', 'CLI001');
        $this->assertEquals(10, $client['total_qty']);
        $this->assertEquals(50.00, $client['total_sales']);
        // The discount row is not listed as a sold item
        $this->assertCount(1, $client['items']);
        $this->assertEquals('PROD1', $client['items'][0]->product_code);
    }

    /** @test */
    public function test_negative_value_rows_subtract_sales_but_not_units()
    {
        $user = User::factory()->create();

        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD1',
            'product_description' => 'Real Product',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);

        // Credit note row: negative quantity, amount stored positive
        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'NC001',
            'product_description' => 'NOTA DE CREDITO',
            'quantity' => -3,
            'total_sales' => 50.00,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '2026-06-01']));

        $response->assertStatus(200);

        // Units: credit row must NOT subtract (10, not 7)
        $kpis = $response->viewData('kpis');
        $this->assertEquals(10, $kpis['total_quantity']);
        // Sales: credit amount subtracts even though stored positive (100 - 50 = 50)
        $this->assertEquals(50.00, $kpis['total_sales']);

        $salesByClient = $response->viewData('salesByClient');
        $client = collect($salesByClient)->firstWhere('code', 'CLI001');
        $this->assertEquals(10, $client['total_qty']);
        $this->assertEquals(50.00, $client['total_sales']);
        // The credit row is not listed as a sold item
        $this->assertCount(1, $client['items']);
        $this->assertEquals('PROD1', $client['items'][0]->product_code);
    }

    /** @test */
    public function test_simple_import_adds_rows_without_touching_existing_data()
    {
        $user = User::factory()->create();

        // Existing regular data for June 2026 must survive the improvised import
        Sale::create([
            'report_date' => '2026-06-01',
            'exchange_rate' => 40.00,
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD1',
            'product_description' => 'Real Product',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);

        // XLSX fixture in the improvised format
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([
            ['Código', 'Productos', 'Clase Terapéutica', 'Cliente', 'Clase', 'Mes', 'Año', 'Unidades', 'Valores', 'Tasa Valor USD'],
            ['7595368000050', 'MEZIHITIN 10 MG X 50 COMP.', 'DROGAS ANTI-DEMENCIA', 'FARMATODO, C.A.', 'FARMATODO', 6, 2026, 13592, 50735.16, null],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'rep') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        $response = $this->actingAs($user)->post(route('sales.import-simple'), [
            'report_file' => new \Illuminate\Http\UploadedFile($path, 'simple.xlsx', null, null, true),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // The regular row is untouched
        $regular = Sale::where('product_code', 'PROD1')->first();
        $this->assertNotNull($regular);
        $this->assertEquals(10, $regular->quantity);
        $this->assertFalse((bool) $regular->is_improvised);

        // The improvised row was imported with rate 0 and correct month
        $imported = Sale::where('product_code', '7595368000050')->first();
        $this->assertNotNull($imported);
        $this->assertTrue((bool) $imported->is_improvised);
        $this->assertEquals('2026-06-01', $imported->report_date->format('Y-m-d'));
        $this->assertEquals(13592, $imported->quantity);
        $this->assertEquals(50735.16, (float) $imported->total_sales);
        $this->assertEquals(0.0, (float) $imported->exchange_rate);
        $this->assertEquals('FARMATODO, C.A.', $imported->client_name);
        $this->assertEquals('FARMATODO', $imported->client_class);

        // The dashboard shows both sets of units without dividing by zero
        $response = $this->actingAs($user)->get(route('dashboard', ['month' => '2026-06-01']));
        $response->assertStatus(200);
        $kpis = $response->viewData('kpis');
        $this->assertEquals(13602, $kpis['total_quantity']);
    }

    /** @test */
    public function test_it_filters_by_month_and_year_inputs()
    {
        $user = User::factory()->create();

        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_JUN',
            'product_description' => 'June Product',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);

        Sale::create([
            'report_date' => '2026-07-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_JUL',
            'product_description' => 'July Product',
            'quantity' => 20,
            'total_sales' => 200.00,
        ]);

        // Month + year => exact month
        $response = $this->actingAs($user)->get(route('dashboard', ['filter_month' => 6, 'filter_year' => 2026]));
        $response->assertStatus(200);
        $this->assertEquals(10, $response->viewData('kpis')['total_quantity']);
        $this->assertEquals('2026-06-01', $response->viewData('selectedMonthVal'));

        // Year only => whole year
        $response = $this->actingAs($user)->get(route('dashboard', ['filter_year' => 2026]));
        $response->assertStatus(200);
        $this->assertEquals(30, $response->viewData('kpis')['total_quantity']);
        $this->assertEquals(2026, $response->viewData('selectedYearOnly'));
        $this->assertEquals('Año 2026', $response->viewData('selectedMonthLabel'));

        // Both empty => all months
        $response = $this->actingAs($user)->get(route('dashboard', ['filter_month' => '', 'filter_year' => '']));
        $response->assertStatus(200);
        $this->assertEquals(30, $response->viewData('kpis')['total_quantity']);
    }

    /** @test */
    public function test_it_compares_a_year_with_a_previous_year()
    {
        $user = User::factory()->create();

        Sale::create([
            'report_date' => '2025-03-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD1',
            'product_description' => 'Product',
            'quantity' => 50,
            'total_sales' => 500.00,
        ]);

        Sale::create([
            'report_date' => '2026-03-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD1',
            'product_description' => 'Product',
            'quantity' => 100,
            'total_sales' => 1000.00,
        ]);

        // Filter to year 2026 => compares against 2025 automatically
        $response = $this->actingAs($user)->get(route('dashboard', ['filter_year' => 2026]));
        $response->assertStatus(200);

        $cmp = $response->viewData('yearComparison');
        $this->assertNotNull($cmp);
        $this->assertEquals(2026, $cmp['year_a']);
        $this->assertEquals(2025, $cmp['year_b']);
        $this->assertEquals(100, $cmp['a']['units'][2]); // March
        $this->assertEquals(50, $cmp['b']['units'][2]);
        $this->assertEquals(1000.0, $cmp['a']['sales'][2]);
        $this->assertEquals(500.0, $cmp['b']['sales'][2]);
        $this->assertEquals(100.0, $cmp['totals']['delta_units']); // +100%

        // Explicit compare_year override
        $response = $this->actingAs($user)->get(route('dashboard', ['filter_year' => 2025, 'compare_year' => 2026]));
        $cmp = $response->viewData('yearComparison');
        $this->assertEquals(2025, $cmp['year_a']);
        $this->assertEquals(2026, $cmp['year_b']);
    }

    /** @test */
    public function test_it_updates_client_info_across_all_their_sales()
    {
        $user = User::factory()->create();

        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Old Name',
            'client_class' => 'A',
            'product_code' => 'PROD1',
            'product_description' => 'Product',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);
        Sale::create([
            'report_date' => '2026-07-01',
            'client_code' => 'CLI001',
            'client_name' => 'Old Name',
            'client_class' => 'A',
            'product_code' => 'PROD2',
            'product_description' => 'Product 2',
            'quantity' => 5,
            'total_sales' => 50.00,
        ]);
        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI002',
            'client_name' => 'Other Client',
            'client_class' => 'C',
            'product_code' => 'PROD1',
            'product_description' => 'Product',
            'quantity' => 3,
            'total_sales' => 30.00,
        ]);

        // The clients list loads
        $response = $this->actingAs($user)->get(route('clients.index'));
        $response->assertStatus(200);

        // Edit CLI001 => all its sales get the new name/class
        $response = $this->actingAs($user)->post(route('clients.update'), [
            'original_code' => 'CLI001',
            'client_code' => 'CLI001',
            'client_name' => 'New Name',
            'client_class' => 'B',
        ]);
        $response->assertRedirect();

        $this->assertEquals(2, Sale::where('client_code', 'CLI001')
            ->where('client_name', 'New Name')->where('client_class', 'B')->count());

        // Other clients untouched
        $other = Sale::where('client_code', 'CLI002')->first();
        $this->assertEquals('Other Client', $other->client_name);
        $this->assertEquals('C', $other->client_class);
    }

    /** @test */
    public function test_compare_shows_differences_without_importing()
    {
        $user = User::factory()->create();

        // Existing records in the system for June 2026
        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_SAME',
            'product_description' => 'Same Product',
            'quantity' => 10,
            'total_sales' => 100.00,
        ]);
        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_CHANGED',
            'product_description' => 'Changed Product',
            'quantity' => 5,
            'total_sales' => 50.00,
        ]);
        Sale::create([
            'report_date' => '2026-06-01',
            'client_code' => 'CLI001',
            'client_name' => 'Client One',
            'client_class' => 'A',
            'product_code' => 'PROD_ONLY_DB',
            'product_description' => 'Only In System',
            'quantity' => 3,
            'total_sales' => 30.00,
            'is_manual' => true,
        ]);

        // XLSX fixture mimicking the SNC report format for June 2026
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $spreadsheet->getActiveSheet()->fromArray([
            ['Reporte de operaciones desde 01/06/2026 Hasta 30/06/2026'],
            ['Datos del Cliente', 'Clase'],
            ['CLI001', 'Client One', 'A'],
            ['Código', 'Descripción', 'Cantidad', 'Total Ventas', 'Total Costo', 'Total Utilidad', '% Utilidad'],
            ['PROD_SAME', 'Same Product', 10, 100.00, 80.00, 20.00, 20],
            ['PROD_CHANGED', 'Changed Product', 8, 80.00, 64.00, 16.00, 20],
            ['PROD_NEW', 'Brand New Product', 4, 40.00, 32.00, 8.00, 20],
        ]);
        $path = tempnam(sys_get_temp_dir(), 'rep') . '.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        $response = $this->actingAs($user)->post(route('compare.run'), [
            'report_file' => new \Illuminate\Http\UploadedFile($path, 'reporte.xlsx', null, null, true),
        ]);

        $response->assertOk();
        $comparison = $response->viewData('comparison');

        // PROD_SAME matches, PROD_CHANGED differs, PROD_NEW is new, PROD_ONLY_DB missing
        $this->assertEquals('Junio 2026', $comparison['month_label']);
        $this->assertEquals(1, $comparison['same_count']);

        $this->assertCount(1, $comparison['new']);
        $this->assertEquals('PROD_NEW', $comparison['new'][0]['product_code']);

        $this->assertCount(1, $comparison['changed']);
        $this->assertEquals('PROD_CHANGED', $comparison['changed'][0]['product_code']);
        $this->assertEquals(5, $comparison['changed'][0]['old_qty']);
        $this->assertEquals(8, $comparison['changed'][0]['new_qty']);

        $this->assertCount(1, $comparison['missing']);
        $this->assertEquals('PROD_ONLY_DB', $comparison['missing'][0]['product_code']);
        $this->assertTrue($comparison['missing'][0]['is_manual']);

        // Nothing was imported
        $this->assertEquals(3, Sale::count());
    }
}
