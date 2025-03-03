<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductsController;

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

Route::get('all-products', [ProductsController::class, 'index']);
Route::post('add-products', [ProductsController::class, 'add_product']);

Route::post('update-products/{id}', [ProductsController::class, 'update_product']);
Route::get('edit-product/{id}', [ProductsController::class, 'edit_product']);
Route::get('delete-product/{id}', [ProductsController::class, 'delete_product']);
