<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\{AuthController,ShipmentController,QuotationController,DocumentController,NotificationController,ProfileController};

Route::prefix('v1')->group(function () {
 Route::post('/auth/login',[AuthController::class,'login']);
 Route::post('/auth/register',[AuthController::class,'register']);
 Route::middleware('auth:sanctum')->group(function () {
  Route::post('/auth/logout',[AuthController::class,'logout']);
  Route::get('/dashboard',[ShipmentController::class,'dashboard']);
  Route::apiResource('shipments',ShipmentController::class)->only(['index','show']);
  Route::get('/shipments/{shipment}/tracking',[ShipmentController::class,'tracking']);
  Route::get('/shipments/{shipment}/documents',[DocumentController::class,'index']);
  Route::post('/shipments/{shipment}/documents',[DocumentController::class,'store']);
  Route::apiResource('quotations',QuotationController::class)->only(['index','store','show']);
  Route::get('/notifications',[NotificationController::class,'index']);
  Route::post('/notifications/{notification}/read',[NotificationController::class,'markRead']);
  Route::get('/profile',[ProfileController::class,'show']);
  Route::put('/profile',[ProfileController::class,'update']);
 });
});
