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

        // Only CLI001 should be returned, because CLI002 has a total_qty of -2 (<= 0)
        $this->assertCount(1, $salesByClient);
        
        $clientOne = $salesByClient[0];
        $this->assertEquals('CLI001', $clientOne['code']);

        // The items inside CLI001 should only contain PROD_POS, because PROD_NEG has -5 (<= 0)
        $items = $clientOne['items'];
        $this->assertCount(1, $items);
        $this->assertEquals('PROD_POS', $items[0]->product_code);
    }
}
