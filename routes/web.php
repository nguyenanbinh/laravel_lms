<?php

use App\Http\Controllers\Backend\AdminController;
use App\Http\Controllers\Backend\CategoryController;
use App\Http\Controllers\Backend\CouponController;
use App\Http\Controllers\Backend\CourseController;
use App\Http\Controllers\Backend\InstructorController;
use App\Http\Controllers\Backend\UserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Frontend\IndexController;
use App\Http\Controllers\Frontend\WishListController;
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

    // User Wishlist All Route
    Route::controller(WishListController::class)->group(function () {
        Route::get('/user/wishlist', 'getWishlist')->name('user.wishlist');
        Route::get('/get-wishlist-course', 'getWishlistCourse');
        Route::get('/wishlist-remove/{id}', 'removeWishlist');
    });
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
        Route::get('/categories//{id}/delete', 'delete')->name('categories.delete');
        // Subcategory
        Route::get('subcategories', 'allSubCategory')->name('subcategories.index');
        Route::get('subcategories/create', 'createSubCategory')->name('subcategories.create');
        Route::post('subcategories/store', 'storeSubCategory')->name('subcategories.store');
        Route::get('subcategories/{id}/edit', 'editSubCategory')->name('subcategories.edit');
        Route::post('subcategories/update', 'updateSubCategory')->name('subcategories.update');
        Route::get('subcategories/{id}/delete', 'deleteSubCategory')->name('subcategories.delete');
    });

    // Instructor All Route
    Route::controller(AdminController::class)->group(function () {
        Route::get('instructors', 'getInstructors')->name('instructor.index');
        Route::post('/update/user/status', 'updateUserStatus')->name('update.user.status');
        Route::get('/admin/course/details/{id}','adminCourseDetails')->name('admin.course.details');
    });

    // Admin Coruses All Route
    Route::controller(AdminController::class)->group(function () {
        Route::get('/admin/all/course', 'adminAllCourse')->name('admin.all.course');
        Route::post('/update/course/status','UpdateCourseStatus')->name('update.course.status');
    });

    // Admin Coupon All Route
    Route::controller(CouponController::class)->group(function(){
        Route::get('/admin/all/coupon','AdminAllCoupon')->name('admin.all.coupon');
        Route::get('/admin/add/coupon','AdminAddCoupon')->name('admin.add.coupon');
        Route::post('/admin/store/coupon','AdminStoreCoupon')->name('admin.store.coupon');
        Route::get('/admin/edit/coupon/{id}','AdminEditCoupon')->name('admin.edit.coupon');
        Route::post('/admin/update/coupon','AdminUpdateCoupon')->name('admin.update.coupon');
        Route::get('/admin/delete/coupon/{id}','AdminDeleteCoupon')->name('admin.delete.coupon');
    });
});

Route::get('/instructor/login', [InstructorController::class, 'login'])->name('instructor.login');
Route::get('/become/instructor', [AdminController::class, 'becomeInstructor'])->name('instructor.become');
Route::post('/instructor/register', [AdminController::class, 'registerInstructor'])->name('instructor.register');

///// Instructor Group Middleware
Route::middleware(['auth', 'roles:instructor'])->group(function () {
    Route::get('/instructor/logout', [InstructorController::class, 'logout'])->name('instructor.logout');

    Route::get('/instructor/dashboard', [InstructorController::class, 'dashboard'])->name('instructor.dashboard');
    Route::get('/instructor/profile', [InstructorController::class, 'profile'])->name('instructor.profile');
    Route::post('/instructor/profile/store', [InstructorController::class, 'profileUpdate'])->name('instructor.profile.store');
    Route::get('/instructor/change/password', [InstructorController::class, 'changePassword'])->name('instructor.change.password');
    Route::post('/instructor/password/update', [InstructorController::class, 'updatePassword'])->name('instructor.password.update');

    // Instructor All Route
    Route::controller(CourseController::class)->group(function () {
        Route::get('instructor/courses', 'index')->name('instructor.courses.index');
        Route::get('instructor/courses/create', 'create')->name('instructor.courses.create');
        Route::get('subcategory/ajax/{category_id}', 'getSubCategory');
        Route::post('instructor/course/store', 'store')->name('instructor.courses.store');
        Route::get('instructor/course/{id}/edit', 'edit')->name('instructor.courses.edit');
        Route::post('instructor/course/update', 'update')->name('instructor.courses.update');
        Route::post('instructor/course/image/update', 'updateCourseImage')->name('instructor.courses.image.update');
        Route::post('instructor/course/video/update', 'updateCourseVideo')->name('instructor.courses.video.update');
        Route::post('instructor/course/goal/update', 'updateCourseGoal')->name('instructor.courses.goal.update');
        Route::get('instructor/course/{id}/delete', 'delete')->name('instructor.courses.delete');

        // Course Section and Lecture All Route
        Route::controller(CourseController::class)->group(function () {
            Route::get('/instructor/course/lecture/{id}/create', 'createCourseLecture')->name('instructor.courses.lecture.create');
            Route::post('/instructor/course/section/store', 'storeCourseSection')->name('instructor.courses.section.store');
            Route::post('/save-lecture', 'saveLecture')->name('save-lecture');
            Route::get('/instructor/course/lecture/{id}/edit', 'editLecture')->name('instructor.courses.lecture.edit');
            Route::post('/instructor/course/lecture/update', 'updateCourseLecture')->name('instructor.courses.lecture.update');
            Route::get('/instructor/course/lecture/{id}', 'deleteLecture')->name('instructor.courses.lecture.delete');
            Route::post('/instructor/course/section/{id}/delete', 'deleteSection')->name('instructor.courses.section.delete');
        });
    });
});

///// Route Accessable for All
Route::get('/course/details/{id}/{slug}', [IndexController::class, 'courseDetails']);
Route::get('/category/{id}/{slug}', [IndexController::class, 'categoryCourse']);
Route::get('/subcategory/{id}/{slug}', [IndexController::class, 'subCategoryCourse']);
Route::get('/instructor/details/{id}', [IndexController::class, 'instructorDetails'])->name('instructor.details');

Route::post('/add-to-wishlist/{course_id}', [WishListController::class, 'addToWishList']);

Route::post('/cart/data/store/{id}', [CartController::class, 'addToCart']);

// Get Data from Minicart
Route::get('/mini-cart/course/remove/{rowId}', [CartController::class, 'RemoveMiniCart']);

// Cart All Route
Route::controller(CartController::class)->group(function () {
    Route::get('/mycart', 'myCart')->name('mycart');
    Route::get('/cart/data', 'cartData');
    Route::get('/cart-remove/{rowId}', 'cartRemove');
});

Route::post('/coupon-apply', [CartController::class, 'couponApply']);
Route::get('/coupon-calculation', [CartController::class, 'couponCalculation']);
Route::get('/coupon-remove', [CartController::class, 'couponRemove']);
///// End Route Accessable for All

