<?php
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ContractorController;
use App\Http\Controllers\Admin\TemplateController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Counterparty\AuthController;
use App\Http\Controllers\Counterparty\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::prefix('admin')->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::resource('contractors', ContractorController::class);
    Route::resource('templates', TemplateController::class);
    Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
    Route::get('/templates/create', [TemplateController::class, 'create'])->name('templates.create');
    Route::post('/templates', [TemplateController::class, 'store'])->name('templates.store');
    Route::resource('documents', DocumentController::class);
});

Route::prefix('counterparty')->group(function () {
    Route::get('register', [AuthController::class, 'showRegister'])->name('counterparty.register');
    Route::post('register', [AuthController::class, 'register']);
    Route::get('login', [AuthController::class, 'showLogin'])->name('counterparty.login');
    Route::post('login', [AuthController::class, 'login']);
    Route::get('forgot-password', [AuthController::class, 'showForgotPassword'])->name('counterparty.forgot-password');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::get('reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('counterparty.reset-password');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('counterparty.reset-password.submit');
    Route::get('profile', [ProfileController::class, 'show'])->name('counterparty.profile')->middleware('auth:counterparty');
    Route::put('profile', [ProfileController::class, 'update'])->middleware('auth:counterparty');
});

Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::post('login', function () {
    $credentials = request()->only('email', 'password');
    if (Auth::attempt($credentials)) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('login')->with('error', 'Invalid credentials');
})->name('login.post');

Route::get('test-middleware', function () {
    return "Middleware test successful";
})->middleware([\App\Http\Middleware\AdminMiddleware::class])->name('test.middleware');

Route::get('logout', function () {
    Auth::logout();
    return redirect()->route('login')->with('success', 'Logged out successfully');
})->name('logout');
