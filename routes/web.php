<?php

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\InstructorController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
Route::get('/admin/login', [AdminController::class, 'login'])->name('admin.login');
///// Admin Group Middleware
Route::middleware(['auth', 'roles:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    Route::get('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');
    Route::get('/admin/profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::post('/admin/profile/store', [AdminController::class, 'storeProfile'])->name('admin.profile.store');

    Route::get('/admin/change/password', [AdminController::class, 'changePassword'])->name('admin.change.password');
    Route::post('/admin/password/update', [AdminController::class, 'updatePassword'])->name('admin.password.update');
});

Route::get('/instructor/login', [InstructorController::class, 'login'])->name('instructor.login');
///// Instructor Group Middleware
Route::middleware(['auth','roles:instructor'])->group(function(){
    Route::get('/instructor/logout', [InstructorController::class, 'logout'])->name('instructor.logout');

    Route::get('/instructor/dashboard', [InstructorController::class, 'dashboard'])->name('instructor.dashboard');
    Route::get('/instructor/profile', [InstructorController::class, 'profile'])->name('instructor.profile');
    Route::post('/instructor/profile/store', [InstructorController::class, 'profileUpdate'])->name('instructor.profile.store');
    Route::get('/instructor/change/password', [InstructorController::class, 'changePassword'])->name('instructor.change.password');
    Route::post('/instructor/password/update', [InstructorController::class, 'updatePassword'])->name('instructor.password.update');
});
