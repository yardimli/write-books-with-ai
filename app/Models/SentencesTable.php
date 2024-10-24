<?php

	namespace App\Models;


	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;

	class SentencesTable extends Model
	{
		use HasFactory;

		protected $table = 'sentences_table';

		protected $fillable = [
			'prompt',
			'filename',
			'sentences',
			'sentence_order',
			'word_count',
			'created_at',
			'updated_at',
		];
	}
