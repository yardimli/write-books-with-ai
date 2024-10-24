<?php

	namespace App\Console\Commands;

	use App\Helpers\MyHelper;
	use Illuminate\Console\Command;
	use App\Models\SentencesTable;

	class CreateQuestionPrompt extends Command
	{
		protected $signature = 'sentence:create-question-prompt {--count=1 : Number of times to run the command}';
		protected $description = 'Echo random sentences where prompt is null, and set the prompt to the sentence.';

		public function handle()
		{
			$count = $this->option('count');

			for ($i = 0; $i < $count; $i++) {
				$this->processRandomSentence();

				if ($i < $count - 1) {
					$this->info("\n--- Next Iteration ---\n");
				}
			}
		}

		private function processRandomSentence()
		{
			$randomSentence = SentencesTable::whereNull('prompt')
				->inRandomOrder()
				->first();

			if ($randomSentence) {
				$this->info("Random sentence: \n" . $randomSentence->sentences);

				$prompt = "Respond with a short single sentence prompt that would generate the following text: \n" . $randomSentence->sentences;

				$promptResult = MyHelper::llm_no_tool_call( 'open-ai-gpt-4o-mini', '', '', $prompt, false, 'english');

				$this->info("Prompt result: \n\n\n" . $promptResult);

				if (strlen($promptResult) > 200) {
					$this->error("Prompt result is too long. Please try again.");
				} else {

					SentencesTable::where('id', $randomSentence->id)
						->update(['prompt' => $promptResult]);
				}

			} else {
				$this->error("No sentences found with null prompt.");
			}
		}
	}
