<?php

use App\Events\CsvImportEvent;
use App\Http\Controllers\CsvImportController;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::match(['get', 'post'], '/', CsvImportController::class);

Route::get('event', function () {
    \Log::info('This is a test log');
    broadcast(new CsvImportEvent("Csv import started"));
});
