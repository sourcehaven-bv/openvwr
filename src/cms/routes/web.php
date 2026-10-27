<?php

declare(strict_types=1);

use App\Config\Config;
use App\Enums\RouteName;
use App\Filament\Pages\OneTimePasswordValidation;
use App\Http\Controllers\Authentication\PasswordlessLoginController;
use App\Http\Controllers\Authentication\SnapshotSignLoginController;
use App\Http\Controllers\PrivateMediaController;
use App\Http\Controllers\RedirectToTenantController;
use App\Http\Controllers\TransferExportDownloadController;
use App\Http\Middleware\VerifyPratiqueAssertion;
use App\Services\Authentication\AuthenticationStrategyFactory;
use Illuminate\Support\Facades\Route;

// The landing redirect sits outside the panel, so it does not get the panel's
// auth middleware. Under pratique it needs the assertion to know who is asking
// and which organisation the proxy selected.
Route::get('/', RedirectToTenantController::class)
    ->middleware(Config::string('auth.driver') === AuthenticationStrategyFactory::DRIVER_PRATIQUE ? [VerifyPratiqueAssertion::class] : [])
    ->name(RouteName::HOME);

Route::prefix('/login/consume')->middleware('signed')->group(static function (): void {
    Route::get('/', [PasswordlessLoginController::class, 'consume'])->name(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONSUME);
    Route::post('/', [PasswordlessLoginController::class, 'confirm'])->name(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONFIRM);
});

Route::get('/media/{media}', PrivateMediaController::class)
    ->name(RouteName::MEDIA_PRIVATE);

Route::get('/transfer-export/{filename}', TransferExportDownloadController::class)
    ->middleware('signed')
    ->name(RouteName::TRANSFER_EXPORT_DOWNLOAD);

Route::prefix('/snapshot/sign')->middleware('signed')->group(static function (): void {
    Route::prefix('/batch')->group(static function (): void {
        Route::get('/', [SnapshotSignLoginController::class, 'openBatch'])->name(RouteName::SNAPSHOT_SIGN_LOGIN_BATCH_OPEN);
        Route::post('/', [SnapshotSignLoginController::class, 'loginBatch'])->name(RouteName::SNAPSHOT_SIGN_LOGIN_BATCH_LOGIN);
    });
    Route::prefix('/single')->group(static function (): void {
        Route::get('/', [SnapshotSignLoginController::class, 'openSingle'])->name(RouteName::SNAPSHOT_SIGN_LOGIN_SINGLE_OPEN);
        Route::post('/', [SnapshotSignLoginController::class, 'loginSingle'])->name(RouteName::SNAPSHOT_SIGN_LOGIN_SINGLE_LOGIN);
    });
});

Route::get('/{tenant}/two-factor-authentication', OneTimePasswordValidation::class)
    ->name(RouteName::TWO_FACTOR_AUTHENTICATION_REQUEST);
