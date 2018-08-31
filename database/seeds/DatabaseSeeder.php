<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UsersTableSeeder::class);
        $product = factory(App\Model\Product::class, 50)->create();
        $review = factory(App\Model\Review::class, 300)->create();
    }
}
