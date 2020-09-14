<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*Route::get('/products', function () {
    $json = json_decode(file_get_contents("http://0.0.0.0:7000/feed/products"), true);
    dd($json);
});*/

Route::get('/products', [Controllers\ProductsController::class, 'getProducts']);

Route::get('/advertisements', [Controllers\AdsController::class, 'getAds']);
// Route::get('/advertisements', [Controllers\ProductsController::class, 'getAds']);


