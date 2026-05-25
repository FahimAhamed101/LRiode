<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Middleware\AuthAdmin;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ShopController;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
Auth::routes();

//SHOP
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');
//CART
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::any('/cart/increasequantity/{rowId}', [CartController::class, 'increase_cart_quantity'])->name('cart.qty.increase');//put
Route::any('/cart/decreasequantity/{rowId}', [CartController::class, 'decrease_cart_quantity'])->name('cart.qty.decrease');//put
Route::delete('/cart/remove_item/{rowId}', [CartController::class, 'remove_item'])->name('cart.item.remove');
Route::delete('/cart/clear', [CartController::class, 'empty_cart'])->name('cart.empty');
Route::post('/cart/apply-coupon', [CartController::class, 'apply_coupon_code'])->name('cart.coupon.apply');
Route::delete('/cart/delete-coupon', [CartController::class, 'remove_coupon_code'])->name('cart.coupon.remove');
//WISHLIST
Route::post('/wishlist/add', [WishlistController::class, 'add_to_wishlist'])->name('wishlist.add');
Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::delete('/wishlist/remove_item/{rowId}', [WishlistController::class, 'remove_item'])->name('wishlist.item.remove');
Route::delete('/wishlist/clear', [WishlistController::class, 'empty_wishlist'])->name('wishlist.empty');
Route::post('/wishlist/move-to-cart/{rowId}', [WishlistController::class, 'move_to_cart'])->name('wishlist.to.cart');
Route::get('/wishlist/count', function () {
    return response()->json(['count' => Cart::instance('wishlist')->count()]);
});
//CHECKOUT
Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/place_an_order', [CartController::class, 'place_on_order'])->name('cart.place.on.order');
Route::get('/order_confirmation', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');
Route::get('/test-coupon', function() {
    return session()->all();
});

Route::get('/contact-us', [HomeController::class, 'contact'])->name('home.contact');
Route::post('/contact-store', [HomeController::class, 'contact_store'])->name('home.contact.store');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');
Route::get('/about', [HomeController::class, 'about'])->name('home.about');
Route::get('/search', [HomeController::class, 'search'])->name('home.search');



Route::middleware(['auth'])->group(function () {
    Route::get('/account-dashboard', [UserController::class, 'index'])->name('user.index');
    Route::get('/account-orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/account-orders/{order_id}/details', [UserController::class, 'order_details'])->name('user.order-details');
    Route::put('/account-order/cancel-order', [UserController::class, 'order_canceled'])->name('user.order.canceled');
    Route::get('/account-wishlist', [UserController::class, 'wishlist'])->name('user.wishlist');

});


Route::middleware(['auth', AuthAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/product/add', [AdminController::class, 'product_add'])->name('admin.product.add');
    Route::any('/admin/product/store', [AdminController::class, 'products_store'])->name('admin.product.store');
    Route::get('/admin/product/{id}/edit', [AdminController::class, 'product_edit'])->name('admin.product.edit');
    Route::put('/admin/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
    Route::delete('/admin/product/{id}/delete', [AdminController::class, 'product_delete'])->name('admin.product.delete');

 //BRAND
 Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
 Route::get('/admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
 Route::post('/admin/brand/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
 Route::get('/admin/brand/edit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
 Route::put('/admin/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
 Route::delete('/admin/brand/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');
 //CATEGORY
 Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
 Route::get('/admin/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
 Route::post('/admin/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');
 Route::get('/admin/category/{id}/edit', [AdminController::class, 'category_edit'])->name('admin.category.edit');
 Route::put('/admin/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
 Route::delete('/admin/category/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');
//SLIDE
Route::get('/admin/slides', [AdminController::class, 'slides'])->name('admin.slides');
Route::get('/admin/slide/add', [AdminController::class, 'slide_add'])->name('admin.slide.add');
Route::post('/admin/slide/store', [AdminController::class, 'slide_store'])->name('admin.slide.store');
Route::get('/admin/slide/{id}/edit', [AdminController::class, 'slide_edit'])->name('admin.slide.edit');
Route::put('/admin/slide/update', [AdminController::class, 'slide_update'])->name('admin.slide.update');
Route::delete('/admin/slide/{id}/delete', [AdminController::class, 'slide_delete'])->name('admin.slide.delete');
Route::get('/admin/orders', [AdminController::class, 'orders'])->name('admin.orders');
Route::get('/admin/order/{order_id}/details', [AdminController::class, 'order_details'])->name('admin.order-details');
Route::put('/admin/order/update_status', [AdminController::class, 'update_order_status'])->name('admin.order.status.update');

//COUPON
Route::get('/admin/coupons', [AdminController::class, 'coupons'])->name('admin.coupons');
Route::get('/admin/coupon/add', [AdminController::class, 'coupon_add'])->name('admin.coupon.add');
Route::any('/admin/coupon/store', [AdminController::class, 'coupons_store'])->name('admin.coupon.store');
Route::get('/admin/coupon/{id}/edit', [AdminController::class, 'coupon_edit'])->name('admin.coupon.edit');
Route::put('/admin/coupon/update', [AdminController::class, 'coupon_update'])->name('admin.coupon.update');
Route::delete('/admin/coupon/{id}/delete', [AdminController::class, 'coupon_delete'])->name('admin.coupon.delete');
Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');
 //CONTACT
 Route::get('/admin/contacts', [AdminController::class, 'contacts'])->name('admin.contacts');
 Route::delete('/admin/contact/{id}/delete', [AdminController::class, 'contact_delete'])->name('admin.contact.delete');

 Route::get('/admin/user', [AdminController::class, 'user_All'])->name('admin.user');
 Route::delete('/admin/user/{id}/delete', [AdminController::class, 'user_delete'])->name('admin.user.delete');
});    
