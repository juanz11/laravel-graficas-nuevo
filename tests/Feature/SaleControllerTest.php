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
