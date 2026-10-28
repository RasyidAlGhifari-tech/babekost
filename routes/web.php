<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Tenant\BookingController;
use App\Http\Controllers\Tenant\ComplaintController;
use App\Http\Controllers\Tenant\HomeController as TenantHomeController;
use App\Http\Controllers\Tenant\PaymentController;
use App\Http\Controllers\Tenant\ProfileController as TenantProfileController;
use App\Http\Controllers\Visitor\GalleryController;
use App\Http\Controllers\Visitor\HomeController;
use App\Http\Controllers\Visitor\NewsController;
use App\Http\Controllers\Visitor\RoomController;
use Illuminate\Support\Facades\Route;

// ---------- Pengunjung ----------
Route::get('/', HomeController::class)->name('home');

Route::get('/rooms', [RoomController::class, 'index'])->name('visitor.rooms');
Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('visitor.rooms.show');

Route::get('/gallery', GalleryController::class)->name('visitor.gallery');

Route::get('/news', NewsController::class)->name('visitor.news');

// ---------- Penghuni ----------
Route::middleware(['auth', 'role:penyewa'])
    ->prefix('penghuni')->name('tenant.')
    ->group(function () {
        Route::get('/', TenantHomeController::class)->name('home');

        Route::get('/booking', [BookingController::class, 'index'])->name('booking');
        Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');

        Route::get('/payment', [PaymentController::class, 'index'])->name('payment');
        Route::post('/payment', [PaymentController::class, 'store'])->name('payment.store');

        Route::get('/complaints', [ComplaintController::class, 'index'])->name('complaints');
        Route::post('/complaints', [ComplaintController::class, 'store'])->name('complaints.store');

        Route::get('/profile', [TenantProfileController::class, 'show'])->name('profile');
        Route::get('/profile/edit', [TenantProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile/edit', [TenantProfileController::class, 'update'])->name('profile.update');
        Route::get('/profile/password', [TenantProfileController::class, 'password'])->name('profile.password');
        Route::put('/profile/password', [TenantProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::get('/profile/contact', [TenantProfileController::class, 'contact'])->name('profile.contact');
    });

// ---------- Admin (pemilik) ----------
Route::middleware(['auth', 'role:admin'])
    ->prefix('pemilik')->name('admin.')
    ->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');

        // Rooms (rute statis /create dulu, baru yang berparameter)
        Route::get('/rooms', [Admin\RoomController::class, 'index'])->name('rooms');
        Route::get('/rooms/create', [Admin\RoomController::class, 'create'])->name('rooms.create');
        Route::post('/rooms', [Admin\RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}/edit', [Admin\RoomController::class, 'edit'])->name('rooms.edit');
        Route::put('/rooms/{room}', [Admin\RoomController::class, 'update'])->name('rooms.update');
        Route::patch('/rooms/{room}/status', [Admin\RoomController::class, 'status'])->name('rooms.status');
        Route::delete('/rooms/{room}', [Admin\RoomController::class, 'destroy'])->name('rooms.destroy');

        // Finance (rute statis dulu, baru yang berparameter)
        Route::get('/finance', [Admin\FinanceController::class, 'index'])->name('finance');
        Route::post('/finance/expenses', [Admin\FinanceController::class, 'storeExpense'])->name('finance.expenses.store');
        Route::delete('/finance/expenses/{expense}', [Admin\FinanceController::class, 'destroyExpense'])->name('finance.expenses.destroy');
        Route::get('/finance/export/{type}', [Admin\FinanceController::class, 'export'])
            ->whereIn('type', ['pdf', 'excel'])->name('finance.export');
        Route::post('/finance/{payment}/approve', [Admin\FinanceController::class, 'approve'])->name('finance.approve');
        Route::post('/finance/{payment}/reject', [Admin\FinanceController::class, 'reject'])->name('finance.reject');
        Route::get('/finance/{payment}/proof', [Admin\FinanceController::class, 'proof'])->name('finance.proof');

        // More
        Route::get('/more', [Admin\MoreController::class, 'index'])->name('more');
        Route::get('/password', [Admin\MoreController::class, 'password'])->name('password');
        Route::put('/password', [Admin\MoreController::class, 'updatePassword'])->name('password.update');

        Route::get('/bookings', [Admin\BookingController::class, 'index'])->name('bookings');
        Route::post('/bookings/{booking}/approve', [Admin\BookingController::class, 'approve'])->name('bookings.approve');
        Route::post('/bookings/{booking}/reject', [Admin\BookingController::class, 'reject'])->name('bookings.reject');

        Route::get('/complaints', [Admin\ComplaintController::class, 'index'])->name('complaints');
        Route::post('/complaints/{complaint}', [Admin\ComplaintController::class, 'update'])->name('complaints.update');

        Route::get('/facilities', [Admin\FacilityController::class, 'index'])->name('facilities');
        Route::post('/facilities', [Admin\FacilityController::class, 'store'])->name('facilities.store');
        Route::put('/facilities/{facility}', [Admin\FacilityController::class, 'update'])->name('facilities.update');
        Route::delete('/facilities/{facility}', [Admin\FacilityController::class, 'destroy'])->name('facilities.destroy');
        Route::post('/rooms/{room}/facilities', [Admin\FacilityController::class, 'attach'])->name('facilities.attach');
        Route::delete('/rooms/{room}/facilities/{facility}', [Admin\FacilityController::class, 'detach'])->name('facilities.detach');

        Route::get('/gallery', [Admin\GalleryController::class, 'index'])->name('gallery');
        Route::post('/buildings', [Admin\GalleryController::class, 'storeBuilding'])->name('buildings.store');
        Route::delete('/buildings/{building}', [Admin\GalleryController::class, 'destroyBuilding'])->name('buildings.destroy');
        Route::post('/photos', [Admin\GalleryController::class, 'storePhoto'])->name('photos.store');
        Route::delete('/photos/{gallery}', [Admin\GalleryController::class, 'destroyPhoto'])->name('photos.destroy');

        Route::get('/promos', [Admin\PromoController::class, 'index'])->name('promos');
        Route::post('/promos', [Admin\PromoController::class, 'storePromo'])->name('promos.store');
        Route::post('/promos/{promo}/toggle', [Admin\PromoController::class, 'togglePromo'])->name('promos.toggle');
        Route::delete('/promos/{promo}', [Admin\PromoController::class, 'destroyPromo'])->name('promos.destroy');
        Route::post('/agendas', [Admin\PromoController::class, 'storeAgenda'])->name('agendas.store');
        Route::post('/agendas/{agenda}/done', [Admin\PromoController::class, 'doneAgenda'])->name('agendas.done');
        Route::delete('/agendas/{agenda}', [Admin\PromoController::class, 'destroyAgenda'])->name('agendas.destroy');

        // Accounts + tambah penyewa manual
        Route::get('/accounts', [Admin\TenantAccountController::class, 'index'])->name('accounts');
        Route::get('/accounts/create', [Admin\TenantRegistrationController::class, 'create'])->name('accounts.create');
        Route::post('/accounts', [Admin\TenantRegistrationController::class, 'store'])->name('accounts.store');
        Route::post('/accounts/{user}/reset', [Admin\TenantAccountController::class, 'reset'])->name('accounts.reset');
        Route::post('/accounts/{user}/toggle', [Admin\TenantAccountController::class, 'toggle'])->name('accounts.toggle');
    });

// /dashboard bawaan Breeze: arahkan sesuai role
Route::get('/dashboard', function () {
    return auth()->user()->isAdmin()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('tenant.home');
})->middleware(['auth'])->name('dashboard');

// ---------- Notifikasi (admin & penyewa) dan profil bawaan Breeze ----------
Route::middleware('auth')->group(function () {
    Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications');
    Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifikasi/{id}', [NotificationController::class, 'open'])->name('notifications.open');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';