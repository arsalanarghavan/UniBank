<?php

use App\Http\Controllers\Api\V1\AdminStatsController;
use App\Http\Controllers\Api\V1\AdminUserController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BotController;
use App\Http\Controllers\Api\V1\BroadcastController;
use App\Http\Controllers\Api\V1\DegreeLevelController;
use App\Http\Controllers\Api\V1\ExperienceController;
use App\Http\Controllers\Api\V1\FacultyController;
use App\Http\Controllers\Api\V1\ModerationController;
use App\Http\Controllers\Api\V1\ProfessorController;
use App\Http\Controllers\Api\V1\RankingController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\TaxonomyController;
use App\Http\Controllers\Api\V1\UniversityCategoryController;
use App\Http\Controllers\Api\V1\UniversityController;
use App\Modules\Telegram\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:10,1');

    Route::get('/rankings', [RankingController::class, 'index']);
    Route::get('/search', SearchController::class);
    Route::get('/university-categories', [UniversityCategoryController::class, 'index']);
    Route::get('/universities', [UniversityController::class, 'index']);
    Route::get('/universities/{university}', [UniversityController::class, 'show']);
    Route::get('/faculties', [FacultyController::class, 'index']);
    Route::get('/faculties/{faculty}', [FacultyController::class, 'show']);
    Route::get('/degree-levels', [DegreeLevelController::class, 'index']);
    Route::get('/fields', [TaxonomyController::class, 'fields']);
    Route::get('/professors', [ProfessorController::class, 'index']);
    Route::get('/professors/{professor}', [ProfessorController::class, 'show']);
    Route::get('/rules', [SettingsController::class, 'rules']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        Route::apiResource('experiences', ExperienceController::class);
        Route::post('/experiences/{experience}/attachments', [ExperienceController::class, 'uploadAttachment']);
        Route::delete('/experiences/{experience}/attachments/{attachment}', [ExperienceController::class, 'destroyAttachment']);

        Route::middleware('role:admin|owner')->prefix('admin')->group(function () {
            Route::get('/stats', AdminStatsController::class);
            Route::get('/moderation/pending', [ModerationController::class, 'pending']);
            Route::post('/moderation/{experience}/approve', [ModerationController::class, 'approve']);
            Route::post('/moderation/{experience}/reject', [ModerationController::class, 'reject']);

            Route::apiResource('university-categories', UniversityCategoryController::class)->except(['index', 'show']);
            Route::apiResource('universities', UniversityController::class)->except(['index', 'show']);
            Route::apiResource('faculties', FacultyController::class)->except(['index', 'show']);
            Route::apiResource('degree-levels', DegreeLevelController::class)->except(['index', 'show']);

            Route::post('/fields', [TaxonomyController::class, 'storeField']);
            Route::put('/fields/{field}', [TaxonomyController::class, 'updateField']);
            Route::delete('/fields/{field}', [TaxonomyController::class, 'destroyField']);
            Route::post('/majors', [TaxonomyController::class, 'storeMajor']);
            Route::put('/majors/{major}', [TaxonomyController::class, 'updateMajor']);
            Route::delete('/majors/{major}', [TaxonomyController::class, 'destroyMajor']);
            Route::post('/courses', [TaxonomyController::class, 'storeCourse']);
            Route::put('/courses/{course}', [TaxonomyController::class, 'updateCourse']);
            Route::delete('/courses/{course}', [TaxonomyController::class, 'destroyCourse']);

            Route::post('/professors', [ProfessorController::class, 'store']);
            Route::put('/professors/{professor}', [ProfessorController::class, 'update']);
            Route::delete('/professors/{professor}', [ProfessorController::class, 'destroy']);
            Route::post('/professors/{professor}/links', [ProfessorController::class, 'storeLink']);
            Route::put('/professors/{professor}/links/{link}', [ProfessorController::class, 'updateLink']);
            Route::delete('/professors/{professor}/links/{link}', [ProfessorController::class, 'destroyLink']);
            Route::post('/professors/{professor}/assignments', [ProfessorController::class, 'storeAssignment']);
            Route::delete('/professors/{professor}/assignments/{assignment}', [ProfessorController::class, 'destroyAssignment']);

            Route::get('/bots', [BotController::class, 'index']);
            Route::post('/bots', [BotController::class, 'store']);
            Route::get('/bots/{bot}', [BotController::class, 'show']);
            Route::put('/bots/{bot}', [BotController::class, 'update']);
            Route::delete('/bots/{bot}', [BotController::class, 'destroy']);
            Route::put('/bots/{bot}/layout', [BotController::class, 'updateLayout']);
            Route::put('/bots/{bot}/settings', [BotController::class, 'syncSettings']);
            Route::post('/bots/{bot}/texts', [BotController::class, 'upsertText']);
            Route::post('/bots/{bot}/channels', [BotController::class, 'storeChannel']);
            Route::delete('/bots/{bot}/channels/{channel}', [BotController::class, 'destroyChannel']);
            Route::post('/bots/{bot}/required-channels', [BotController::class, 'storeRequiredChannel']);
            Route::delete('/bots/{bot}/required-channels/{channel}', [BotController::class, 'destroyRequiredChannel']);
            Route::post('/bots/{bot}/regenerate-secret', [BotController::class, 'regenerateSecret']);

            Route::get('/settings', [SettingsController::class, 'index']);
            Route::put('/settings', [SettingsController::class, 'updateSetting']);
            Route::post('/channels', [SettingsController::class, 'storeChannel']);
            Route::delete('/channels/{channel}', [SettingsController::class, 'destroyChannel']);
            Route::put('/bot-texts/{botText}', [SettingsController::class, 'updateBotText']);

            Route::get('/users', [AdminUserController::class, 'index']);
            Route::post('/users/{user}/role', [AdminUserController::class, 'assignRole']);
            Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);

            Route::post('/broadcast', [BroadcastController::class, 'broadcast']);
            Route::post('/direct-message', [BroadcastController::class, 'directMessage']);
        });
    });
});

Route::post('/bots/{bot}/webhook', [WebhookController::class, 'handleBot']);
Route::post('/telegram/webhook', [WebhookController::class, 'handleLegacy']);
