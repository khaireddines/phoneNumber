<?php

use App\Http\Controllers\PhoneNumbersController;
use App\Http\Controllers\SubAccountController;
use App\Http\Middleware\AuthToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

# Adding a Cache Control with E-tag hash for better performance also adding the security layer with the Auth Token (bearer Token)
Route::middleware(['cache.headers:public;max_age=26280;etag',AuthToken::class])->group(function (){
    Route::prefix('number')->group(function (){
        # /api/number/search
        Route::get('search', [PhoneNumbersController::class,'search']);
        # /api/number/order
        Route::post('order', [PhoneNumbersController::class,'order']);
    });
    Route::prefix('sub-account')->group(function (){
        # /api/sub-account/create
        Route::post('create',[SubAccountController::class,'create']);
    });
});
