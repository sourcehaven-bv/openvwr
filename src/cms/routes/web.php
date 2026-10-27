<?php

declare(strict_types=1);

use App\Config\Config;
use App\Enums\RouteName;
use App\Filament\Pages\OneTimePasswordValidation;
use App\Http\Controllers\Authentication\PasswordlessLoginController;
use App\Http\Controllers\Authentication\SnapshotSignLoginController;
use App\Http\Controllers\PratiqueWebhookController;
use App\Http\Controllers\PrivateMediaController;
use App\Http\Controllers\RedirectToTenantController;
use App\Http\Controllers\TransferExportDownloadController;
use App\Http\Middleware\ResolveAuthGate;
use App\Services\Authentication\AuthenticationStrategyFactory;
use Illuminate\Support\Facades\Route;

// The landing route resolves which tenant to send someone to, so it needs the
// same gate the panel uses: it lives in the `web` group, which does NOT include
// the panel's auth middleware, and under the pratique driver nothing else would
// verify the assertion before RedirectToTenantController reads the identity.
//
// Resolved per request rather than at registration: routes are registered once
// and cached, so asking the container here would freeze whichever strategy
// happened to be bound at boot.
Route::get('/', RedirectToTenantController::class)
    ->middleware(ResolveAuthGate::class)
    ->name(RouteName::HOME);

Route::prefix('/login/consume')->middleware('signed')->group(static function (): void {
    Route::get('/', [PasswordlessLoginController::class, 'consume'])->name(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONSUME);
    Route::post('/', [PasswordlessLoginController::class, 'confirm'])->name(RouteName::PASSWORDLESS_LOGIN_VALIDATE_CONFIRM);
});

// Private media is authorised per item by the controller, but it still needs an
// identity to authorise against — and like the landing route it sits in the
// `web` group, which establishes none. Without the gate the controller's own
// `user()` call throws under the pratique driver and the response is a 500
// instead of a refusal.
Route::get('/media/{media}', PrivateMediaController::class)
    ->middleware(ResolveAuthGate::class)
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

// Lifecycle events from the Pratique proxy. Registered only under that driver,
// and deliberately outside every auth middleware: the proxy holds no session
// when it calls us, so the JWT signature in the body is the authentication.
// See PratiqueWebhookController.
if (
    Config::string('auth.driver', AuthenticationStrategyFactory::DRIVER_BUILTIN)
    === AuthenticationStrategyFactory::DRIVER_PRATIQUE
) {
    Route::post('/pratique/webhook', PratiqueWebhookController::class)
        ->name(RouteName::PRATIQUE_WEBHOOK);
}
