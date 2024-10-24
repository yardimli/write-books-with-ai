<?php

	namespace App\Models;


	use Illuminate\Database\Eloquent\Factories\HasFactory;
	use Illuminate\Database\Eloquent\Model;

	class ParagraphsTable extends Model
	{
		use HasFactory;

		protected $table = 'paragraphs_table';

		protected $fillable = [
			'prompt',
			'filename',
			'paragraphs',
			'paragraph_order',
			'word_count',
			'created_at',
			'updated_at',
		];
	}
