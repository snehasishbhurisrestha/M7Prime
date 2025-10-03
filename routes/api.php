<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\{
    ContactUsApiController,
    CategoryApiController,
    BrandApiController,
    ProductApiController,
    GoogleAuthApiController,
    AuthenticationApiController,
    CartApiController,
    CheckoutApiController,
    LocationApiController,
    UserApiController,
    ProductReviewApiController,
};


Route::post('contact-us',[ContactUsApiController::class,'store']);

Route::get('/categories', [CategoryApiController::class, 'index']);
Route::get('/categories/{id}', [CategoryApiController::class, 'show']);

Route::get('/brands', [BrandApiController::class, 'index']);
Route::get('/brands/{id}', [BrandApiController::class, 'show']);

Route::get('/products', [ProductApiController::class, 'index']);
Route::get('/products/category/{id}', [ProductApiController::class, 'byCategory']);
Route::get('/products/featured', [ProductApiController::class, 'featured']);
Route::get('/products/special', [ProductApiController::class, 'special']);
Route::get('/products/best-selling', [ProductApiController::class, 'bestSelling']);
Route::get('/products/{id}', [ProductApiController::class, 'show']);

Route::get('/auth/google/generate-url', [GoogleAuthApiController::class, 'redirectToGoogle']);
Route::get('/auth/google/callback', [GoogleAuthApiController::class, 'handleGoogleCallback']);

Route::post('/register', [AuthenticationApiController::class, 'register']);
Route::post('/login', [AuthenticationApiController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthenticationApiController::class, 'logout']);

    Route::post('/add-to-cart', [CartApiController::class, 'add_to_cart']);
    Route::get('/get-cart-items', [CartApiController::class, 'cart_items']);
    Route::post('/remove-from-cart', [CartApiController::class, 'remove_from_cart']);

    Route::get('/get-saved-address', [CheckoutApiController::class, 'get_saved_address']);
    Route::post('/add-new-addresss-book', [CheckoutApiController::class, 'add_new_addresss_book']);

    Route::get('create-razorpay-order',[CheckoutApiController::class, 'createRazorpayOrder']);
    Route::post('place-order',[CheckoutApiController::class, 'placeOrderWithRazorpay']);

    // Profile
    Route::get('/user/get-profile', [UserApiController::class, 'getProfile']);
    Route::post('/user/update-profile', [UserApiController::class, 'updateProfile']);

    // Orders
    Route::get('/user/orders', [UserApiController::class, 'getOrders']);
    Route::get('/user/orders/{id}', [UserApiController::class, 'getOrderDetails']);

    // Address
    Route::get('/user/get-addresses', [UserApiController::class, 'getAddresses']);
    Route::post('/user/add-addresses', [UserApiController::class, 'addAddress']);
    Route::put('/user/update-addresses/{id}', [UserApiController::class, 'updateAddress']);
    Route::delete('/user/delete-addresses/{id}', [UserApiController::class, 'deleteAddress']); 
});

Route::get('/countries', [LocationApiController::class, 'getCountries']);
Route::get('/states/{country_id}', [LocationApiController::class, 'getStates']);
Route::get('/cities/{state_id}', [LocationApiController::class, 'getCities']);

// Reviews
Route::get('reviews', [ProductReviewApiController::class, 'index']);
Route::get('reviews/{id}', [ProductReviewApiController::class, 'show']);