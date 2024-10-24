<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	class CreateSentencesTable extends Migration
	{
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up()
		{
			Schema::create('sentences_table', function (Blueprint $table) {
				$table->id();
				$table->string("prompt")->nullable();
				$table->string("filename")->nullable()->index();
				$table->integer("sentence_order")->default(0);
				$table->text("sentences")->nullable();
				$table->integer("word_count")->default(0);
				$table->string("language")->nullable()->default("english")->index();
				$table->timestamps();
			});
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down()
		{
			Schema::dropIfExists('sentences_table');
		}
	}
