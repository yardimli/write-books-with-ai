<?php

	namespace App\Console\Commands;

	use Illuminate\Console\Command;
	use Illuminate\Support\Facades\DB;
	use Illuminate\Support\Facades\File;
	use Illuminate\Support\Facades\Storage;

	class ProcessTextFiles extends Command
	{
		protected $signature = 'process:text-files {folder}';
		protected $description = 'Process text files and insert paragraphs into database';

		public function handle()
		{
			$folder = $this->argument('folder');
			$path = resource_path($folder);

			if (!File::isDirectory($path)) {
				$this->error("The specified folder does not exist in resources.");
				return;
			}

			$files = File::files($path);

			foreach ($files as $file) {
				if ($file->getExtension() === 'txt') {
					$this->processFile($file);
				}
			}

			$this->info('Processing completed.');
		}

		private function processFile($file)
		{
			$content = file_get_contents($file);

			// Detect the encoding of the file
			$encoding = mb_detect_encoding($content, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);

			// Convert to UTF-8 if it's not already
			if ($encoding !== 'UTF-8') {
				$content = mb_convert_encoding($content, 'UTF-8', $encoding);
			}

			// Remove any invalid UTF-8 sequences
			$content = iconv('UTF-8', 'UTF-8//IGNORE', $content);

			// Split content into sentences
			$sentences = preg_split('/(?<=[.!?])\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
			$sentences = array_map('trim', $sentences);

			if (empty($sentences)) {
				$this->error("No sentences found in the file: " . $file);
				return;
			}

			$this->info("Sentences found in the file: " . basename($file) . " - " . count($sentences));

			$content = str_replace(["\r\n", "\r"], "\n", $content);
			$paragraphs = array_filter(array_map('trim', explode("\n", $content)));

			if (empty($paragraphs)) {
				$this->error("No paragraphs found in the file: " . $file);
				return;
			}

			$this->info("Paragraphs found in the file: " . basename($file) . " - " . count($paragraphs));

			$wordCount = 0;
			$combinedParagraph = '';
			$combinedSentences = '';

			$paragraphCount = 0;
			$sentenceCount = 0;

			$useParagraphs = false;

			if ($useParagraphs) {

				foreach ($paragraphs as $paragraph) {
					$paragraphWordCount = str_word_count($paragraph);
					$wordCount += $paragraphWordCount;
					$combinedParagraph .= $paragraph . "\n\n";

					if ($wordCount >= 250) {
						$paragraphCount++;
						$this->insertParagraph($combinedParagraph, $file, $paragraphCount, $wordCount);
						$wordCount = 0;
						$combinedParagraph = '';
					}
				}

				// Insert any remaining content
				if (!empty($combinedParagraph)) {
					$paragraphCount++;
					$this->insertParagraph($combinedParagraph, $file, $paragraphCount, $wordCount);
				}
			} else {
				foreach ($sentences as $sentence) {
					$sentenceWordCount = str_word_count($sentence);
					$wordCount += $sentenceWordCount;
					$combinedSentences .= $sentence . " ";

					if ($wordCount >= 250) {
						$sentenceCount++;
						$this->insertSentences($combinedSentences, $file, $sentenceCount, $wordCount);
						$wordCount = 0;
						$combinedSentences = '';
					}
				}

				// Insert any remaining content
				if (!empty($combinedSentences)) {
					$sentenceCount++;
					$this->insertSentences($combinedSentences, $file, $sentenceCount, $wordCount);
				}
			}
		}


		private function insertParagraph($paragraph, $file, $paragraph_order, $wordCount)
		{
			DB::table('paragraphs_table')->insert([
				'paragraphs' => trim($paragraph),
				'filename' => basename($file),
				'paragraph_order' => $paragraph_order,
				'word_count' => $wordCount,
				'created_at' => now(),
				'updated_at' => now(),
			]);
		}

		private function insertSentences($sentences, $file, $sentence_order, $wordCount)
		{
			DB::table('sentences_table')->insert([
				'sentences' => trim($sentences),
				'filename' => basename($file),
				'sentence_order' => $sentence_order,
				'word_count' => $wordCount,
				'created_at' => now(),
				'updated_at' => now(),
			]);
		}
	}
