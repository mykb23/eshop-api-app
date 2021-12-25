<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth;
use App\Http\Controllers\CartController;



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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['prefix' => 'v1'], function () {
    Route::post('/register', [Auth\AuthController::class, 'register'])->name('register');
    Route::get('/signup/activate/{token}', [Auth\AuthController::class, 'signupActivate']);
    Route::post('activation-token', [Auth\AuthController::class, 'requestNewVerificationToken']);
    Route::post('password-reset/create', [Auth\PasswordResetController::class, 'create']);
    Route::get('password-reset/{token}', [Auth\PasswordResetController::class, 'find']);
    Route::post('password-reset', [Auth\PasswordResetController::class, 'reset']);
    Route::get('product/', [ProductController::class, 'index'])->name('index.product');
    Route::get('product/{id}', [ProductController::class, 'show'])->name('show.product');
    // Route::post('rating', 'RatingController@store');
    Route::post('login', [Auth\AuthController::class, 'login'])->name('login');



    Route::middleware(['auth:sanctum'])->group(function () {
        Route::prefix('agent')->group(function () {
            Route::post('product/', [ProductController::class, 'store'])->name('product.create.');
            Route::put('product/{id}', [ProductController::class, 'update'])->name('product.update');
            Route::delete('product/{id}', [ProductController::class, 'destroy'])->name('product.delete');
        });

        Route::prefix('cart')->group(function () {
            Route::get('/', [CartController::class, 'index'])->name('cart.index');
            Route::post('/', [CartController::class, 'store'])->name('cart.store');
            Route::patch('update/{id}', [CartController::class, 'update'])->name('cart.update');
            Route::delete('remove/{id}', [CartController::class, 'destroy'])->name('cart.remove');
            Route::delete('clear', [CartController::class, 'clearAllCart'])->name('cart.clear');
        });

        Route::get('profile', [UserController::class, 'profile'])->name('profile');
        Route::patch('profile-update/{id}', [UserController::class, 'profileUpdate'])->name('updateProfile');
        Route::get('logout', [Auth\AuthController::class, 'logout'])->name('logout');

        Route::prefix('admin')->group(function () {
            Route::get('users/', [UserController::class, 'index'])->name('users.index');
            Route::get('users/{id}', [UserController::class, 'show'])->name('users.show');
            Route::patch('users/{id}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{id}', [UserController::class, 'destroy'])->name('users.delete');
        });
    });
});
