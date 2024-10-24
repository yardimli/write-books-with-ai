<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	return new class extends Migration {
		/**
		 * Run the migrations.
		 *
		 * @return void
		 */
		public function up()
		{
			DB::connection('pgsql')->getSchemaBuilder()->create('question_embeddings_pg', function (Blueprint $table) {
				$table->id();
				$table->unsignedBigInteger('questions_id');
				$table->unsignedBigInteger('field_type')->default(0);
				$table->vector('embedding', 256)->nullable();
				$table->timestamps();

				$table->index('questions_id');
				$table->index('field_type');
			});
		}

		/**
		 * Reverse the migrations.
		 *
		 * @return void
		 */
		public function down()
		{
			Schema::dropIfExists('question_embeddings_pg');
		}
	};
