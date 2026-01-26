<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\QaSuggestController;
use App\Http\Controllers\PublicPageController;
use App\Http\Controllers\AskQuestionController;
use App\Http\Controllers\PublicAnswerController;

use App\Http\Controllers\PublicQuestionController;
use App\Http\Controllers\Admin\AdminPageController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\QuestionSuggestController;
use App\Http\Controllers\Admin\AdminAnswerController;
use App\Http\Controllers\Admin\AdminSettingController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminQuestionController;

Route::get('/', [PublicPageController::class, 'home'])->name('home');


/**
 * ✅ All Questions (DB driven)
 */
Route::get('/questions', [PublicQuestionController::class, 'index'])
    ->name('questions.index');

/**
 * ✅ Public Question Detail
 */
Route::get('/questions/{slug}', [PublicQuestionController::class, 'show'])
    ->name('questions.show');

/**
 * ✅ Category-wise list (DB driven)
 */
Route::get('/categories/{slug}', [PublicQuestionController::class, 'category'])
    ->name('categories.show');

/**
 * Answers List (Public) - (later DB-driven করতে পারবেন)
 */
Route::get('/answers', [PublicAnswerController::class, 'index'])
    ->name('answers.index');

Route::get('/about', [PublicPageController::class, 'about'])->name('about');

/**
 * ✅ Ask Routes
 */
Route::get('/ask', [AskQuestionController::class, 'create'])
    ->name('ask');

Route::post('/ask', [AskQuestionController::class, 'store'])
    ->middleware('throttle:ask-question')
    ->name('ask.store');

Route::get('/ask/thanks/{id}', function ($id) {
    return view('pages.ask.thanks', ['id' => $id]);
})->name('ask.thanks');

/**
 * ✅ Realtime Duplicate/Suggest
 */
Route::get('/qa/suggest', QaSuggestController::class)
    ->middleware('throttle:120,1') // আগে ছিল 30,1
    ->name('qa.suggest');

Route::get('/suggest/questions', QuestionSuggestController::class)
    ->middleware('throttle:30,1')
    ->name('suggest.questions');

/**
 * ✅ Auth + Admin
 */
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', fn() => view('dashboard'))->name('dashboard');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(['permission:qa.view_admin'])
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])
                ->name('dashboard');

            Route::get('/questions', [AdminQuestionController::class, 'index'])
                ->name('questions.index');

            Route::get('/questions/{question}', [AdminQuestionController::class, 'show'])
                ->name('questions.show');

            Route::post('/questions/{question}/approve', [AdminQuestionController::class, 'approve'])
                ->middleware('permission:qa.moderate_questions')
                ->name('questions.approve');

            Route::post('/questions/{question}/reject', [AdminQuestionController::class, 'reject'])
                ->middleware('permission:qa.moderate_questions')
                ->name('questions.reject');

            Route::post('/questions/{question}/answer/draft', [AdminAnswerController::class, 'saveDraft'])
                ->middleware('permission:qa.write_answers')
                ->name('answers.draft');

            Route::post('/questions/{question}/answer/publish', [AdminAnswerController::class, 'publish'])
                ->middleware('permission:qa.publish_answers')
                ->name('answers.publish');

            /**
             * ✅ Category CRUD (Admin)
             */
            Route::get('/categories', [AdminCategoryController::class, 'index'])
                ->middleware('permission:qa.manage_categories')
                ->name('categories.index');

            Route::get('/categories/create', [AdminCategoryController::class, 'create'])
                ->middleware('permission:qa.manage_categories')
                ->name('categories.create');

            Route::post('/categories', [AdminCategoryController::class, 'store'])
                ->middleware('permission:qa.manage_categories')
                ->name('categories.store');

            Route::get('/categories/{category}/edit', [AdminCategoryController::class, 'edit'])
                ->middleware('permission:qa.manage_categories')
                ->name('categories.edit');

            Route::put('/categories/{category}', [AdminCategoryController::class, 'update'])
                ->middleware('permission:qa.manage_categories')
                ->name('categories.update');

            Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])
                ->middleware('permission:qa.manage_categories')
                ->name('categories.destroy');

            // ✅ Static Page CRUD (Admin)
            Route::get('/pages', [AdminPageController::class, 'index'])
                ->middleware('permission:qa.manage_pages')
                ->name('pages.index');

            Route::get('/pages/create', [AdminPageController::class, 'create'])
                ->middleware('permission:qa.manage_pages')
                ->name('pages.create');

            Route::post('/pages', [AdminPageController::class, 'store'])
                ->middleware('permission:qa.manage_pages')
                ->name('pages.store');

            Route::get('/pages/{page}/edit', [AdminPageController::class, 'edit'])
                ->middleware('permission:qa.manage_pages')
                ->name('pages.edit');

            Route::put('/pages/{page}', [AdminPageController::class, 'update'])
                ->middleware('permission:qa.manage_pages')
                ->name('pages.update');

            Route::delete('/pages/{page}', [AdminPageController::class, 'destroy'])
                ->middleware('permission:qa.manage_pages')
                ->name('pages.destroy');

            Route::get('/settings', [AdminSettingController::class, 'index'])
                ->middleware('permission:qa.manage_settings')
                ->name('settings.index');

            Route::put('/settings', [AdminSettingController::class, 'update'])
                ->middleware('permission:qa.manage_settings')
                ->name('settings.update');
        });
});
