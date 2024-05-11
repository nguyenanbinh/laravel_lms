<?php

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\InstructorController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', [UserController::class, 'index'])->name('index');

Route::get('/dashboard', function () {
    return view('frontend.dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/user/profile/update', [UserController::class, 'updateProfile'])->name('user.profile.update');
    Route::get('/user/logout', [UserController::class, 'logout'])->name('user.logout');
    Route::get('/user/change/password', [UserController::class, 'changePassword'])->name('user.change.password');
    Route::post('/user/password/update', [UserController::class, 'updatePassword'])->name('user.password.update');
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

    // Category All Route
    Route::controller(CategoryController::class)->group(function () {
        Route::get('/categories', 'index')->name('categories.index');
        Route::get('/categories/create', 'create')->name('categories.create');
        Route::post('/categories/store', 'store')->name('categories.store');
        Route::get('/categories/{id}/edit', 'edit')->name('categories.edit');
        Route::post('/categories/update', 'update')->name('categories.update');
        Route::get('/categories//{id}/delete','delete')->name('categories.delete');
        // Subcategory
        Route::get('subcategories','allSubCategory')->name('subcategories.index');
        Route::get('subcategories/create','createSubCategory')->name('subcategories.create');
        Route::post('subcategories/store','storeSubCategory')->name('subcategories.store');
        Route::get('subcategories/{id}/edit','editSubCategory')->name('subcategories.edit');
        Route::post('subcategories/update','updateSubCategory')->name('subcategories.update');
        Route::get('subcategories/{id}/delete','deleteSubCategory')->name('subcategories.delete');
    });
});

Route::get('/instructor/login', [InstructorController::class, 'login'])->name('instructor.login');
///// Instructor Group Middleware
Route::middleware(['auth', 'roles:instructor'])->group(function () {
    Route::get('/instructor/logout', [InstructorController::class, 'logout'])->name('instructor.logout');

    Route::get('/instructor/dashboard', [InstructorController::class, 'dashboard'])->name('instructor.dashboard');
    Route::get('/instructor/profile', [InstructorController::class, 'profile'])->name('instructor.profile');
    Route::post('/instructor/profile/store', [InstructorController::class, 'profileUpdate'])->name('instructor.profile.store');
    Route::get('/instructor/change/password', [InstructorController::class, 'changePassword'])->name('instructor.change.password');
    Route::post('/instructor/password/update', [InstructorController::class, 'updatePassword'])->name('instructor.password.update');
});
