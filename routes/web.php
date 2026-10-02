<?php

use App\Http\Controllers\AdminController;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\RegistrationVerificationController;
use App\Http\Controllers\PayMongoWebhookController;

Route::post('/paymongo/webhook', [PayMongoWebhookController::class, 'webhook'])->name('paymongo.webhook');

Route::middleware(['signed', 'throttle:20,1'])->group(function () {
    Route::get('/booking/payment/return/{booking}', [PayMongoWebhookController::class, 'complete'])
        ->name('booking.payment.return');
    Route::get('/booking/payment/cancel/{booking}', [PayMongoWebhookController::class, 'cancel'])
        ->name('booking.payment.cancel');
});

route::get('/', [HomeController::class, 'my_home']);

Route::view('/install-app', 'home.install-app')->name('mobile-app.install');

Route::get('/download-app', function () {
    return response()->download(public_path('downloads/Mi-Cusina.apk'), 'Mi-Cusina.apk', [
        'Content-Type' => 'application/vnd.android.package-archive',
        'Cache-Control' => 'private, no-store, max-age=0',
    ])->setPrivate();
})->name('mobile-app.download');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegistrationVerificationController::class, 'create'])->name('register');
    Route::post('/register', [RegistrationVerificationController::class, 'send'])
        ->middleware('throttle:registration-email')
        ->name('register.send');
    Route::get('/register/verify', [RegistrationVerificationController::class, 'verification'])
        ->name('register.verify');
    Route::post('/register/verify', [RegistrationVerificationController::class, 'confirm'])
        ->middleware('throttle:registration-code')
        ->name('register.confirm');
});

// Guests may build a cart in their session. Checkout remains protected below,
// where Laravel will send unauthenticated customers to the login page.
Route::post('/add_cart/{id}', [HomeController::class, 'add_cart']);
Route::post('/add_cart_ajax/{id}', [HomeController::class, 'add_cart_ajax']);
Route::get('/my_cart', [HomeController::class, 'my_cart']);
Route::post('/update_cart/{id}', [HomeController::class, 'update_cart']);
Route::delete('/remove_cart/{id}', [HomeController::class, 'remove_cart']);


Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');
    Route::redirect('/home', '/dashboard');
    Route::post('/customer/logout', [HomeController::class, 'logout'])->name('customer.logout');
    Route::post('/admin/profile-photo', [AdminController::class, 'update_profile_photo'])->name('admin.profile-photo.update');
    Route::post('/admin/notifications/read', [AdminController::class, 'markNotificationsRead'])->name('admin.notifications.read');
    Route::post('/customer/profile-photo', [HomeController::class, 'update_profile_photo'])->name('customer.profile-photo.update');

    Route::get('/add_food', [AdminController::class, 'add_food']);
    Route::post('/upload_food', [AdminController::class, 'upload_food']);
    Route::get('/view_food', [AdminController::class, 'view_food']);
    Route::get('/inventory', [AdminController::class, 'inventory']);
    Route::post('/update_stock/{id}', [AdminController::class, 'update_stock']);
    Route::delete('/delete_food/{id}', [AdminController::class, 'delete_food']);
    Route::get('/update_food/{id}', [AdminController::class, 'update_food']);
    Route::post('/edit_food/{id}', [AdminController::class, 'edit_food']);

    Route::get('/checkout', [HomeController::class, 'checkout'])->name('checkout');
    Route::get('/my_orders', [HomeController::class, 'my_orders']);
    Route::get('/track_order/{id}', [HomeController::class, 'track_order']);
    Route::get('/track_order/{id}/status', [HomeController::class, 'order_status']);
    Route::post('/orders/{id}/cancel', [HomeController::class, 'cancel_order'])->name('orders.cancel');
    Route::get('/order_receipt', [HomeController::class, 'order_receipt'])->name('order.receipt');
    Route::post('/confirm_order', [HomeController::class, 'confirm_order']);

    Route::get('/orders', [AdminController::class, 'orders'])->name('admin.orders');
    Route::get('/orders/updates', [AdminController::class, 'order_updates'])->name('orders.updates');
    Route::get('/sales-report', [AdminController::class, 'sales_report'])->name('admin.sales-report');
    Route::get('/sales-report/pdf', [AdminController::class, 'sales_report_pdf'])->name('admin.sales-report.pdf');
    Route::get('/transaction-history', [AdminController::class, 'transaction_history'])->name('admin.transaction-history');
    Route::get('/transaction-history/pdf', [AdminController::class, 'transaction_history_pdf'])->name('admin.transaction-history.pdf');
    Route::get('/users', [AdminController::class, 'users']);
    Route::get('/riders', [AdminController::class, 'riders']);
    Route::post('/assign_rider/{id}', [AdminController::class, 'assign_rider']);
    Route::post('/rider_availability/{id}', [AdminController::class, 'set_rider_availability']);
    Route::post('/on_the_way/{id}', [AdminController::class, 'on_the_way']);
    Route::post('/delivered/{id}', [AdminController::class, 'delivered']);
    Route::post('/canceled/{id}', [AdminController::class, 'canceled']);
    Route::post('/paid/{id}', [AdminController::class, 'paid']);

    Route::post('/book_table', [HomeController::class, 'book_table'])->middleware('throttle:10,1');
    Route::get('/my_bookings', [HomeController::class, 'my_bookings'])->name('bookings.index');
    Route::get('/track_booking/{id}', [HomeController::class, 'track_booking'])->name('bookings.track');
    Route::get('/track_booking/{id}/status', [HomeController::class, 'booking_status']);
    Route::post('/bookings/{id}/cancel', [HomeController::class, 'cancel_booking'])->name('bookings.cancel');
    Route::get('/reservations', [AdminController::class, 'reservations']);
    Route::post('/approve_reservation/{id}', [AdminController::class, 'approve_reservation']);
    Route::post('/verify_reservation_payment/{id}', [AdminController::class, 'verify_reservation_payment']);
    Route::get('/add_staff', [AdminController::class, 'add_staff']);
    Route::post('/store_staff', [AdminController::class, 'store_staff']);
    Route::post('/chatbot/message', [HomeController::class, 'chatbot_message'])->middleware('throttle:30,1');
});
