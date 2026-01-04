<?php

namespace Nehal\ModelCaching\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Nehal\ModelCaching\Traits\CachesQueries;

class CachingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
    }

    /** @test */
    public function it_caches_queries()
    {
        Product::create(['name' => 'Laptop']);

        // First query - should cache
        $products = Product::all();
        $this->assertCount(1, $products);
        
        // Verify cache has it
        // We can check if second query hits DB.
        
        \DB::enableQueryLog();
        $products2 = Product::all();
        $this->assertCount(1, $products2);
        
        $log = \DB::getQueryLog();
        $this->assertCount(0, $log); // Should be 0 queries
    }

    /** @test */
    public function it_invalidates_cache_on_update()
    {
        $product = Product::create(['name' => 'Laptop']);
        Product::all(); // Cache it

        $product->update(['name' => 'Desktop']);
        
        \DB::enableQueryLog();
        $products = Product::all();
        $log = \DB::getQueryLog();
        
        $this->assertCount(1, $log); // Should re-query
        $this->assertEquals('Desktop', $products->first()->name);
    }

    /** @test */
    public function it_can_bypass_cache()
    {
        Product::create(['name' => 'Laptop']);
        Product::all(); // Cache it

        \DB::enableQueryLog();
        $products = Product::withoutCache()->get();
        $log = \DB::getQueryLog();
        
        $this->assertCount(1, $log);
    }
}

class Product extends Model
{
    use CachesQueries;
    protected $guarded = [];
}
