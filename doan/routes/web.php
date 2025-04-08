<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Auth\Events\Login;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\LoginSocialController;
use App\Http\Controllers\ShopController;
use App\Models\Brand;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;



Route::get('/', [FrontController::class, 'index'])->name('front.home');
Route::get('/shop/{categorySlug?}/{subCategorySlug?}', [ShopController::class, 'index'])->name('front.shop');
Route::get('/product/{slug}', [ShopController::class, 'product'])->name('front.product');
Route::get('/cart', [CartController::class, 'cart'])->name('front.cart');
Route::post('/add-to-cart', [CartController::class, 'addToCart'])->name('front.addToCart');
Route::post('/update-cart', [CartController::class, 'updateCart'])->name('front.updateCart');
Route::post('/delete-item', [CartController::class, 'deleteItem'])->name('front.deleteItem.cart');
Route::get('/checkout', [CartController::class, 'checkout'])->name('front.checkout');
Route::post('/process-checkout', [CartController::class, 'processCheckout'])->name('front.processCheckout');
Route::get('/thanks/{orderId}', [CartController::class, 'thankyou'])->name('front.thankyou');
Route::post('/get-order-summery', [CartController::class, 'getOrderSummery'])->name('front.getOrderSummery');
Route::post('/apply-discount', [CartController::class, 'applyDiscount'])->name('front.applyDiscount');
Route::post('/remove-discount', [CartController::class, 'removeCoupon'])->name('front.removeCoupon');
Route::post('/add-to-wishlist', [FrontController::class, 'addToWishlist'])->name('front.addToWishlist');
Route::get('/page/{slug}', [FrontController::class, 'page'])->name('front.page');
Route::post('/send-email-contact', [FrontController::class, 'sendContactEmail'])->name('front.sendContactEmail');


//Checkout VnPay
Route::get('/proceed-to-vnPay', [CheckoutController::class, 'show'])->name('front.showCheckout');
Route::post('/checkout-vnpay', [CheckoutController::class, 'checkoutVnpay'])->name('front.checkoutVnpay');
Route::get('/thanksVnpay', [CheckoutController::class, 'thankyouVnpay'])->name('front.thankyouVnpay');

//Checkout Momo
Route::post('/checkout-momo', [CheckoutController::class, 'checkoutMomo'])->name('front.checkoutMomo');

//About Us
Route::get('/about-us', [FrontController::class, 'aboutUs'])->name('front.aboutUs');

//Contact Us
Route::get('/contact-us', [FrontController::class, 'contactUs'])->name('front.contactUs');


Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('front.forgotPassword');
Route::post('/process-forgot-password', [AuthController::class, 'processForgotPassword'])->name('front.processForgotPassword');
Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('front.resetPassword');
Route::post('/process-reset-password', [AuthController::class, 'processResetPassword'])->name('front.processResetPassword');
Route::post('/save-rating/{productId}', [ShopController::class, 'saveRating'])->name('front.saveRating');

// Language Mutigual
Route::get('language/{locale}', [LanguageController::class, 'index'])->name('front.language');


// Detail News
Route::get('/detail-news/{id}', [FrontController::class, 'detailNew'])->name('front.detailNew');



Route::group(['prefix' => 'account'], function () {
    Route::group(['middleware' => 'guest'], function () {
        // Authenciation user
        Route::get('/register', [AuthController::class, 'register'])->name('account.register');
        Route::post('/process-register', [AuthController::class, 'processRegister'])->name('account.processRegister');

        //Login Home Page
        Route::post('/login', [AuthController::class, 'authenticate'])->name('account.authenticate');
        Route::get('/login', [AuthController::class, 'login'])->name('account.login');
        //Login Google
        Route::get('/login-google', [LoginSocialController::class, 'login_google'])->name('account.loginGoogle');
        Route::get('/google/callback', [LoginSocialController::class, 'callback_google'])->name('account.callback');
    });

    Route::group(['middleware' => 'auth'], function () {
        //Log out
        Route::post('/logout', [AuthController::class, 'logout'])->name('account.logout');
        Route::post('/update-profile', [AuthController::class, 'updateProfile'])->name('account.updateProfile');
        Route::post('/update-address', [AuthController::class, 'updateAddress'])->name('account.updateAddress');
        Route::get('/profile', [AuthController::class, 'profile'])->name('account.profile');
        Route::get('/change-password', [AuthController::class, 'showChangePasswordForm'])->name('account.changePassword');
        Route::post('/process-change-password', [AuthController::class, 'changePassword'])->name('account.processChangePassword');
        Route::get('/my-wish-list', [AuthController::class, 'wishlist'])->name('account.wishlist');
        Route::post('/remove-product-from-wishlist', [AuthController::class, 'removeProductFromWishList'])->name('account.removeProductFromWishList');
        Route::get('/my-order', [AuthController::class, 'orders'])->name('account.orders');
        Route::get('/order-detail/{orderId}', [AuthController::class, 'orderDetail'])->name('account.orderDetail');
    });
});

