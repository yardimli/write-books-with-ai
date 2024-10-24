<?php

	namespace App\Http\Controllers;

	use Carbon\Carbon;
	use Illuminate\Http\Request;
	use App\Models\User;
	use App\Models\NewOrder;
	use App\Models\NewOrderItem;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;
	use Illuminate\Support\Facades\Validator;
	use App\Helpers\MyHelper;
	use Illuminate\Support\Facades\Session;
	use Illuminate\Validation\Rule;
	use Illuminate\Support\Facades\Hash;
	use Illuminate\Validation\ValidationException;
	use Illuminate\Pagination\LengthAwarePaginator;

	use BinshopsBlog\Models\BinshopsCategory;
	use BinshopsBlog\Models\BinshopsCategoryTranslation;
	use BinshopsBlog\Models\BinshopsLanguage;
	use BinshopsBlog\Models\BinshopsPostTranslation;


	class StaticPagesController extends Controller
	{

		//-------------------------------------------------------------------------
		// Index
		public function index(Request $request)
		{
			$posts = MyHelper::getBlogData();

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;

			return view("user.index", compact('posts',  'genres_array', 'adult_genres_array'));

		}

		public function landing(Request $request)
		{
			return view('landing.landing');
		}

		public function about(Request $request)
		{
			return view('user.about');
		}

		public function faq(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;

			return view("user.faq", compact('posts', 'genres_array', 'adult_genres_array'));
		}


		public function myBooks(Request $request)
		{
			$booksDir = Storage::disk('public')->path('books');

			$books = [];
			if ($handle = opendir($booksDir)) {
				while (false !== ($subDir = readdir($handle))) {
					if ($subDir !== '.' && $subDir !== '..') {
						$bookJsonPath = "$booksDir/$subDir/book.json";
						if (file_exists($bookJsonPath)) {
							$bookJson = file_get_contents($bookJsonPath);
							$bookData = json_decode($bookJson, true);
							if ($bookData) {
								$random_int = rand(1, 16);
								$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
								$bookData['cover_filename'] = $bookData['cover_filename'] ?? '';

								if ($bookData['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $bookData['cover_filename']))) {
									$coverFilename = asset("storage/ai-images/" . $bookData['cover_filename']);
								}

								//search $book['owner'] in users table name column
								$user = User::where('email', ($bookData['owner']))->first();
								if ( ($user && $user->email === Auth::user()->email) || (Auth::user() && Auth::user()->isAdmin())) {
									$bookData['owner_name'] = $user->name ?? 'deleted_user';
									if (!($bookData['owner_name'] == 'deleted_user')) {
										if ($user->avatar) {
											$bookData['author_avatar'] = Storage::url($user->avatar);
										} else {
											$bookData['author_avatar'] = '/assets/images/avatar/03.jpg';
										}
									} else
									{
										$bookData['author_avatar'] = '/assets/images/avatar/02.jpg';
									}


									$bookData['id'] = $subDir;
									$bookData['cover_filename'] = $coverFilename;
									$bookData['file_time'] = filemtime($bookJsonPath);
									$bookData['owner'] = $bookData['owner'] ?? 'deleted_user';
									$books[] = $bookData;
								}
							}
						}
					}
				}
				closedir($handle);
			}

			usort($books, function ($a, $b) {
				return $b['file_time'] - $a['file_time'];
			});

			//remove books whose owner is not the current user or admin
			$books = array_filter($books, function ($book) {
				return ( (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email)) || (Auth::user() && Auth::user()->isAdmin()) || (($book['public-domain'] ?? 'yes') === 'yes'));
			});

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;


			return view("user.my-books", compact('books', 'genres_array', 'adult_genres_array'));

		}

		public function onboarding(Request $request)
		{
			return view('user.onboarding');
		}

		public function help(Request $request)
		{
			return view('help.help');
		}

		public function helpDetails(Request $request, $topic)
		{
			return view('help.help-details', ['topic' => $topic]);
		}

		public function contact_us(Request $request)
		{
			$posts = MyHelper::getBlogData();
			// Return to the existing blog list view with the posts

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;

			return view("user.contact-us", compact('posts', 'genres_array', 'adult_genres_array'));

		}

		public function privacy(Request $request)
		{
			return view('user.privacy');
		}

		public function terms(Request $request)
		{
			return view('user.terms');
		}

		public function changeLog(Request $request)
		{
			return view('user.change-log');
		}


		//------------------------------------------------------------------------------

		public function readBook(Request $request, $slug)
		{
			$bookPath = Storage::disk('public')->path("books/{$slug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsFile = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath) || !File::exists($actsFile)) {
				return response()->json(['success' => false, 'message' => __('Book not found ' . $bookJsonPath)], 404);
			}

			$book = json_decode(File::get($bookJsonPath), true);

//search $book['owner'] in users table name column
			$user = User::where('email', ($book['owner'] ?? 'admin'))->first();
			if ($user) {
				$book['owner_name'] = $user->name;
				if ($user->avatar) {
					$book['author_avatar'] = Storage::url($user->avatar);
				} else
				{
					$book['author_avatar'] = '/assets/images/avatar/03.jpg';
				}
			} else
			{
				$book['owner_name'] = 'admin';
				$book['author_name'] = $book['author_name']  . '(anonymous)';
				$book['author_avatar'] = '/assets/images/avatar/02.jpg';
			}

			$actsData = json_decode(File::get($actsFile), true);

			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);

					if (isset($chapterData['events']) && is_array($chapterData['events'])) {
						$chapterData['events'] = implode("\n", $chapterData['events']);
					}
					if (isset($chapterData['places']) && is_array($chapterData['places'])) {
						$chapterData['places'] = implode("\n", $chapterData['places']);
					}
					if (isset($chapterData['people']) && is_array($chapterData['people'])) {
						$chapterData['people'] = implode("\n", $chapterData['people']);
					}

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;

					}
				}

				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}


			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$book['cover_filename'] = $book['cover_filename'] ?? '';
			$book['file_time'] = filemtime($bookJsonPath);

			$book_slug = $slug;

			if ($book['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $book['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $book['cover_filename']);
			}

			$book['cover_filename'] = $coverFilename;
			$book['acts'] = $acts;

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;

			return view('user.read-book', compact( 'book', 'book_slug', 'genres_array', 'adult_genres_array'));
		}

		public function showcaseLibrary(Request $request)
		{
			$booksDir = Storage::disk('public')->path('books');

			$books = [];
			if ($handle = opendir($booksDir)) {
				while (false !== ($subDir = readdir($handle))) {
					if ($subDir !== '.' && $subDir !== '..') {
						$bookJsonPath = "$booksDir/$subDir/book.json";
						if (file_exists($bookJsonPath)) {
							$bookJson = file_get_contents($bookJsonPath);
							$bookData = json_decode($bookJson, true);
							if ($bookData) {
								$random_int = rand(1, 16);
								$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
								$bookData['cover_filename'] = $bookData['cover_filename'] ?? '';

								if ($bookData['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $bookData['cover_filename']))) {
									$coverFilename = asset("storage/ai-images/" . $bookData['cover_filename']);
								}

								//search $book['owner'] in users table name column
								$user = User::where('email', ($bookData['owner'] ?? 'admin'))->first();
								if ($user) {
									$bookData['owner_name'] = $user->name;
									if ($user->avatar) {
										$bookData['author_avatar'] = Storage::url($user->avatar);
									} else
									{
										$bookData['author_avatar'] = '/assets/images/avatar/03.jpg';
									}
								} else
								{
									$bookData['owner_name'] = 'admin';
									$bookData['author_name'] = $bookData['author_name']  . '(anonymous)';
									$bookData['author_avatar'] = '/assets/images/avatar/02.jpg';
								}

								if ($user && $user->isAdmin()) {
									$bookData['id'] = $subDir;
									$bookData['cover_filename'] = $coverFilename;
									$bookData['file_time'] = filemtime($bookJsonPath);
									$bookData['owner'] = $bookData['owner'] ?? 'admin';
									$books[] = $bookData;
								}
							}
						}
					}
				}
				closedir($handle);
			}

			usort($books, function ($a, $b) {
				return $b['file_time'] - $a['file_time'];
			});

			//remove books whose owner is not the current user or admin
			$books = array_filter($books, function ($book) {
				return ( (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email)) || (Auth::user() && Auth::user()->isAdmin()) || (($book['public-domain'] ?? 'yes') === 'yes'));
			});

			$perPage = 12; // Number of items per page
			$currentPage = $request->input('page', 1);
			$offset = ($currentPage - 1) * $perPage;

			$paginatedBooks = new LengthAwarePaginator(
				array_slice($books, $offset, $perPage),
				count($books),
				$perPage,
				$currentPage,
				['path' => $request->url(), 'query' => $request->query()]
			);

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;


			return view('user.showcase-library', compact( 'paginatedBooks', 'genres_array', 'adult_genres_array'));
		}

		public function booksDetail(Request $request, $slug)
		{
			$bookPath = Storage::disk('public')->path("books/{$slug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsFile = "{$bookPath}/acts.json";

			if (!File::exists($bookJsonPath) || !File::exists($actsFile)) {
				return response()->json(['success' => false, 'message' => __('default.Book not found ' . $bookJsonPath)], 404);
			}

			$book = json_decode(File::get($bookJsonPath), true);

			//search $book['owner'] in users table name column
			$user = User::where('email', ($book['owner'] ?? 'admin'))->first();
			if ($user) {
				$book['owner_name'] = $user->name;
				if ($user->avatar) {
					$book['author_avatar'] = Storage::url($user->avatar);
				} else
				{
					$book['author_avatar'] = '/assets/images/avatar/03.jpg';
				}
			} else
			{
				$book['owner_name'] = 'admin';
				$book['author_name'] = $book['author_name']  . '(anonymous)';
				$book['author_avatar'] = '/assets/images/avatar/02.jpg';
			}

			$actsData = json_decode(File::get($actsFile), true);

			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row'])) {
						continue;
					}
					$chapterData['chapterFilename'] = basename($chapterFile);

					if ($chapterData['row'] === $act['id']) {
						$actChapters[] = $chapterData;

					}
				}

				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}


			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$book['cover_filename'] = $book['cover_filename'] ?? '';

			$book_slug = $slug;

			if ($book['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $book['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $book['cover_filename']);
			}

			$book['cover_filename'] = $coverFilename;
			$book['file_time'] = filemtime($bookJsonPath);

			$book['acts'] = $acts;

			$genres_array = MyHelper::$genres_array;
			$adult_genres_array = MyHelper::$adult_genres_array;

			return view('user.book-details', compact( 'book', 'book_slug', 'genres_array', 'adult_genres_array'));
		}

	}
