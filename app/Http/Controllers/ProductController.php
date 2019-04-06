<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductCollection;
use App\Model\Product;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return ProductCollection::collection(Product::paginate(20));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductRequest $request)
    {
        $product = new Product;
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->discount = $request->discount;
        $product->save();

        $this->store_redis($product->id, $request);        
        $this->log_print('[REDIS | DATABASE] Stored product detail for product: '.$product);

        return response([
          'data' => new ProductResource($product)
        ], Response::HTTP_CREATED);

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Model\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $stored = Redis::hgetall('product:'.$product->id);
        if (!empty($stored)) {
            $product_redis = new Product([
                'name' => $stored['name'],
                'description' => $stored['description'],
                'price' => $stored['price'],
                'stock' => $stored['stock'],
                'discount' => $stored['discount']
            ]);
            $this->log_print('[REDIS] Showing product detail for product: '.$product_redis);
            return new ProductResource($product_redis);
        } else {
            $product_redis = new Product([
                'name' => $product['name'],
                'description' => $product['description'],
                'price' => $product['price'],
                'stock' => $product['stock'],
                'discount' => $product['discount']
            ]);
            $this->store_redis($product->id, $product_redis);
            $this->log_print('[DATABASE] Showing product detail for product: '.$product);
            return new ProductResource($product);
        }
    }

    public function log_print($message){
        $console = new \Symfony\Component\Console\Output\ConsoleOutput();
        $console->writeln($message);
        Log::debug($message);
    }

    public function store_redis($id, $data){
        return Redis::hmset('product:'.$id, [
            'name' => $data->name,
            'description' => $data->description,
            'price' => $data->price,
            'stock' => $data->stock,
            'discount' => $data->discount
        ]);
    }

    public function destroy_redis($id){
        return Redis::del("product:".$id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Model\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $product->update($request->all());
        $this->store_redis($product->id, $request);

        return response([
          'data' => new ProductResource($product)
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Model\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        $this->destroy_redis($product->id);
        $product->delete();
        return response(null, Response::HTTP_NO_CONTENT);
    }
}
