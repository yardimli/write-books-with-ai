<?php

	namespace App\Console\Commands;

	use App\Helpers\MyHelper;
	use Illuminate\Console\Command;
	use App\Models\SentencesTable;
	use Illuminate\Support\Facades\DB;

	class AddVectors extends Command
	{
		protected $signature = 'vectors:add {field_type} {--count=1 : Number of times to run the command}';
		protected $description = 'Add vectors to the database';


		public function handle()
		{
			$field_type = $this->argument('field_type');
			$count = $this->option('count');


			$this->info("Adding vectors to the database for field type: $field_type");

			for ($i = 0; $i < $count; $i++) {
				$this->info("\n--- Iteration " . ($i+1) . " of " . $count . "---\n");

				$randomSentence = SentencesTable::whereNotNull('prompt')
					->inRandomOrder()
					->first();

				if ($randomSentence) {
					$this->info("Prompt: \n" . $randomSentence->prompt . "\n\nAnswer:\n" . $randomSentence->sentences);

					//check question_embeddings_pg table for field_type 1 exist?
					$question_embedding = DB::connection('pgsql')->table('question_embeddings_pg')->where('questions_id', $randomSentence->id)->where('field_type', 1)->first();

					//check question_embeddings_pg table for field_type 2 exist?
					$answer_embedding = DB::connection('pgsql')->table('question_embeddings_pg')->where('questions_id', $randomSentence->id)->where('field_type', 2)->first();


					if (!$question_embedding) {

						$question_embedding = MyHelper::getEmbedding($randomSentence->prompt);
						$question_embedding = $question_embedding['data'][0]['embedding'];

						$this->info("Question embedding: \n" . json_encode($question_embedding));
						$field_type = 1; // 1 for question, 2 for answer
						$question_add_id = DB::connection('pgsql')->table('question_embeddings_pg')->insertGetId([
							'questions_id' => $randomSentence->id,
							'field_type' => $field_type,
							'embedding' => json_encode($question_embedding),
							'created_at' => now(),
							'updated_at' => now()
						]);
					} else {
						$this->info("Question embedding already exists: \n" . json_encode($question_embedding));
					}

					if (!$answer_embedding) {
						$answer_embedding = MyHelper::getEmbedding($randomSentence->sentences);
						$answer_embedding = $answer_embedding['data'][0]['embedding'];

						$this->info("Answer embedding: \n" . json_encode($answer_embedding));
						$field_type = 2;  // 1 for question, 2 for answer
						$answer_add_id = DB::connection('pgsql')->table('question_embeddings_pg')->insertGetId([
							'questions_id' => $randomSentence->id,
							'field_type' => $field_type,
							'embedding' => json_encode($answer_embedding),
							'created_at' => now(),
							'updated_at' => now()
						]);
					} else {
						$this->info("Answer embedding already exists: \n" . json_encode($answer_embedding));
					}
				}
			}
		}
	}

