<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\MypageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ExhibitionController;

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

Route::get('/', [ItemController::class, 'showIndex'])->name('home');
Route::get('/item/{item}', [ItemController::class, 'showItemDetail'])->name('item.show');

Route::get('/purchase/success', [PurchaseController::class, 'handlePurchaseSuccess'])->name('purchase.success');
Route::get('/purchase/cancel', [PurchaseController::class, 'handlePurchaseCancel'])->name('purchase.cancel');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/purchase/{item}', [PurchaseController::class, 'showPurchase'])->name('purchase.show');
    Route::get('/purchase/address/{item}', [PurchaseController::class, 'showPurchaseAddress'])->name('purchase.address');
    Route::post('/purchase/{item}', [PurchaseController::class, 'storePurchase'])->name('purchase.store');
    Route::post('/purchase/address/{item}', [PurchaseController::class, 'storePurchaseAddress'])->name('purchase.address.store');
    Route::get('/sell', [ExhibitionController::class, 'showSellForm'])->name('sell.show');
    Route::post('/sell', [ExhibitionController::class, 'storeSellItem'])->name('sell.store');
    Route::get('/mypage', [MypageController::class, 'showMyPage'])->name('mypage');
    Route::get('/mypage/profile', [MypageController::class, 'showMyPageProfile'])->name('mypage.profile');
    Route::post('/mypage/profile', [MypageController::class, 'updateMyPageProfile'])->name('mypage.profile.store');
    Route::post('/item/{item}/comment', [ItemController::class, 'storeComment'])->name('item.comment');
    Route::post('/item/{item}/favorite', [ItemController::class, 'toggleFavorite'])->name('item.favorite');
});
