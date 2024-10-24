<?php

	namespace App\Console\Commands;

	use App\Helpers\MyHelper;
	use Illuminate\Console\Command;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Facades\Storage;

	class DeleteUnusedImages extends Command
	{
		protected $signature = 'books:delete-unused-images';
		protected $description = 'Delete images that are not used in any book';

		public function handle()
		{
			function scanForBookPhp($dir)
			{
				$results = [];
				$files = scandir($dir);

				foreach ($files as $file) {
					if ($file == '.' || $file == '..') continue;

					$path = $dir . DIRECTORY_SEPARATOR . $file;

					if (is_dir($path)) {
						$results = array_merge($results, scanForBookPhp($path));
					} else if ($file == 'book.json') {
						$results[] = $path;
					}
				}

				return $results;
			}

			//load all images in storage/app/public/ai-images to array
			$images = Storage::disk('public')->files('ai-images');
			$book_images = [];

			$booksDir = Storage::disk('public')->path('books');
			$this->info("Scanning for book.php files in $booksDir...");
			$bookFiles = scanForBookPhp($booksDir);

			foreach ($bookFiles as $bookFile) {
				$content = file_get_contents($bookFile);
				$data = json_decode($content, true);
				if (isset($data['cover_filename'])) {
					$book_images[] = 'ai-images/' . $data['cover_filename'];
				}
			}

			//compare the two arrays and delete the images that are not used
			$unused_images = array_diff($images, $book_images);
			foreach ($unused_images as $image) {
				Storage::disk('public')->delete($image);
				$this->info("Deleted $image");
			}

		}
	}

