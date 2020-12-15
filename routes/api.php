<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/', function(){
    return response()->json('Hello World');
});

Route::post('product', function(Request $request){
    $model = new \App\Models\Product();

    $data = $model;
    $data->name = $request->name;
    $data->qty = $request->qty;
    $data->price = $request->price;
    $data->save();

    return response()->json([
        'request' => $request->all(),
        'data' => $data
    ]);
});

Route::post('transaction', function(Request $request){
    $productModel = new \App\Models\Product();
    $model = new \App\Models\Transaction();

    \DB::transaction(function () use ($request, $productModel, $model) {
        $product = $productModel->findOrFail($request->product_id);
        $product->qty -= $request->qty;
        $product->save();

        $data = $model;
        $data->product_id = $product->id;
        $data->qty = $request->qty;
        $data->price = $product->price;
        $data->save();

        return response()->json([
            'product' => $product,
            'data' => $data
        ]);
    });

    return response()->json([
        'message' => 'Something went wrong',
        'product' => $productModel->findOrFail($request->product_id)
    ]);
});