<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\AuthenticatedSessionController;
use App\Http\Controllers\Admin\CommunicationController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceExportController;
use App\Http\Controllers\Admin\JudgingController;
use App\Http\Controllers\Admin\PasswordResetController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\PortalController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\RegistrationExportController;
use App\Http\Controllers\Admin\RegistrationStatusController;
use App\Http\Controllers\Admin\SeasonController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamController;
use App\Http\Controllers\StatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public site
|--------------------------------------------------------------------------
*/

Route::view('/', 'pages.home')->name('home');
Route::view('/about', 'pages.about')->name('about');
Route::view('/seasons', 'pages.seasons')->name('seasons');
Route::view('/groups', 'pages.groups')->name('groups');
Route::view('/programme', 'pages.programme')->name('programme');
Route::view('/impact', 'pages.impact')->name('impact');
Route::view('/faq', 'pages.faq')->name('faq');
Route::view('/register', 'pages.register')->name('register');
Route::view('/terms', 'pages.terms')->name('terms');
Route::get('/status', [StatusController::class, 'create'])->name('status');

/*
|--------------------------------------------------------------------------
| Management system — authentication
|--------------------------------------------------------------------------
*/

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::middleware('guest')->group(function (): void {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:login')->name('login.store');
        Route::get('login/verify', [AuthenticatedSessionController::class, 'verifyForm'])->name('login.verify');
        Route::post('login/verify', [AuthenticatedSessionController::class, 'verify'])->middleware('throttle:verify')->name('login.verify.store');

        Route::get('password/reset', [PasswordResetController::class, 'requestForm'])->name('password.request');
        Route::post('password/email', [PasswordResetController::class, 'send'])->middleware('throttle:6,1')->name('password.email');
        Route::get('password/reset/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
        Route::post('password/reset', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
    });

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->middleware('auth')
        ->name('logout');
});

/*
|--------------------------------------------------------------------------
| Management system — application
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'admin.access'])
    ->group(function (): void {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('seasons', [SeasonController::class, 'index'])->name('seasons.index');
        Route::get('seasons/create', [SeasonController::class, 'create'])->name('seasons.create');
        Route::post('seasons', [SeasonController::class, 'store'])->name('seasons.store');
        Route::get('seasons/{season}', [SeasonController::class, 'show'])->name('seasons.show');
        Route::get('seasons/{season}/edit', [SeasonController::class, 'edit'])->name('seasons.edit');
        Route::put('seasons/{season}', [SeasonController::class, 'update'])->name('seasons.update');
        Route::post('seasons/{season}/make-current', [SeasonController::class, 'makeCurrent'])->name('seasons.make-current');

        Route::get('registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::get('registrations/export', RegistrationExportController::class)->name('registrations.export');
        Route::get('registrations/create', [RegistrationController::class, 'create'])->name('registrations.create');
        Route::post('registrations', [RegistrationController::class, 'store'])->name('registrations.store');
        Route::get('registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
        Route::get('registrations/{registration}/edit', [RegistrationController::class, 'edit'])->name('registrations.edit');
        Route::put('registrations/{registration}', [RegistrationController::class, 'update'])->name('registrations.update');
        Route::delete('registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
        Route::post('registrations/{registration}/status', RegistrationStatusController::class)->name('registrations.status');
        Route::post('registrations/{registration}/assign', [RegistrationController::class, 'assign'])->name('registrations.assign');
        Route::post('registrations/{registration}/tag', [RegistrationController::class, 'tag'])->name('registrations.tag');

        Route::get('audit', [AuditLogController::class, 'index'])->name('audit.index');

        Route::get('communications', [CommunicationController::class, 'index'])->name('communications.index');
        Route::get('communications/{thread}', [CommunicationController::class, 'show'])->name('communications.show');
        Route::post('communications/{thread}/reply', [CommunicationController::class, 'reply'])->name('communications.reply');
        Route::post('communications/{thread}', [CommunicationController::class, 'update'])->name('communications.update');

        Route::get('judging', [JudgingController::class, 'index'])->name('judging.index');
        Route::post('judging/nudge', [JudgingController::class, 'nudge'])->name('judging.nudge');
        Route::post('judging/rounds/{round}/publish', [JudgingController::class, 'publish'])->name('judging.publish');

        Route::get('payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/export', InvoiceExportController::class)->name('payments.export');
        Route::post('payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('payments/reconcile', [PaymentController::class, 'reconcile'])->name('payments.reconcile');

        Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::post('analytics/impact', [AnalyticsController::class, 'impactReport'])->name('analytics.impact');

        Route::get('content', [ContentController::class, 'index'])->name('content.index');
        Route::post('content', [ContentController::class, 'store'])->name('content.store');
        Route::post('content/pages/{page}/publish', [ContentController::class, 'publish'])->name('content.publish');
        Route::put('content/slots/{slot}', [ContentController::class, 'updateSlot'])->name('content.slot');

        Route::get('portal', [PortalController::class, 'index'])->name('portal.index');
        Route::post('portal/{registration}/magic-link', [PortalController::class, 'sendMagicLink'])->name('portal.magic-link');

        Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
        Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
        Route::put('teams/{teamMember}', [TeamController::class, 'update'])->name('teams.update');
        Route::delete('teams/{teamMember}', [TeamController::class, 'destroy'])->name('teams.destroy');

        Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('settings/test', [SettingController::class, 'testConnections'])->name('settings.test');
        Route::post('settings/integrations/{integration}', [SettingController::class, 'toggleIntegration'])->name('settings.integration');
    });
