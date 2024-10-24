<?php

	namespace App\Console\Commands;

	use App\Helpers\MyHelper;
	use Illuminate\Console\Command;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Facades\Storage;

	class AddMissingBookFields extends Command
	{
		protected $signature = 'books:add-missing-fields';
		protected $description = 'Add missing fields to the books table';

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

			$booksDir = Storage::disk('public')->path('books');
			$this->info("Scanning for book.php files in $booksDir...");
			$bookFiles = scanForBookPhp($booksDir);

			$genres_array = array(
				"Action",
				"Biography",
				"Body, Mind & Spirit",
				"Business & Economics",
				"Education",
				"Family & Relationships",
				"Health & Fitness",
				"Romance",
				"Young Adult",
				"Horror",
				"Fantasy",
				"Realistic",
				"LGBTQ+",
				"Science Fiction",
				"Dark Humor",
				"Mystery",
				"Thriller",
				"Historical",
				"Paranormal",
				"Adventure",
				"Crime",
				"Children's Literature",
				"Steampunk",
				"Chick Lit",
				"Post-Apocalyptic",
				"Humor",
				"Drama",
				"Western",
				"Space Opera"
			);


			foreach ($bookFiles as $bookFile) {
				$content = file_get_contents($bookFile);
				$data = json_decode($content, true);


				// Add missing fields
				if (!isset($data['genre'])) {
					$prompt = "Choose a genre for the book with titile " . $data['title'] . " and back cover text: " . $data['back_cover_text'] . "\n" . "Choose from the following genres: " . implode(", ", $genres_array) . " only output the genre name\n";
					$promptResult = MyHelper::llm_no_tool_call('open-ai-gpt-4o-mini', '', '', $prompt, false, 'english');
					$this->info("Genre: " . $promptResult);

					$data['genre'] = "Fiction"; // You might want to set this dynamically
				}

				if (!isset($data['tags'])) {
					$prompt = "Respond with 10 comma separated keywords for a book with the title: " . $data['title'] . " and back cover text: " . $data['back_cover_text'] . "\n";
					$promptResult = MyHelper::llm_no_tool_call('open-ai-gpt-4o-mini', '', '', $prompt, false, 'english');
					$this->info("Keywords: " . $promptResult);

					$data['tags'] = explode(',', $promptResult);
				}

				if (!isset($data['author_name'])) {
					$prompt = "Respond with a random author name for a book with the title: " . $data['title'] . " and back cover text: " . $data['back_cover_text'] . "\nOnly output the author name\n";
					$promptResult = MyHelper::llm_no_tool_call('open-ai-gpt-4o-mini', '', '', $prompt, false, 'english');
					$this->info("Author: " . $promptResult);

					$data['author_name'] = $promptResult;
				}

				if (!isset($data['publisher_name'])) {
					$prompt = "Respond with a random publishing house name for a book with the title: " . $data['title'] . " and back cover text: " . $data['back_cover_text'] . "\nOnly output the publishing house name\n";
					$promptResult = MyHelper::llm_no_tool_call('open-ai-gpt-4o-mini', '', '', $prompt, false, 'english');
					$this->info("Publisher: " . $promptResult);

					$data['publisher_name'] = $promptResult;
				}

				if (!isset($data['public-domain'])) {
					$data['public-domain'] = 'yes';
				}

				// Save the updated content back to the file
				file_put_contents($bookFile, json_encode($data, JSON_PRETTY_PRINT));

				$this->info("Updated $bookFile");
			}

			$this->info("Finished updating book.php files.");
		}
	}
