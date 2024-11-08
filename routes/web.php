<?php

	use App\Http\Controllers\BookActionController;
	use App\Http\Controllers\BookBeatController;
	use App\Http\Controllers\BookCodexController;
	use App\Http\Controllers\DreamStudioController;
	use App\Http\Controllers\JobController;
	use App\Http\Controllers\LoginWithGoogleController;
	use App\Http\Controllers\LoginWithLineController;
	use App\Http\Controllers\ProductController;
	use App\Http\Controllers\StaticPagesController;
	use App\Http\Controllers\UserController;
	use App\Http\Controllers\UserSettingsController;
	use App\Http\Controllers\VerifyThankYouController;
	use App\Mail\ThankYouForYourOrder;
	use App\Mail\WelcomeMail;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\Mail;
	use Illuminate\Support\Facades\Route;


	/*
	|--------------------------------------------------------------------------
	| Web Routes
	|--------------------------------------------------------------------------
	|
	| Here is where you can register web routes for your application. These
	| routes are loaded by the RouteServiceProvider and all of them will
	| be assigned to the "web" middleware group. Make something great!
	|
	*/

	//-------------------------------------------------------------------------
	Route::get('/', [StaticPagesController::class, 'landing'])->name('landing-page');

	Route::get('login/google', [LoginWithGoogleController::class, 'redirectToGoogle']);
	Route::get('login/google/callback', [LoginWithGoogleController::class, 'handleGoogleCallback']);

	Route::get('/logout', [LoginWithGoogleController::class, 'logout']);

	Route::get('/verify-thank-you', [VerifyThankYouController::class, 'index'])->name('verify-thank-you')->middleware('verified');
	Route::get('/verify-thank-you-zh_TW', [VerifyThankYouController::class, 'index_zh_TW'])->name('verify-thank-you-zh_TW')->middleware('verified');

	Route::get('/showcase-library/genre/{genre}', [StaticPagesController::class, 'showcaseLibrary'])->name('showcase-library-genre');
	Route::get('/showcase-library/keyword/{keyword}', [StaticPagesController::class, 'showcaseLibrary'])->name('showcase-library-keyword');
	Route::get('/showcase-library', [StaticPagesController::class, 'showcaseLibrary'])->name('showcase-library');
	Route::get('/book-details/{slug}', [StaticPagesController::class, 'booksDetail'])->name('book-details');

	Route::get('/privacy', [StaticPagesController::class, 'privacy'])->name('privacy-page');
	Route::get('/terms', [StaticPagesController::class, 'terms'])->name('terms-page');
	Route::get('/help', [StaticPagesController::class, 'help'])->name('help-page');
	Route::get('/help/{topic}', [StaticPagesController::class, 'helpDetails'])->name('help-details');
	Route::get('/about', [StaticPagesController::class, 'about'])->name('about-page');
	Route::get('/contact', [StaticPagesController::class, 'contact'])->name('contact-page');
	Route::get('/onboarding', [StaticPagesController::class, 'onboarding'])->name('onboarding-page');
	Route::get('/change-log', [StaticPagesController::class, 'changeLog'])->name('change-log-page');
	Route::get('/buy-packages', [UserSettingsController::class, 'buyPackages'])->name('buy-packages');

	Route::get('/help', [StaticPagesController::class, 'help'])->name('help-page');

	//-------------------------------------------------------------------------

	Route::get('/buy-packages', [UserSettingsController::class, 'buyPackages'])->name('buy-packages');

	Route::get('/buy-credits-test/{id}', [PayPalController::class, 'beginTransaction'])->name('beginTransaction');
	Route::get('/buy-credits/{id}', [PayPalController::class, 'processTransaction'])->name('processTransaction');
	Route::get('/success-transaction', [PayPalController::class, 'successTransaction'])->name('successTransaction');
	Route::get('/cancel-transaction', [PayPalController::class, 'cancelTransaction'])->name('cancelTransaction');

	Route::get('/writer-profile/{username}', [StaticPagesController::class, 'userProfile'])->name('user-profile');

	Route::get('/read-book/{slug}', [StaticPagesController::class, 'readBook'])->name('read-book');

	//-------------------------------------------------------------------------
	Route::middleware(['auth'])->group(function () {

		Route::get('/prompts/{filename}.txt', function ($filename) {
			$filePath = resource_path("prompts/{$filename}.txt");

			if (File::exists($filePath)) {
				return response()->file($filePath);
			} else {
				abort(404, 'File not found.');
			}
		});

		Route::get('/check-llms-json', [BookActionController::class, 'checkLLMsJson']);

		Route::get('/start-writing', [BookActionController::class, 'startWriting'])->name('start-writing');
		Route::get('/edit-book/{slug}', [BookActionController::class, 'editBook'])->name('edit-book');

		Route::post('/write-book-character-profiles', [BookActionController::class, 'writeBookCharacterProfiles'])->name('write-book-character-profiles');
		Route::post('/write-book', [BookActionController::class, 'writeBook'])->name('write-book');
		Route::post('/book/{bookSlug}/chapter', [BookActionController::class, 'saveChapter'])->name('book-save-chapter');
		Route::post('/book/{bookSlug}/cover', [BookActionController::class, 'saveCover'])->name('book-save-cover');
		Route::post('/book/{bookSlug}/details', [BookActionController::class, 'saveBookDetails'])->name('book-save-book-details');


		Route::post('/book/write-beats/{bookSlug}/{chapterFilename}', [BookBeatController::class, 'writeBeats'])->name('book-write-beats');
		Route::post('/book/write-beat-description/{bookSlug}/{chapterFilename}', [BookBeatController::class, 'writeBeatDescription'])->name('book-write-beat-description');

		Route::post('/book/write-beat-text/{bookSlug}/{chapterFilename}', [BookBeatController::class, 'writeBeatText'])->name('book-write-beat-text');

		Route::post('/book/write-beat-summary/{bookSlug}/{chapterFilename}', [BookBeatController::class, 'writeBeatSummary'])->name('book-write-beat-summary');


		Route::get('/book/{bookSlug}/codex', [BookCodexController::class, 'showCodex'])->name('book-codex');
		Route::post('/book/{bookSlug}/codex', [BookCodexController::class, 'saveCodex'])->name('book-save-codex');
		Route::post('/book/{bookSlug}/update-codex-from-beats', [BookCodexController::class, 'updateCodexFromBeats'])->name('book-update-codex-from-beats');

		Route::post('/rewrite-chapter', [BookActionController::class, 'rewriteChapter'])->name('rewrite-chapter');
		Route::post('/accept-rewrite', [BookActionController::class, 'acceptRewrite'])->name('accept-rewrite');
		Route::delete('/book/{bookSlug}', [BookActionController::class, 'deleteBook'])->name('book-delete');
		Route::post('/send-llm-prompt/{bookSlug}', [BookActionController::class, 'sendLlmPrompt'])->name('send-llm-prompt');
		Route::post('/make-cover-image/{bookSlug}', [BookActionController::class, 'makeCoverImage'])->name('make-cover-image');


		Route::get('/my-books', [StaticPagesController::class, 'myBooks'])->name('my-books');
		Route::get('/settings', [UserSettingsController::class, 'editSettings'])->name('my-settings');
		Route::post('/settings', [UserSettingsController::class, 'updateSettings'])->name('settings-update');

		Route::post('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('settings-password-update');
		Route::post('/settings/api-keys', [UserSettingsController::class, 'updateApiKeys'])->name('settings-update-api-keys');

		Route::get('/users', [UserController::class, 'index'])->name('users-index');
		Route::post('/login-as', [UserController::class, 'loginAs'])->name('users-login-as');

		Route::post('/settings/password', [UserSettingsController::class, 'updatePassword'])->name('settings-password-update');

	});

//-------------------------------------------------------------------------

	Auth::routes();
	Auth::routes(['verify' => true]);
