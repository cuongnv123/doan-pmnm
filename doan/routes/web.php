<?php

use App\Http\Controllers\admin\AdminLoginController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\admin\ColorController;
use App\Http\Controllers\admin\DiscountCodeController;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\HomeController;
use App\Http\Controllers\admin\NewsController;
use App\Http\Controllers\admin\OrderController;
use App\Http\Controllers\admin\OrderPrintController;
use App\Http\Controllers\admin\PageController;
use App\Http\Controllers\admin\ProductController;
use App\Http\Controllers\admin\ProductImageController;
use App\Http\Controllers\admin\ProductSubCategoryController;
use App\Http\Controllers\admin\SettingController;
use App\Http\Controllers\admin\ShippingController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\SizesController;
use App\Http\Controllers\admin\SubCategoryController;
use App\Http\Controllers\admin\TempImagesController;
use App\Http\Controllers\admin\UserController;
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

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/test', function () {
//     orderEmail(13);
// });


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
        Route::get('/logout', [AuthController::class, 'logout'])->name('account.logout');
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








Route::group(['prefix' => 'admin'], function () {


    Route::get('/login', [AdminLoginController::class, 'index'])->name('admin.login');
    Route::post('/authenticate', [AdminLoginController::class, 'authenticate'])->name('admin.authenticate');

    Route::group(['middleware' => 'admin.auth'], function () {});
    Route::get('/dashboard', [HomeController::class, 'index'])->name('admin.dashboard');
    Route::get('/dashboard1', [HomeController::class, 'index1'])->name('admin.dashboard1');
    Route::get('/export-revenue', [HomeController::class, 'export_revenue'])->name('admin.export_revenue');
    Route::get('/export-week-report', [HomeController::class, 'export_weeksaleReport'])->name('admin.export_weeksaleReport');
    Route::get('/export-month-report', [HomeController::class, 'export_monthsaleReport'])->name('admin.export_monthsaleReport');
    Route::get('/logout', [HomeController::class, 'logout'])->name('admin.logout');
    // Category Routes
    Route::get('/admin/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/admin/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/admin/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/admin/categories/edit/{category}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/admin/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/admin/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.delete');
    // SubCategory Routes
    Route::get('/sub-categories', [SubCategoryController::class, 'index'])->name('sub-categories.index');
    Route::get('/sub-categories/create', [SubCategoryController::class, 'create'])->name('sub-categories.create');
    Route::post('/sub-categories', [SubCategoryController::class, 'store'])->name('sub-categories.store');
    Route::get('/sub-categories/{subCategory}/edit', [SubCategoryController::class, 'edit'])->name('sub-categories.edit');
    Route::put('/sub-categories/{subCategory}', [SubCategoryController::class, 'update'])->name('sub-categories.update');
    Route::delete('/sub-categories/{subCategory}', [SubCategoryController::class, 'destroy'])->name('sub-categories.delete');


    // Brands routes
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::get('/brands/{brand}/edit', [BrandController::class, 'edit'])->name('brands.edit');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.delete');

    // Colors Routes
    Route::get('/colors', [ColorController::class, 'index'])->name('colors.index');
    Route::get('/colors/create', [ColorController::class, 'create'])->name('colors.create');
    Route::post('/colors', [ColorController::class, 'store'])->name('colors.store');
    Route::get('/colors/{color}/edit', [ColorController::class, 'edit'])->name('colors.edit');
    Route::put('/colors/{color}', [ColorController::class, 'update'])->name('colors.update');
    Route::delete('/colors/{color}', [ColorController::class, 'delete'])->name('colors.delete');



    // Sizes Routes
    Route::get('/size', [SizesController::class, 'index'])->name('size.index');
    Route::get('/size/create', [SizesController::class, 'create'])->name('size.create');
    Route::post('/size', [SizesController::class, 'store'])->name('size.store');
    Route::get('/size/{size}/edit', [SizesController::class, 'edit'])->name('size.edit');
    Route::put('/size/{size}', [SizesController::class, 'update'])->name('size.update');
    Route::delete('/size/{size}', [SizesController::class, 'delete'])->name('size.delete');


    // Products routes
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.delete');
    Route::get('/get-products', [ProductController::class, 'getProducts'])->name('products.getProducts');
    Route::get('/ratings', [ProductController::class, 'productRatings'])->name('products.productRatings');
    Route::get('/change-ratings-status', [ProductController::class, 'changeRatingStatus'])->name('products.changeRatingStatus');


    //Product subcategories
    Route::get('/products-subcategories', [ProductSubCategoryController::class, 'index'])->name('products-subcategories.index');

    Route::post('/product-images/update', [ProductImageController::class, 'update'])->name('product-images.update');
    Route::delete('/product-images', [ProductImageController::class, 'destroy'])->name('product-images.destroy');
    Route::post('/product-images/store-temp', [ProductImageController::class, 'storeTemp'])->name('product-images.store-temp');
    Route::post('/product-images/store-temp', [ProductImageController::class, 'storeTemp'])->name('product-images.storeTemp');

    //Shipping routes
    Route::get('/shipping/create', [ShippingController::class, 'create'])->name('shipping.create');
    Route::post('/shipping', [ShippingController::class, 'store'])->name('shipping.store');
    Route::get('/shipping/{id}', [ShippingController::class, 'edit'])->name('shipping.edit');
    Route::put('/shipping/{id}', [ShippingController::class, 'update'])->name('shipping.update');
    Route::delete('/shipping/{id}', [ShippingController::class, 'destroy'])->name('shipping.delete');

    // Coupon Code Routes
    Route::get('/coupons', [DiscountCodeController::class, 'index'])->name('coupons.index');
    Route::get('/coupons/create', [DiscountCodeController::class, 'create'])->name('coupons.create');
    Route::post('/coupons', [DiscountCodeController::class, 'store'])->name('coupons.store');
    Route::get('/coupons/{coupon}/edit', [DiscountCodeController::class, 'edit'])->name('coupons.edit');
    Route::put('/coupons/{coupon}', [DiscountCodeController::class, 'update'])->name('coupons.update');
    Route::delete('/coupons/{coupon}', [DiscountCodeController::class, 'destroy'])->name('coupons.delete');

    // Order Routes
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [OrderController::class, 'detail'])->name('orders.detail');
    Route::post('/orders/change-status/{id}', [OrderController::class, 'changeOrderStatus'])->name('orders.changeOrderStatus');
    Route::post('/orders/send-email/{id}', [OrderController::class, 'sendInvoiceEmail'])->name('orders.sendInvoiceEmail');

    //Print Orders
    Route::get('/orders/print-orders/{id}', [OrderPrintController::class, 'printOrder'])->name('orders.printOrder');

    //User Routes
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.delete');

    //Page routes
    Route::get('/pages', [PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/create', [PageController::class, 'create'])->name('pages.create');
    Route::post('/pages', [PageController::class, 'store'])->name('pages.store');
    Route::get('/pages/{page}/edit', [PageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [PageController::class, 'update'])->name('pages.update');
    Route::delete('/pages/{page}', [PageController::class, 'destroy'])->name('pages.delete');

    //News routes
    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->name('news.create');
    Route::post('/news', [NewsController::class, 'store'])->name('news.store');
    Route::get('/news/{new}/edit', [NewsController::class, 'edit'])->name('news.edit');
    Route::put('/news/{news}', [NewsController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}', [NewsController::class, 'destroy'])->name('news.delete');

    //Setting routes
    Route::get('/change-password', [SettingController::class, 'showChangePasswordForm'])->name('admin.showChangePasswordForm');
    Route::post('/process-change-password', [SettingController::class, 'processChangePassword'])->name('admin.processChangePassword');

    //temp-images.create
    Route::post('/admin/upload-temp-image', [TempImagesController::class, 'create'])->name('temp-images.create');



    Route::get('/getSlug', function (Request $request) {
        $slug = '';
        if (!empty($request->title)) {
            $slug = Str::slug($request->title);
        }
        return response()->json([
            'status' => true,
            'slug' => $slug
        ]);
    })->name('getSlug');
});