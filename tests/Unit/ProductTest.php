<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductCollection;
use App\User;

class ProductTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function create_an_product_with_valid_data()
    {
        $data = [
          'name' => $this->faker->word,
          'description' => $this->faker->paragraph,
          'price' => $this->faker->numberBetween(100,1000),
          'stock' => $this->faker->randomDigit,
          'discount' => $this->faker->numberBetween(2,30)
        ];
        $this->withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'])
          ->post(route('products.store'), $data)
          ->dump()
          ->assertStatus(Response::HTTP_CREATED)
          ->assertJson([
            'data' => new ProductResource($data)
        ]);
    }

    /**
     * @test
     */
    public function get_an_product()
    {
        $id = 6;
        $this->withHeaders([
            'Accept' => 'application/json'])
          ->get(route('products.show'), $id)
          ->assertStatus(200)
          ->assertJson([
            'data' => new ProductResource($data)
          ]);
    }
}
