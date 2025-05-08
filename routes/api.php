<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);
Route::post('auth/verify', [AuthController::class, 'verify']);
Route::post('auth/forgot-password', [AuthController::class, 'forgottenPassword']);
Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:api')->group(function () {
    Route::get('/auth/profile', [AuthController::class, 'profile']);
    Route::get('/auth/delete-user', [AuthController::class, 'deleteAccount']); //admin can delete any user
    // Finish AuthController routes
    Route::post('/invoices-upload', [StudentController::class, 'invoicesUpload']);
    Route::get('/invoices-list', [StudentController::class, 'invoicesList']);
    Route::get('/invoices-list/{id}', [StudentController::class, 'invoicesGet']);
    Route::post('/invoices-update/{id}', [StudentController::class, 'invoicesUpdate']);
    // route to get invoice by rrr
    Route::get('/invoices-get-by-rrr/{rrr}', [StudentController::class, 'invoicesGetByRrr']);
    // route to query invoice by date
    Route::get('/invoices-query-by-date', [StudentController::class, 'invoicesQueryByDate']);
    Route::delete('/invoices-delete/{id}', [StudentController::class, 'invoicesDelete']);
    // route to get all payments
    Route::get('/payments-list', [StudentController::class, 'getAllPayments']); //admin can get all payments
    

    // Route::get("refresh", [AuthController::class, "refreshToken"]);
    Route::get("auth/logout", [AuthController::class, "logout"]);
});

Route::get("/debug", function () {
    return response()->json([
        'message' => 'Debug route is working',
        'status' => true,
    ]);
});
Route::get("/test", function () {
    return response()->json([
        'message' => 'Test route is working',
        'status' => true,
    ]);
});
