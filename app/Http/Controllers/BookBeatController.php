<?php

	namespace App\Http\Controllers;

	use App\Models\SentencesTable;
	use GuzzleHttp\Client;
	use GuzzleHttp\Exception\ClientException;
	use Illuminate\Http\Request;
	use Illuminate\Support\Facades\Auth;
	use Illuminate\Support\Facades\File;
	use App\Helpers\MyHelper;
	use Illuminate\Support\Facades\Log;
	use Illuminate\Support\Facades\Storage;
	use Illuminate\Support\Str;

	class BookBeatController extends Controller
	{
		public function bookBeats(Request $request, $bookSlug, $selectedChapter = 'all-chapters')
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return redirect()->route('user.books')->with('error', $verified['message']);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookData = json_decode(File::get("{$bookPath}/book.json"), true);
			$actsData = json_decode(File::get("{$bookPath}/acts.json"), true);

			// Load all chapters and their beats
			$acts = [];
			foreach ($actsData as $act) {
				$actChapters = [];
				$chapterFiles = File::glob("{$bookPath}/*.json");
				foreach ($chapterFiles as $chapterFile) {
					$chapterData = json_decode(File::get($chapterFile), true);
					if (!isset($chapterData['row']) || $chapterData['row'] !== $act['id']) {
						continue;
					}
					if (!isset($chapterData['beats'])) {
						$chapterData['beats'] = [];
						//create 3 empty beats
						for ($i = 0; $i < 3; $i++) {
							$chapterData['beats'][] = [
								'description' => '',
								'beat_text' => '',
								'beat_summary' => '',
							];
						}
					}
					$chapterData['chapterFilename'] = basename($chapterFile);
					$actChapters[] = $chapterData;
				}
				usort($actChapters, fn($a, $b) => $a['order'] - $b['order']);
				$acts[] = [
					'id' => $act['id'],
					'title' => $act['title'],
					'chapters' => $actChapters
				];
			}

			$bookData['acts'] = $acts;

			$random_int = rand(1, 16);
			$coverFilename = '/images/placeholder-cover-' . $random_int . '.jpg';
			$bookData['cover_filename'] = $bookData['cover_filename'] ?? '';

			if ($bookData['cover_filename'] && file_exists(Storage::disk('public')->path("ai-images/" . $bookData['cover_filename']))) {
				$coverFilename = asset("storage/ai-images/" . $bookData['cover_filename']);
			}

			$bookData['cover_filename'] = $coverFilename;

			$selectedChapterIndex = 0;

			if ($selectedChapter !== 'all-chapters') {

				foreach ($bookData['acts'] as $act) {
					foreach ($act['chapters'] as $index => $chapter) {
						$selectedChapterIndex++;
						if ($chapter['chapterFilename'] === $selectedChapter . '.json') {
							break 2;
						}
					}
				}

				// Filter to only include the specified chapter
				foreach ($bookData['acts'] as &$act) {
					$act['chapters'] = array_filter($act['chapters'], function ($chapter) use ($selectedChapter) {
						return $chapter['chapterFilename'] === $selectedChapter . '.json';
					});
				}
				// Remove acts with no chapters
				$bookData['acts'] = array_filter($bookData['acts'], function ($act) {
					return !empty($act['chapters']);
				});
			}

			foreach ($bookData['acts'] as &$act) {
				foreach ($act['chapters'] as &$chapter) {

					if (isset($chapter['events']) && is_array($chapter['events'])) {
						$chapter['events'] = implode("\n", $chapter['events']);
					}
					if (isset($chapter['places']) && is_array($chapter['places'])) {
						$chapter['places'] = implode("\n", $chapter['places']);
					}
					if (isset($chapter['people']) && is_array($chapter['people'])) {
						$chapter['people'] = implode("\n", $chapter['people']);
					}

					if (array_key_exists('beats', $chapter)) {
						foreach ($chapter['beats'] as &$beat) {
							foreach ($beat as $key => &$content) {
								if (is_string($content)) {
									$content = str_replace("<BR><BR>", "\n", $content);
									$content = str_replace("<BR>", "\n", $content);
								}
							}
						}
					}
				}
			}

			if ($selectedChapter === 'all-chapters') {
				$selectedChapter = '';
			}

			return view('user.all-beats', [
				'book' => $bookData,
				'book_slug' => $bookSlug,
				'selected_chapter' => $selectedChapter ?? '',
				'selected_chapter_index' => $selectedChapterIndex,
				'writingStyles' => MyHelper::$writingStyles,
				'narrativeStyles' => MyHelper::$narrativeStyles,
			]);
		}

		public function writeBeats(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			$bookData = json_decode(File::get($bookJsonPath), true);
			$actsData = json_decode(File::get($actsJsonPath), true);

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

			$current_chapter = null;
			$previous_chapter = null;
			$next_chapter = null;
			foreach ($acts as $act) {
				foreach ($act['chapters'] as $chapter) {
					if ($current_chapter && !$next_chapter) {
						$next_chapter = $chapter;
						break;
					}

					if ($chapter['chapterFilename'] === $chapterFilename) {
						$current_chapter = $chapter;
					}

					if (!$current_chapter) {
						$previous_chapter = $chapter;
					}

				}
			}

			$previous_chapter_beats = $current_chapter['from_previous_chapter'];
			if ($previous_chapter && array_key_exists('beats', $previous_chapter)) {
				if ($previous_chapter['beats']) {
					$previous_chapter_beats = '';
					foreach ($previous_chapter['beats'] as $beat) {
						if (key_exists('beat_summary', $beat) && $beat['beat_summary'] !== '') {
							$previous_chapter_beats .= $beat['beat_summary'] . "\n";
						} else {
							$previous_chapter_beats .= ($beat['description'] ?? '') . "\n";
						}
					}
				}
			}

			$save_results = ($request->input('save_results', 'true') === 'true');

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');
			$beats_per_chapter = (int)$request->input('beats_per_chapter', 3);

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}
			$prompt = File::get(resource_path('prompts/beat_prompt.txt'));

			$beats_per_chapter_list = '';
			for ($i = 0; $i < $beats_per_chapter; $i++) {
				$beats_per_chapter_list .= "{\"description\":\"write beat " . ($i + 1) . " for this chapter.\"}";
				if ($i < $beats_per_chapter - 1) {
					$beats_per_chapter_list .= ",\n";
				}
			}

			$writing_style = $request->input('writing_style', 'Minimalist');
			$narrative_style = $request->input('narrative_style', 'Third Person - The narrator has a godlike perspective');

			if (isset($current_chapter['events']) && is_array($current_chapter['events'])) {
				$current_chapter['events'] = implode("\n", $current_chapter['events']);
			}
			if (isset($current_chapter['places']) && is_array($current_chapter['places'])) {
				$current_chapter['places'] = implode("\n", $current_chapter['places']);
			}
			if (isset($current_chapter['people']) && is_array($current_chapter['people'])) {
				$current_chapter['people'] = implode("\n", $current_chapter['people']);
			}

			$replacements = [
				'##book_title##' => $bookData['title'] ?? 'no title',
				'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
				'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
				'##language##' => $bookData['language'] ?? 'English',
				'##act##' => $current_chapter['row'] ?? 'no act',
				'##chapter##' => $current_chapter['name'] ?? 'no name',
				'##description##' => $current_chapter['short_description'] ?? 'no description',
				'##events##' => $current_chapter['events'] ?? 'no events',
				'##people##' => $current_chapter['people'] ?? 'no people',
				'##places##' => $current_chapter['places'] ?? 'no places',
				'##previous_chapter##' => $previous_chapter_beats ?? 'Beginning of the book',
				'##next_chapter##' => $current_chapter['to_next_chapter'] ?? 'No more chapters',
				'##beats_per_chapter##' => $beats_per_chapter,
				'##beats_per_chapter_list##' => $beats_per_chapter_list,
				'##character_profiles##' => $bookData['character_profiles'] ?? 'no character profiles',
				'##genre##' => $bookData['genre'] ?? 'fantasy',
				'##writing_style##' => $writing_style,
				'##narrative_style##' => $narrative_style,
			];

//			Log::info('Prompt replacements: ' . json_encode($replacements));

			$prompt = str_replace(array_keys($replacements), array_values($replacements), $prompt);

			$example_question = '';
			$example_answer = '';
			$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 2, 5);
			if ($similar_prompts === []) {
				$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 1, 5);
			}

			if ($similar_prompts !== []) {
				$question_index = 0;
				$question = null;
				shuffle($similar_prompts);
				while ($question_index < count($similar_prompts)) {
					$question_id = $similar_prompts[$question_index]->questions_id;
					$question = SentencesTable::where('id', $question_id)->first();
					if ($question) {
						break;
					}
					$question_index++;
				}
				if ($question) {
					$example_question = $question['prompt'];
					$example_answer = $question['sentences'];
				}
			}

			$resultData = MyHelper::llm_no_tool_call($llm, $example_question, $example_answer, $prompt, true);

			if (isset($resultData->error)) {
				return response()->json(['success' => false, 'message' => $resultData->error]);
			}

			//loop all data fields and replace <BR> with \n
			foreach ($resultData as $key => $value) {
				if (gettype($value) === 'string') {
					$resultData[$key] = str_replace('<BR>', "\n", $value);
				} else if (gettype($value) === 'array') {
					foreach ($value as $key2 => $value2) {
						if (gettype($value2) === 'string') {
							$resultData[$key][$key2] = str_replace('<BR>', "\n", $value2);
						}
					}
				}
			}

			$beats = null;
			if (isset($resultData['beats'])) {
				$beats = $resultData['beats'];
			} elseif (is_array($resultData)) {
				$beats = $resultData;
			}

			if ($beats) {
				$chapterFilePath = "{$bookPath}/{$current_chapter['chapterFilename']}";

				if ($save_results) {
					if (file_exists($chapterFilePath)) {
						$chapterData = json_decode(file_get_contents($chapterFilePath), true);
						$chapterData['beats'] = $beats;
						$chapterData['example_question'] = $example_question;
						$chapterData['example_answer'] = $example_answer;

						if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
							return response()->json(['success' => true, 'message' => 'Wrote beats to file.', 'beats' => $beats]);
						} else {
							return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
						}
					} else {
						return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
					}
				}
			} else {
				return response()->json(['success' => false, 'message' => __('Failed to generate beats')]);
			}

			return response()->json(['success' => true, 'message' => 'Generated beats.', 'beats' => $beats]);
		}

		public function writeBeatDescription(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			$bookData = json_decode(File::get($bookJsonPath), true);
			$actsData = json_decode(File::get($actsJsonPath), true);

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


			$current_chapter = null;
			$previous_chapter = null;
			$next_chapter = null;
			foreach ($acts as $act) {
				foreach ($act['chapters'] as $chapter) {
					if ($current_chapter && !$next_chapter) {
						$next_chapter = $chapter;
						break;
					}

					if ($chapter['chapterFilename'] === $chapterFilename) {
						$current_chapter = $chapter;
					}

					if (!$current_chapter) {
						$previous_chapter = $chapter;
					}
				}
			}

			$save_results = ($request->input('save_results', 'true') === 'true');
			$beatIndex = (int)$request->input('beat_index', 0);
			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');
			$current_beat = $request->input('current_beat', '');

			$writing_style = $request->input('writing_style', 'Minimalist');
			$narrative_style = $request->input('narrative_style', 'Third Person - The narrator has a godlike perspective');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}

			$createPrompt = $request->input('create_prompt', 'false');
			$createPrompt = $createPrompt === 'true';

			if ($createPrompt) {
				$previous_beat_summaries = '';
				$last_beat_full_text = '';
				$next_beat = '';

				// Get the current beat
				if ($current_beat === '') {
					if (isset($current_chapter['beats'][$beatIndex])) {
						$current_beat = $current_chapter['beats'][$beatIndex]['beat_text'] ?? '';
					}
				}

				// Process previous beats and last beat
				if ($beatIndex > 0) {
					for ($i = 0; $i < $beatIndex; $i++) {
						if ($i === $beatIndex - 1) {
							$last_beat_full_text = $current_chapter['beats'][$i]['beat_text'] ?? '';
						} else {
							$current_beat_summary = $current_chapter['beats'][$i]['beat_summary'] ?? $current_chapter['beats'][$i]['description'] ?? '';
							$previous_beat_summaries .= $current_beat_summary . "\n";
						}
					}
				} else {
					// If it's the first beat of the chapter, look at the previous chapter
					if ($previous_chapter !== null && isset($previous_chapter['beats'])) {
						$prev_chapter_beats = $previous_chapter['beats'];
						$prev_beats_count = count($prev_chapter_beats);

						for ($i = 0; $i < $prev_beats_count; $i++) {
							if ($i === $prev_beats_count - 1) {
								$last_beat_full_text = $prev_chapter_beats[$i]['beat_text'] ?? '';
							} else {
								$current_beat_summary = $prev_chapter_beats[$i]['beat_summary'] ?? $prev_chapter_beats[$i]['description'] ?? '';
								$previous_beat_summaries .= $current_beat_summary . "\n";
							}
						}
					}
				}

				// Process next beat
				if (isset($current_chapter['beats'][$beatIndex + 1])) {
					$next_beat = $current_chapter['beats'][$beatIndex + 1]['description'] ?? '';
				} else {
					// If it's the last beat of the chapter, look at the next chapter
					if ($next_chapter !== null && isset($next_chapter['beats'][0])) {
						$next_beat = $next_chapter['beats'][0]['description'] ?? '';
					}
				}

				// Trim any trailing newlines from previous_beat_summaries
				$previous_beat_summaries = rtrim($previous_beat_summaries);


				// Load the beat prompt template
				$beatPromptTemplate = File::get(resource_path('prompts/beat_description_prompt.txt'));

				if (isset($current_chapter['events']) && is_array($current_chapter['events'])) {
					$current_chapter['events'] = implode("\n", $current_chapter['events']);
				}
				if (isset($current_chapter['places']) && is_array($current_chapter['places'])) {
					$current_chapter['places'] = implode("\n", $current_chapter['places']);
				}
				if (isset($current_chapter['people']) && is_array($current_chapter['people'])) {
					$current_chapter['people'] = implode("\n", $current_chapter['people']);
				}

				$replacements = [
					'##book_title##' => $bookData['title'] ?? 'no title',
					'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
					'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
					'##language##' => $bookData['language'] ?? 'English',
					'##character_profiles##' => $bookData['character_profiles'] ?? 'no character profiles',
					'##act##' => $current_chapter['row'] ?? 'no act',
					'##chapter##' => $current_chapter['name'] ?? 'no name',
					'##description##' => $current_chapter['short_description'] ?? 'no description',
					'##events##' => $current_chapter['events'] ?? 'no events',
					'##people##' => $current_chapter['people'] ?? 'no people',
					'##places##' => $current_chapter['places'] ?? 'no places',
					'##previous_chapter##' => $previous_chapter_beats ?? 'Beginning of the book',
					'##next_chapter##' => $current_chapter['to_next_chapter'] ?? 'No more chapters',
					'##previous_beat_summaries##' => $previous_beat_summaries,
					'##last_beat_full_text##' => $last_beat_full_text,
					'##codex##' => ($bookData['codex']['characters'] ?? 'no characters') . "\n" .
						($bookData['codex']['locations'] ?? 'no locations') . "\n" .
						($bookData['codex']['objects'] ?? 'no objects') . "\n" .
						($bookData['codex']['lore'] ?? 'no lore'),
					'##current_beat##' => $current_beat,
					'##next_beat##' => $next_beat,
					'##genre##' => $bookData['genre'] ?? 'fantasy',
					'##writing_style##' => $writing_style,
					'##narrative_style##' => $narrative_style,
				];

				$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

				echo json_encode(['success' => true, 'prompt' => $beatPrompt]);

			} else {

				$beatPrompt = $request->input('beat_prompt', '');

				$example_question = '';
				$example_answer = '';

				if (array_key_exists('example_question', $current_chapter) && array_key_exists('example_answer', $current_chapter)) {
					$example_question = $current_chapter['example_question'];
					$example_answer = $current_chapter['example_answer'];
				}

				$resultData = MyHelper::llm_no_tool_call($llm, $example_question, $example_answer, $beatPrompt, false);

				if (isset($resultData->error)) {
					return response()->json(['success' => false, 'message' => $resultData->error]);
				}


				$resultData = str_replace('<BR>', "\n", $resultData);

				if ($save_results) {
					$chapterFilePath = "{$bookPath}/{$chapterFilename}";

					if (file_exists($chapterFilePath)) {
						$chapterData = json_decode(file_get_contents($chapterFilePath), true);
						$chapterData['beats'][$beatIndex]['description'] = $resultData;

						if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
							return response()->json(['success' => true, 'message' => 'Wrote beat description to file.', 'prompt' => $resultData]);
						} else {
							return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
						}
					} else {
						return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
					}
				}

				echo json_encode(['success' => true, 'prompt' => $resultData]);
			}
		}

		public function writeBeatText(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");
			$bookJsonPath = "{$bookPath}/book.json";
			$actsJsonPath = "{$bookPath}/acts.json";

			$bookData = json_decode(File::get($bookJsonPath), true);
			$actsData = json_decode(File::get($actsJsonPath), true);

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


			$current_chapter = null;
			$previous_chapter = null;
			$next_chapter = null;
			foreach ($acts as $act) {
				foreach ($act['chapters'] as $chapter) {
					if ($current_chapter && !$next_chapter) {
						$next_chapter = $chapter;
						break;
					}

					if ($chapter['chapterFilename'] === $chapterFilename) {
						$current_chapter = $chapter;
					}

					if (!$current_chapter) {
						$previous_chapter = $chapter;
					}
				}
			}

			$save_results = ($request->input('save_results', 'true') === 'true');
			$beatIndex = (int)$request->input('beat_index', 0);
			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');
			$current_beat = $request->input('current_beat', '');


			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}

			$previous_beat_summaries = '';
			$last_beat_full_text = '';
			$next_beat = '';

			// Get the current beat
			if ($current_beat === '') {
				if (isset($current_chapter['beats'][$beatIndex])) {
					$current_beat = $current_chapter['beats'][$beatIndex]['beat_text'] ?? '';
				}
			}

			// Process previous beats and last beat
			if ($beatIndex > 0) {
				for ($i = 0; $i < $beatIndex; $i++) {
					if ($i === $beatIndex - 1) {
						$last_beat_full_text = $current_chapter['beats'][$i]['beat_text'] ?? '';
					} else {
						$current_beat_summary = $current_chapter['beats'][$i]['beat_summary'] ?? $current_chapter['beats'][$i]['description'] ?? '';
						$previous_beat_summaries .= $current_beat_summary . "\n";
					}
				}
			} else {
				// If it's the first beat of the chapter, look at the previous chapter
				if ($previous_chapter !== null && isset($previous_chapter['beats'])) {
					$prev_chapter_beats = $previous_chapter['beats'];
					$prev_beats_count = count($prev_chapter_beats);

					for ($i = 0; $i < $prev_beats_count; $i++) {
						if ($i === $prev_beats_count - 1) {
							$last_beat_full_text = $prev_chapter_beats[$i]['beat_text'] ?? '';
						} else {
							$current_beat_summary = $prev_chapter_beats[$i]['beat_summary'] ?? $prev_chapter_beats[$i]['description'] ?? '';
							$previous_beat_summaries .= $current_beat_summary . "\n";
						}
					}
				}
			}

			// Process next beat
			if (isset($current_chapter['beats'][$beatIndex + 1])) {
				$next_beat = $current_chapter['beats'][$beatIndex + 1]['description'] ?? '';
			} else {
				// If it's the last beat of the chapter, look at the next chapter
				if ($next_chapter !== null && isset($next_chapter['beats'][0])) {
					$next_beat = $next_chapter['beats'][0]['description'] ?? '';

					//if next chapter has no beats, use the chapter description
					if ($next_beat === '') {
						$next_beat = ($next_chapter['name'] ?? 'Next Chapter') . "\nNext Chapter Description:" . ($next_chapter['short_description'] ?? '');
					}
				} else if ($next_chapter !== null) {
					$next_beat = ($next_chapter['name'] ?? 'Next Chapter') . "\nNext Chapter Description:" . ($next_chapter['short_description'] ?? '');
				}
			}

			// Trim any trailing newlines from previous_beat_summaries
			$previous_beat_summaries = rtrim($previous_beat_summaries);


			$createPrompt = $request->input('create_prompt', 'false');
			$createPrompt = $createPrompt === 'true';

			if ($createPrompt) {
				// Load the beat prompt template
				$beatPromptTemplate = File::get(resource_path('prompts/beat_text_prompt.txt'));

				$writingStyle = $request->input('writing_style', 'Minimalist');
				$narrativeStyle = $request->input('narrative_style', 'Third Person - The narrator has a godlike perspective');

				if (isset($current_chapter['events']) && is_array($current_chapter['events'])) {
					$current_chapter['events'] = implode("\n", $current_chapter['events']);
				}
				if (isset($current_chapter['places']) && is_array($current_chapter['places'])) {
					$current_chapter['places'] = implode("\n", $current_chapter['places']);
				}
				if (isset($current_chapter['people']) && is_array($current_chapter['people'])) {
					$current_chapter['people'] = implode("\n", $current_chapter['people']);
				}

				$replacements = [
					'##book_title##' => $bookData['title'] ?? 'no title',
					'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
					'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
					'##language##' => $bookData['language'] ?? 'English',
					'##character_profiles##' => $bookData['character_profiles'] ?? 'no character profiles',
					'##act##' => $current_chapter['row'] ?? 'no act',
					'##chapter##' => $current_chapter['name'] ?? 'no name',
					'##description##' => $current_chapter['short_description'] ?? 'no description',
					'##events##' => $current_chapter['events'] ?? 'no events',
					'##people##' => $current_chapter['people'] ?? 'no people',
					'##places##' => $current_chapter['places'] ?? 'no places',
					'##previous_chapter##' => $previous_chapter_beats ?? 'Beginning of the book',
					'##next_chapter##' => $current_chapter['to_next_chapter'] ?? 'No more chapters',
					'##previous_beat_summaries##' => $previous_beat_summaries,
					'##last_beat_full_text##' => $last_beat_full_text,
					'##codex##' => ($bookData['codex']['characters'] ?? 'no characters') . "\n" .
						($bookData['codex']['locations'] ?? 'no locations') . "\n" .
						($bookData['codex']['objects'] ?? 'no objects') . "\n" .
						($bookData['codex']['lore'] ?? 'no lore'), '##current_beat##' => $current_beat,
					'##next_beat##' => $next_beat,
					'##genre##' => $bookData['genre'] ?? 'fantasy',
					'##writing_style##' => $writingStyle,
					'##narrative_style##' => $narrativeStyle,
				];

				$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

				echo json_encode(['success' => true, 'prompt' => $beatPrompt]);

			} else {

				$beatPrompt = $request->input('beat_prompt', '');

				$example_question = '';
				$example_answer = '';

				if (array_key_exists('example_question', $current_chapter) && array_key_exists('example_answer', $current_chapter)) {
					$example_question = $current_chapter['example_question'];
					$example_answer = $current_chapter['example_answer'];
				} else {
					$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 2, 5);
					if ($similar_prompts === []) {
						$similar_prompts = MyHelper::getEmbeddingSimilarity($current_chapter['short_description'], 0.1, 1, 5);
					}

					if ($similar_prompts !== []) {
						$question_index = 0;
						$question = null;
						shuffle($similar_prompts);
						while ($question_index < count($similar_prompts)) {
							$question_id = $similar_prompts[$question_index]->questions_id;
							$question = SentencesTable::where('id', $question_id)->first();
							if ($question) {
								break;
							}
							$question_index++;
						}
						if ($question) {
							$example_question = $question['prompt'];
							$example_answer = $question['sentences'];
						}
					}
				}


				$resultData = MyHelper::llm_no_tool_call($llm, $example_question, $example_answer, $beatPrompt, false);

				if (isset($resultData->error)) {
					return response()->json(['success' => false, 'message' => $resultData->error]);
				}


				$resultData = str_replace('<BR>', "\n", $resultData);

				if ($save_results) {
					$chapterFilePath = "{$bookPath}/{$chapterFilename}";

					if (file_exists($chapterFilePath)) {
						$chapterData = json_decode(file_get_contents($chapterFilePath), true);
						$chapterData['beats'][$beatIndex]['beat_text'] = $resultData;

						if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
							return response()->json(['success' => true, 'message' => 'Wrote beat text to file.', 'prompt' => $resultData]);
						} else {
							return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
						}
					} else {
						return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
					}
				}

				echo json_encode(['success' => true, 'prompt' => $resultData]);
			}
		}

		public function writeBeatSummary(Request $request, $bookSlug, $chapterFilename)
		{
			$verified = MyHelper::verifyBookOwnership($bookSlug);
			if (!$verified['success']) {
				return response()->json($verified);
			}

			$bookPath = Storage::disk('public')->path("books/{$bookSlug}");

			$currentBeatDescription = $request->input('current_beat_description') ?? '';
			$currentBeatText = $request->input('current_beat_text') ?? '';
			$beatIndex = $request->input('beat_index', 0);
			$save_results = ($request->input('save_results', 'false') === 'true');

			$llm = $request->input('llm', 'anthropic/claude-3-haiku:beta');

			if ($llm === 'anthropic-haiku' || $llm === 'anthropic-sonet') {
				$model = $llm === 'anthropic-haiku' ? env('ANTHROPIC_HAIKU_MODEL') : env('ANTHROPIC_SONET_MODEL');
			} elseif ($llm === 'open-ai-gpt-4o' || $llm === 'open-ai-gpt-4o-mini') {
				$model = $llm === 'open-ai-gpt-4o' ? env('OPEN_AI_GPT4_MODEL') : env('OPEN_AI_GPT4_MINI_MODEL');
			} else {
				$model = $llm;
			}

			// Load the beat prompt template
			$beatPromptTemplate = File::get(resource_path('prompts/beat_summary.txt'));

			if (isset($current_chapter['events']) && is_array($current_chapter['events'])) {
				$current_chapter['events'] = implode("\n", $current_chapter['events']);
			}
			if (isset($current_chapter['places']) && is_array($current_chapter['places'])) {
				$current_chapter['places'] = implode("\n", $current_chapter['places']);
			}
			if (isset($current_chapter['people']) && is_array($current_chapter['people'])) {
				$current_chapter['people'] = implode("\n", $current_chapter['people']);
			}

			$replacements = [
				'##book_title##' => $bookData['title'] ?? 'no title',
				'##back_cover_text##' => $bookData['back_cover_text'] ?? 'no back cover text',
				'##book_blurb##' => $bookData['blurb'] ?? 'no blurb',
				'##language##' => $bookData['language'] ?? 'English',
				'##act##' => $current_chapter['row'] ?? 'no act',
				'##chapter##' => $current_chapter['name'] ?? 'no name',
				'##description##' => $current_chapter['short_description'] ?? 'no description',
				'##events##' => $current_chapter['events'] ?? 'no events',
				'##people##' => $current_chapter['people'] ?? 'no people',
				'##places##' => $current_chapter['places'] ?? 'no places',
				'##beat_summary##' => $currentBeatDescription ?? '',
				'##beat_text##' => $currentBeatText ?? '',
			];

			$beatPrompt = str_replace(array_keys($replacements), array_values($replacements), $beatPromptTemplate);

			$resultData = MyHelper::llm_no_tool_call($llm, '', '', $beatPrompt, false);

			if (isset($resultData->error)) {
				return response()->json(['success' => false, 'message' => $resultData->error]);
			}

			if ($save_results) {
				$chapterFilePath = "{$bookPath}/{$chapterFilename}";

				if (file_exists($chapterFilePath)) {
					$chapterData = json_decode(file_get_contents($chapterFilePath), true);
					$chapterData['beats'][$beatIndex]['beat_summary'] = $resultData;

					if (file_put_contents($chapterFilePath, json_encode($chapterData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
						return response()->json(['success' => true, 'message' => 'Wrote beat summary to file.', 'prompt' => $resultData]);
					} else {
						return response()->json(['success' => false, 'message' => __('Failed to write to file')]);
					}
				} else {
					return response()->json(['success' => false, 'message' => __('Chapter file not found')]);
				}
			}

			echo json_encode(['success' => true, 'prompt' => $resultData]);
		}

	}
