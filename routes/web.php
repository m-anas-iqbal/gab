<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\SubscriptionPlanController;
use Illuminate\Support\Facades\Http;
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
Route::get('/proxy-image', function () {
    $imageUrl = request('url');
    $response = Http::get($imageUrl);
    return response($response->body(), 200)
    ->header('Content-Type', 'image/png')
    ->header('Access-Control-Allow-Origin', '*'); });

Route::get('/clear-cache', function () {
	Artisan::call('view:clear');
	Artisan::call('route:clear');
	Artisan::call('config:cache');
	Artisan::call('cache:clear');
	return "cleared successfully";
 });

 Route::get('/migrate', function () {
    Artisan::call('migrate');
    return "Migrate successfully";
 });
 Route::get('/queue', function () {
      echo "Queue Work successfully";
     return Artisan::call('queue:work');
  });

