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

Route::get('/', [ItemController::class, 'index'])->name('home');
Route::get('/item/{item}', [ItemController::class, 'item'])->name('item.show');

Route::get('/purchase/success', [PurchaseController::class, 'purchaseSuccess'])->name('purchase.success');
Route::get('/purchase/cancel', [PurchaseController::class, 'purchaseCancel'])->name('purchase.cancel');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/purchase/{item}', [PurchaseController::class, 'purchase'])->name('purchase.show');
    Route::get('/purchase/address/{item}', [PurchaseController::class, 'purchaseAddress'])->name('purchase.address');
    Route::post('/purchase/{item}', [PurchaseController::class, 'purchaseStore'])->name('purchase.store');
    Route::post('/purchase/address/{item}', [PurchaseController::class, 'purchaseAddressStore'])->name('purchase.address.store');
    Route::get('/sell', [ExhibitionController::class, 'sell'])->name('sell.show');
    Route::post('/sell', [ExhibitionController::class, 'sellStore'])->name('sell.store');
    Route::get('/mypage', [MypageController::class, 'mypage'])->name('mypage');
    Route::get('/mypage/profile', [MypageController::class, 'mypageProfile'])->name('mypage.profile');
    Route::post('/mypage/profile', [MypageController::class, 'mypageProfileStore'])->name('mypage.profile.store');
    Route::post('/item/{item}/comment', [ItemController::class, 'commentStore'])->name('item.comment');
    Route::post('/item/{item}/favorite', [ItemController::class, 'favoriteToggle'])->name('item.favorite');
});
