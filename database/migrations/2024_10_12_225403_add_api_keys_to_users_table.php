<?php

	use Illuminate\Database\Migrations\Migration;
	use Illuminate\Database\Schema\Blueprint;
	use Illuminate\Support\Facades\Schema;

	class AddApiKeysToUsersTable extends Migration
	{
		public function up()
		{
			Schema::table('users', function (Blueprint $table) {
				$table->string('openai_api_key')->nullable();
				$table->string('anthropic_key')->nullable();
				$table->string('openrouter_key')->nullable();
			});
		}

		public function down()
		{
			Schema::table('users', function (Blueprint $table) {
				$table->dropColumn(['openai_api_key', 'anthropic_key', 'openrouter_key']);
			});
		}
	}
