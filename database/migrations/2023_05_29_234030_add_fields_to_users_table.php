<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->text('about_me')->nullable();
			$table->integer('tokens_left')->default(30000);
			$table->integer('member_status')->default(1);
			$table->integer('member_type')->default(1);
			$table->dateTime('last_login')->default(now())->nullable();
			$table->string('last_ip')->nullable();
		});

		Schema::create('token_usages', function (Blueprint $table) {
			$table->id();
			$table->unsignedBigInteger('user_id');
			$table->integer('prompt_tokens')->default(0);
			$table->integer('completion_tokens')->default(0);
			$table->integer('credit_tokens')->default(0);
			$table->string('usage_type')->nullable();
			$table->string('product_name')->nullable();
			$table->unsignedBigInteger('order_id')->default(0);
			$table->timestamps();
		});

	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn('about_me');
			$table->dropColumn('tokens_left');
			$table->dropColumn('member_status');
			$table->dropColumn('member_type');
			$table->dropColumn('last_login');
			$table->dropColumn('last_ip');
		});

		Schema::dropIfExists('token_usages');
	}
};
