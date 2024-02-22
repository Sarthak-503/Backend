<?php

use Illuminate\Http\Request;
use App\Http\Controllers\api\AdaptationKeyController;
use App\Http\Controllers\api\UserAuthController;
use App\Http\Controllers\api\ResetPasswordController;
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

Route::post('/login/', [UserAuthController::class, 'login'])->name('login');
Route::get('/unauthorized/', [UserAuthController::class, 'unauthorized'])->name('unauthorized');
Route::post('/forgot-password', [ResetPasswordController::class, 'forgotPassword'])->name('forgotPassword');
Route::get('/verify-token', [ResetPasswordController::class, 'resetPasswordChecker'])->name('resetPasswordChecker');
Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('resetPassword');
Route::get('/translate', [AdaptationKeyController::class, 'translate']);
// Route::post('/index', [UserAuthController::class, 'checkToken'])->name('checkToken');
Route::get('keydata/{key}', [AdaptationKeyController::class, 'getKeyData']);

Route::middleware('auth:api')->group(function () {
    Route::get('adaptations', [AdaptationKeyController::class, 'getAllAdaptations']);
    Route::get('keys', [AdaptationKeyController::class, 'getAllKeys']);
    Route::post('create', [AdaptationKeyController::class, 'createKey']);
    Route::put('update', [AdaptationKeyController::class, 'updateKey']);
    Route::put('delete', [AdaptationKeyController::class, 'deleteKey']);
    Route::post('refresh', [UserAuthController::class, 'refreshToken'])->name('refresh');
    // Route::get('user/profile', [UserController::class, 'getUserProfile'])->name('getUserProfile');
    Route::put('user/settings/change-password', [UserAuthController::class, 'changePassword'])->name('changePassword');
    Route::post('logout', [UserAuthController::class, 'logout'])->name('logout');
});
