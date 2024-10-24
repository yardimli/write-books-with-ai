<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{__('default.Write Books With AI')}}</title>
	
	<!-- FAVICON AND TOUCH ICONS -->
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	
	<meta name="csrf-token" content="{{ csrf_token() }}">
	
	<!-- Bootstrap CSS -->
	<link href="/css/bootstrap.css" rel="stylesheet">
	<link href="/css/bootstrap-icons.min.css" rel="stylesheet">
	<!-- Custom styles for this template -->
	<link href="/css/custom.css" rel="stylesheet">
	<link rel="stylesheet" href="/css/introjs.css">
	<link rel="stylesheet" href="/css/introjs-dark.css" disabled>
	
	<script>
		let bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
		let colorOptions = @json($colorOptions);
	</script>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<div class="mb-1 mt-1 w-100" style="text-align: right;">
			<button class="btn btn-sm btn-info mb-1 mt-1 me-2" id="showBookDetailsBtn">
				<i class="bi bi-info-circle"></i> {{__('default.Book Details')}}
			</button>
			<a class="btn btn-sm btn-primary mb-1 mt-1" href="{{route('my-books')}}"><i
					class="bi bi-bookshelf"></i> {{__('default.Back to My Books')}}</a>
			<a class="btn btn-sm btn-primary" href="{{route('book-details',$book_slug)}}"><i
					class="bi bi-book"></i> {{__('default.Back to Book Page')}}</a>
		</div>
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<h3 style="margin:10px;" class="text-center" id="bookTitle">{{$book['title']}}</h3>
			</div>
			<div class="card-body modal-content modal-content-color d-flex flex-row">
				<!-- Image Div -->
				<div class="row">
					
					<!-- Text Blocks Div -->
					<div class="col-12 col-xl-6">
						
						<span for="llmSelect" class="form-label">{{__('default.AI Engines:')}}
							@if (Auth::user() && Auth::user()->isAdmin())
								<label class="badge bg-danger">Admin</label>
							@endif
						
						</span>
						<select id="llmSelect" class="form-select mx-auto">
							<option value="">{{__('default.Select an AI Engine')}}</option>
							@if (Auth::user() && Auth::user()->isAdmin())
								<option value="anthropic-sonet">anthropic :: claude-3.5-sonnet (direct)</option>
								<option value="anthropic-haiku">anthropic :: haiku (direct)</option>
								<option value="open-ai-gpt-4o">openai :: gpt-4o (direct)</option>
								<option value="open-ai-gpt-4o-mini">openai :: gpt-4o-mini (direct)</option>
							@endif
							@if (Auth::user() && !empty(Auth::user()->anthropic_key))
								<option value="anthropic-sonet">anthropic :: claude-3.5-sonnet (direct)</option>
								<option value="anthropic-haiku">anthropic :: haiku (direct)</option>
							@endif
							@if (Auth::user() && !empty(Auth::user()->openai_api_key))
								<option value="open-ai-gpt-4o">openai :: gpt-4o (direct)</option>
								<option value="open-ai-gpt-4o-mini">openai :: gpt-4o-mini (direct)</option>
							@endif
						</select>
					</div>
					
					<div class="col-12 col-lg-6" id="beatsPerChapterLabel">
						
						<span style="font-size: 18px;">{{__('default.Number of beats per chapter:')}}</span>
						<select id="beatsPerChapter" class="form-select mx-auto mb-1">
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							<option value="10">10</option>
						</select>
					</div>
					
					<div class="col-12 col-lg-6">
						<span for="writingStyle" class="form-label">{{__('default.Writing Style')}}:</span>
						<select class="form-control" id="writingStyle" name="writingStyle" required>
							@foreach($writingStyles as $style)
								@if ($style['value'] === $book['writing_style'])
									<option value="{{ $style['value'] }}" selected>{{ $style['label'] }}</option>
								@else
									<option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
								@endif
							@endforeach
						</select>
					</div>
					
					<div class="col-12 col-lg-6">
						<span for="narrativeStyle" class="form-label">{{__('default.Narrative Style')}}:</span>
						<select class="form-control" id="narrativeStyle" name="narrativeStyle" required>
							@foreach($narrativeStyles as $style)
								@if ($style['value'] === $book['narrative_style'])
									<option value="{{ $style['value'] }}" selected>{{ $style['value'] }}</option>
								@else
									<option value="{{ $style['value'] }}">{{ $style['value'] }}</option>
								@endif
							@endforeach
						</select>
					</div>
					
					<div class="col-12 d-none" id="modelInfo">
						<div class="mt-1 small" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px;">
							<div id="modelDescription"></div>
							<div id="modelPricing"></div>
						</div>
					</div>
					
					
					<div class="col-12">
						<button class="btn btn-danger mb-1 mt-2" id="generateAllBeatsBtn"
						        title="{{__('default.Write All Beats')}}"><i
								class="bi bi-lightning-charge"></i> {{__('default.Write All Beats')}}
						</button>
						
						<a class="btn bt-lg btn-primary mb-1 mt-2 editAllBeatsLink"
						   href="/book-beats/{{$book_slug}}/all-chapters"><i class="bi bi-check-all"></i> {{__('default.Edit All Beats')}}</a>
						
						<a href="{{route('book-codex',[$book_slug])}}" class="btn btn-secondary mb-1 mt-2" id="openCodexBtn">
							<i class="bi bi-book"></i> {{__('default.Open Codex')}}
						</a>
						
						@if (Auth::user())
							@if (Auth::user()->email === $book['owner'] || Auth::user()->name === $book['owner'] || Auth::user()->isAdmin())
								<button class="btn btn-primary mb-1 mt-2" id="editBookDetailsBtn">
									<i class="bi bi-pencil-square"></i> {{__('default.Edit Book Details')}}
								</button>
								
								<button class="btn btn-primary mb-1 mt-2" id="openLlmPromptModalBtn">
									<i class="bi bi-chat-dots"></i> {{__('default.Chat with AI')}}
								</button>
								
								<button class="btn btn-danger delete-book-btn mb-1 mt-2"
								        data-book-id="<?php echo urlencode($book_slug); ?>"><i
										class="bi bi-trash-fill"></i> {{__('default.Delete Book')}}
								</button>
							@endif
						@endif
					
						<br>
						<a href="#" id="restartTour">{{ __('default.Restart Tour') }}</a>
					
					</div>
				</div>
			
			</div>
		</div>
		
		<div class="book-chapter-board" id="bookBoard">
			@foreach ($book['acts'] as $act)
				<div class="card general-card mb-1">
					<div class="card-header modal-header modal-header-color">
						<div class="card-title">{{__('default.act_with_number', ['id' => $act['id']])}} — {{$act['title']}}</div>
					</div>
				</div>
				
				@foreach ($act['chapters'] as $chapter)
					<div class="card general-card">
						<div class="card-header modal-header modal-header-color">
							<div class="card-title">{{__('default.chapter_with_number', ['order' => $chapter['order']])}}
								— {{$chapter['name']}}</div>
						</div>
						<div class="card-body modal-content modal-content-color">
							<div class="row">
								<div class="col-9">
									<div class="mb-3">
										<label for="chapterName" class="form-label">{{__('default.Name')}}</label>
										<input type="text" class="form-control chapterName" value="{{$chapter['name']}}">
									</div>
								</div>
								<div class="col-3">
									<div class="mb-3">
										<label for="chapterOrder" class="form-label">{{__('default.Order')}}</label>
										<input type="text" class="form-control chapterOrder" value="{{$chapter['order']}}">
									</div>
								</div>
								<div class="col-12">
									<div class="mb-3">
										<label for="chapterShortDescription" class="form-label">{{__('default.Short Description')}}</label>
										<textarea class="form-control chapterShortDescription"
										          rows="3">{{$chapter['short_description']}}</textarea>
									</div>
								</div>
								<div class="col-12">
									<div class="mb-3">
										<label for="chapterEvents" class="form-label"> {{__('default.Events')}}</label>
										<textarea class="form-control chapterEvents" rows="2">{{$chapter['events']}}</textarea>
									</div>
								</div>
								<div class=" col-12">
									<div class="mb-3">
										<label for="chapterPeople" class="form-label">{{__('default.People')}}</label>
										<textarea class="form-control chapterPeople" rows="2">{{$chapter['people']}}</textarea>
									</div>
								</div>
								<div class=" col-12">
									<div class="mb-3">
										<label for="chapterPlaces" class="form-label"> {{__('default.Places')}}</label>
										<textarea class="form-control chapterPlaces" rows="2">{{$chapter['places']}}</textarea>
									</div>
								</div>
								<div class=" col-12">
									<div class="mb-3">
										<label for="chapterFromPreviousChapter"
										       class="form-label">{{__('default.Previous Chapter')}}</label>
										<textarea class="form-control chapterFromPreviousChapter"
										          rows="2">{{$chapter['from_previous_chapter']}}</textarea>
									</div>
								</div>
								<div class="col-12">
									<div class="mb-3">
										<label for="chapterToNextChapter" class="form-label"> {{__('default.Next Chapter')}}</label>
										<textarea class="form-control chapterToNextChapter"
										          rows="2">{{$chapter['to_next_chapter']}}</textarea>
									</div>
								</div>
							</div>
							<div class="row" style="margin-left: -15px; margin-right: -15px;">
								<div class="col-12 col-xl-4 col-lg-4 mb-2 mt-1">
									<button class="btn bt-lg btn-secondary w-100 update-chapter-btn"
									        data-chapter-filename="{{$chapter['chapterFilename']}}">
										{{__('default.Update Chapter')}}
									</button>
								</div>
								<div class="col-12 col-xl-4 col-lg-4 mb-2 mt-1">
									<a class="btn bt-lg btn-primary w-100 editBeatsLink"
									   href="/book-beats/{{$book_slug}}/{{str_replace('.json','', $chapter['chapterFilename'])}}">{{__('default.Open Beats')}}</a>
								</div>
								<div class="col-12 col-xl-4 col-lg-4 mb-2 mt-1">
									<div class="btn bt-lg btn-warning w-100 rewriteChapterBtn"
									     onclick="rewriteChapter('{{$chapter['chapterFilename']}}')">{{__('default.Rewrite Chapter')}}</div>
								</div>
							</div>
						</div>
					
					</div>
				@endforeach
			@endforeach
		
		</div>
	</div>
</main>

<!-- Modal for Generating All Beats -->
<div class="modal fade" id="generateAllBeatsModal" tabindex="-1" aria-labelledby="generateAllBeatsModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="generateAllBeatsModalLabel">{{__('default.Generating Beats for All Chapters')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div class="progress mb-3">
					<div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0"
					     aria-valuemax="100">0%
					</div>
				</div>
				<div id="generateAllBeatsLog"
				     style="height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;"></div>
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-secondary closeAndRefreshButton"> {{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="alertModalLabel">Alsert</h5>
				<button type="button" class="btn-close alert-modal-close-button" data-bs-dismiss="modal"
				        aria-label="{{__('default.Close')}}"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div id="alertModalContent"></div>
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-secondary alert-modal-close-button"
				        data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- Book Details Modal -->
<div class="modal fade" id="bookDetailsModal" tabindex="-1" aria-labelledby="bookDetailsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="bookDetailsModalLabel">{{__('default.Book Details')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<h6>{{__('default.Blurb')}}</h6>
				<p id="modalBookBlurb">{{$book['blurb']}}</p>
				
				<h6>{{__('default.Back Cover Text')}}</h6>
				<p id="modalBackCoverText">{!!str_replace("\n","<br>",$book['back_cover_text'])!!}</p>
				
				<h6>{{__('default.Prompt For Book')}}</h6>
				<p id="modalBookPrompt">{{$book['prompt'] ?? 'no prompt'}}</p>
				
				<h6>{{__('default.Character Profiles')}}</h6>
				<p id="modalBookCharacters">{!! str_replace("\n","<br>", $book['character_profiles'] ?? 'no characters')!!}</p>
			</div>
			<div class="modal-footer modal-footer-color">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>


<!-- Modal for Editing Book Details -->
<div class="modal fade" id="editBookDetailsModal" tabindex="-1" aria-labelledby="editBookDetailsModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="editBookDetailsModalLabel">{{__('default.Edit Book Details')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<form id="editBookDetailsForm">
					<div class="mb-3">
						<label for="editBlurb" class="form-label">{{__('default.Blurb')}}</label>
						<textarea class="form-control" id="editBlurb" rows="3"></textarea>
					</div>
					<div class="mb-3">
						<label for="editBackCoverText" class="form-label">{{__('default.Back Cover Text')}}</label>
						<textarea class="form-control" id="editBackCoverText" rows="5"></textarea>
					</div>
					<div class="mb-3">
						<label for="editCharacterProfiles" class="form-label">{{__('default.Character Profiles')}}</label>
						<textarea class="form-control" id="editCharacterProfiles" rows="5"></textarea>
					</div>
					<div class="mb-3">
						<label for="editAuthorName" class="form-label">{{__('default.Author Name')}}</label>
						<input type="text" class="form-control" id="editAuthorName">
					</div>
					<div class="mb-3">
						<label for="editPublisherName" class="form-label">{{__('default.Publisher Name')}}</label>
						<input type="text" class="form-control" id="editPublisherName">
					</div>
				</form>
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-primary" id="saveBookDetailsBtn">{{__('default.Save Changes')}}</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- LLM Prompt Modal -->
<div class="modal fade" id="llmPromptModal" tabindex="-1" aria-labelledby="llmPromptModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="llmPromptModalLabel">{{__('default.Chat with AI')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div class="mb-3">
					<label for="userPrompt" class="form-label">{{__('default.User Prompt')}}</label>
					<textarea class="form-control" id="userPrompt" rows="8"></textarea>
				</div>
				<div class="mb-3">
					<label for="llmResponse" class="form-label">{{__('default.LLM Response')}}</label>
					<textarea class="form-control" id="llmResponse" rows="10" readonly></textarea>
				</div>
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-primary" id="sendPromptBtn">{{__('default.Send Prompt')}}</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- Rewrite Chapter Modal -->
<div class="modal fade" id="rewriteChapterModal" tabindex="-1" aria-labelledby="rewriteChapterModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="rewriteChapterModalLabel">{{__('default.Rewrite Chapter')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div class="mb-3">
					<label for="rewriteUserPrompt" class="form-label">{{__('default.User Prompt')}}</label>
					<textarea class="form-control" id="rewriteUserPrompt" rows="10"></textarea>
				</div>
				<div class="mb-3">
					<h6>{{__('default.Rewritten Chapter:')}}</h6>
					<textarea class="form-control" id="rewriteResult" rows="10"></textarea>
				</div>
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-primary"
				        id="sendRewritePromptBtn">{{__('default.Rewrite Chapter')}}</button>
				<button type="button" class="btn btn-success" id="acceptRewriteBtn"
				        style="display: none;">{{__('default.Accept Rewrite')}}</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="deleteConfirmModalLabel">Confirm Delete</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				Are you sure you want to delete this book?
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
			</div>
		</div>
	</div>
</div>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="/js/jquery-3.7.0.min.js"></script>
<script src="/js/bootstrap.min.js"></script>
<script src="/js/moment.min.js"></script>

<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>
<script src="/js/intro.min.js"></script>

<script>
	
	let reload_window = false;
	let savedLlm = localStorage.getItem('edit-book-llm') || 'anthropic/claude-3-haiku:beta';
	let beatsPerChapter = localStorage.getItem('beats-per-chapter') || 3;
	$("#beatsPerChapter").val(beatsPerChapter);
	
	function saveChapter(chapterData) {
		$.ajax({
			url: `/book/${bookSlug}/chapter`,
			type: 'POST',
			data: chapterData,
			dataType: 'json',
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			success: function (response) {
				if (response.success) {
					reload_window = true;
					
					$('#save_result').html('<div class="alert alert-success">{{__('default.Chapter saved successfully!')}}</div>');
					$("#alertModalContent").html('{{__('default.Chapter saved successfully!')}}');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				} else {
					console.log(response);
					$("#alertModalContent").html('{{__('default.Failed to save chapter: ')}}' + response.message);
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			},
			error: function (xhr, status, error) {
				console.error(xhr.responseText);
				$("#alertModalContent").html('{{__('default.An error occurred while saving the chapter.')}}');
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			}
		});
	}
	
	function generateAllBeats(beatsPerChapter = 3, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
		const modal = $('#generateAllBeatsModal');
		const progressBar = modal.find('.progress-bar');
		const log = $('#generateAllBeatsLog');
		
		modal.modal({backdrop: 'static', keyboard: true}).modal('show');
		$('#generateAllBeatsLog').empty();
		progressBar.css('width', '0%').attr('aria-valuenow', 0).text('0%');
		
		$('#generateAllBeatsLog').append('<br>' + '{{__('default.This process will write 10 short beats for each chapter in the book. Later these beats will be turned into full book pages.')}}');
		$('#generateAllBeatsLog').append('<br>' + '{{__('default.Please wait...')}}');
		$('#generateAllBeatsLog').append('<br><br>{{__('default.If the progress bar is stuck for a long time, please refresh the page and try again.')}}<br><br>');
		
		chapters = bookData.acts.flatMap(act => act.chapters);
		
		console.log(chapters);
		generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, 0);
		
	}
	
	function generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, chapter_index = 0) {
		const modal = $('#generateAllBeatsModal');
		const log = $('#generateAllBeatsLog');
		
		const totalChapters = chapters.length;
		
		chapter_index++;
		
		const chapter = chapters[chapter_index - 1];
		$('#generateAllBeatsLog').append('<br><br>Processing chapter: ' + chapter.name);
		$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
		
		// Check if the chapter already has beats
		if (chapter.beats && chapter.beats.length > 0) {
			$('#generateAllBeatsLog').append('<br>Chapter "' + chapter.name + '" already has beats. Skipping...');
			
			const progressBar = modal.find('.progress-bar');
			const progress = Math.round((chapter_index / totalChapters) * 100);
			progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
			
			$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
			if (chapter_index < totalChapters) {
				generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, chapter_index);
			}
		} else {
			
			$.ajax({
				url: `/book/write-beats/${bookSlug}/${chapter.chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					beats_per_chapter: beatsPerChapter,
					writing_style: writingStyle,
					narrative_style: narrativeStyle,
					save_results: true,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						// Save the generated beats back to the chapter
						
						const progressBar = modal.find('.progress-bar');
						const progress = Math.round((chapter_index / totalChapters) * 100);
						progressBar.css('width', `${progress}%`).attr('aria-valuenow', progress).text(`${progress}%`);
						
						if (Array.isArray(response.beats)) {
							$('#generateAllBeatsLog').append('<br>Beats generated and saved for chapter: ' + chapter.name);
							
							response.beats.forEach((beat, index) => {
								$('#generateAllBeatsLog').append(`<br>${beat.description}`);
							});
						} else {
							$('#generateAllBeatsLog').append('<br>Beats failed for chapter: ' + chapter.name);
							$("#alertModalContent").html('Failed to generate beats: ' + response.beats);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
						if (chapter_index < totalChapters) {
							generateSingleChapterBeats(chapters, beatsPerChapter, writingStyle, narrativeStyle, chapter_index);
						} else {
							$('#generateAllBeatsLog').append('<br>' + '{{__('default.All chapters processed!')}}');
							$('#generateAllBeatsLog').scrollTop(log[0].scrollHeight);
						}
					} else {
						$('#generateAllBeatsLog').append('<br>Beats failed for chapter: ' + chapter.name);
						$("#alertModalContent").html('Failed to generate beats: ' + response.beats);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				
				error: function () {
					$('#generateAllBeatsLog').append('<p>Error generating beats for chapter: ' + chapter.name + '</p>');
					$('#generateAllBeats').scrollTop(log[0].scrollHeight);
					//break loop
				}
			});
		}
	}
	
	function rewriteChapter(chapterFilename) {
		const modal = $('#rewriteChapterModal');
		
		let chaptersToInclude = [];
		let foundCurrentChapter = false;
		let foundCurrentChapterData = [];
		for (let act of bookData.acts) {
			for (let chapter of act.chapters) {
				if (chapter.chapterFilename === chapterFilename) {
					foundCurrentChapter = true;
					foundCurrentChapterData = chapter;
					break;
				}
				chaptersToInclude.push(chapter);
			}
			if (foundCurrentChapter) {
				break;
			}
		}
		
		// Fetch the rewrite_chapter.txt template
		$.get('/prompts/rewrite_chapter.txt', function (template) {
			// Replace placeholders in the template
			const replacements = {
				'##user_blurb##': bookData.prompt || '',
				'##language##': bookData.language || 'English',
				'##book_title##': bookData.title || '',
				'##book_blurb##': bookData.blurb || '',
				'##book_keywords##': bookData.keywords ? bookData.keywords.join(', ') : '',
				'##back_cover_text##': bookData.back_cover_text || '',
				'##character_profiles##': bookData.character_profiles || '',
				'##genre##': bookData.genre || 'fantasy',
				'##adult_content##': bookData.adult_content || 'non-adult',
				'##writing_style##': $("#writingStyle").val() || 'Minimalist',
				'##narrative_style##': $("#narrativeStyle").val() || 'Third Person - The narrator has a godlike perspective',
				'##book_structure##': bookData.book_structure || 'the_1_act_story.txt',
				'##previous_chapters##': chaptersToInclude.map(ch =>
					`name: ${ch.name}\nshort description: ${ch.short_description}\nevents: ${ch.events}\npeople: ${ch.people}\nplaces: ${ch.places}\nfrom previous chapter: ${ch.from_previous_chapter}\nto next chapter: ${ch.to_next_chapter}\n\nbeats:\n${ch.beats ? ch.beats.map(b => b.beat_summary || b.description).join('\n') : ''}`
				).join('\n\n'),
				'##current_chapter##': `name: ${foundCurrentChapterData.name}\nshort description: ${foundCurrentChapterData.short_description}\nevents: ${foundCurrentChapterData.events}\npeople: ${foundCurrentChapterData.people}\nplaces: ${foundCurrentChapterData.places}\nfrom previous chapter: ${foundCurrentChapterData.from_previous_chapter}\nto next chapter: ${foundCurrentChapterData.to_next_chapter}`
			};
			
			for (const [key, value] of Object.entries(replacements)) {
				template = template.replace(new RegExp(key, 'g'), value);
			}
			
			$('#rewriteUserPrompt').val(template.trim());
			
			// Show the modal
			modal.modal({backdrop: 'static', keyboard: true}).modal('show');
		});
		
		// Handle the rewrite button click
		$('#sendRewritePromptBtn').off('click').on('click', function () {
			const userPrompt = $('#rewriteUserPrompt').val();
			$('#sendRewritePromptBtn').prop('disabled', true).text('{{__('default.Rewriting...')}}');
			
			$.ajax({
				url: '/rewrite-chapter',
				method: 'POST',
				data: {
					book_slug: bookSlug,
					llm: savedLlm,
					user_prompt: userPrompt
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						// Display the rewritten chapter in the modal
						$('#rewriteResult').val(JSON.stringify(response.rewrittenChapter, null, 2));
						$('#acceptRewriteBtn').show();
					} else {
						$("#alertModalContent").html('{{__('default.Failed to rewrite chapter:')}}' + response.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
					$('#sendRewritePromptBtn').prop('disabled', false).text('{{__('default.Rewrite Chapter')}}');
				},
				error: function () {
					$("#alertModalContent").html('{{__('default.Error rewriting chapter')}}');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					$('#sendRewritePromptBtn').prop('disabled', false).text('{{__('default.Rewrite Chapter')}}');
				}
			});
		});
		
		$('#acceptRewriteBtn').off('click').on('click', function () {
			$.ajax({
				url: '/accept-rewrite',
				method: 'POST',
				data: {
					book_slug: bookSlug,
					chapter_filename: chapterFilename,
					rewritten_content: $('#rewriteResult').val()
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						reload_window = true;
						$("#alertModalContent").html('{{__('default.Chapter rewritten successfully!')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					} else {
						$("#alertModalContent").html('{{__('default.Failed to save rewritten chapter:')}}' + response.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				error: function () {
					$("#alertModalContent").html('{{__('default.Error saving rewritten chapter')}}');
					$("#alertModal").modal('show');
				}
			});
		});
		
	}
	
	function getLLMsData() {
		return new Promise((resolve, reject) => {
			$.ajax({
				url: '/check-llms-json',
				type: 'GET',
				success: function (data) {
					resolve(data);
				},
				error: function (xhr, status, error) {
					reject(error);
				}
			});
		});
	}
	
	function linkify(text) {
		const urlRegex = /(https?:\/\/[^\s]+)/g;
		return text.replace(urlRegex, function (url) {
			return '<a href="' + url + '" target="_blank" rel="noopener noreferrer">' + url + '</a>';
		});
	}
	
	function isDarkMode() {
		let savedTheme = localStorage.getItem('theme') || 'light';
		if (savedTheme === 'dark') {
			return true;
		}
	}
	
	function toggleIntroJsStylesheet() {
		const lightStylesheet = document.querySelector('link[href="/css/introjs.css"]');
		const darkStylesheet = document.querySelector('link[href="/css/introjs-dark.css"]');
		
		if (isDarkMode()) {
			lightStylesheet.disabled = true;
			darkStylesheet.disabled = false;
		} else {
			lightStylesheet.disabled = false;
			darkStylesheet.disabled = true;
		}
	}
	
	function startIntro() {
		let intro = introJs().setOptions({
			steps: [
				{
					element: '#llmSelect',
					intro: 'Select an AI engine to use for generating content.'
				},
				{
					element: '#beatsPerChapter',
					intro: 'Choose how many beats should be generated for each chapter.'
				},
				{
					element: '#writingStyle',
					intro: 'Select the writing style for your book. You can change this later for individual chapters or beats.'
				},
				{
					element: '#narrativeStyle',
					intro: 'Choose the narrative style for your book. Again this can be changed later for individual chapters or beats.'
				},
				{
					element: '#generateAllBeatsBtn',
					intro: 'Click this button to generate beats for all chapters in your book. You can also generate beats for individual chapters in the chapter beat editor.'
				},
				{
					element: '#openCodexBtn',
					intro: 'Click here to open the Codex, you\'ll be able to auto update the codex from the beats already written.'
				},
				{
					element: '#editBookDetailsBtn',
					intro: 'To modify blurb, back cover text, character profiles, author name, and publisher name, click here. The changes will be applied to all future generated content.'
				},
				{
					element: '#openLlmPromptModalBtn',
					intro: 'Click here to chat with the AI engine you selected. You can use this to generate content for your book.'
				},
				{
					element: '.delete-book-btn',
					intro: 'Click this button to delete the book. This action cannot be undone.'
				},
				{
					element: '.chapterName',
					intro: 'The chapter name is already written. You can modify it if you want. This will effect how generated content is written.'
				},
				{
					element: '.chapterShortDescription',
					intro: 'Provide a short description of what happens in this chapter. Again this will effect how generated content is written.'
				},
				{
					element: '.chapterEvents',
					intro: 'List the key events that occur in this chapter. The AI will use this to narrow down the generated content.'
				},
				{
					element: '.chapterPeople',
					intro: 'Note the important characters involved in this chapter. This will help the AI generate content that is relevant to the characters.'
				},
				{
					element: '.chapterPlaces',
					intro: 'Mention the significant locations in this chapter. The AI will use this to generate content that is relevant to the locations.'
				},
				{
					element: '.chapterFromPreviousChapter',
					intro: 'Describe how this chapter connects to the previous one. This will help the AI generate content that flows smoothly from one chapter to the next. The AI also will use previosly generated beats to generate new ones.'
				},
				{
					element: '.chapterToNextChapter',
					intro: 'Explain how this chapter leads into the next one. This will help the AI generate content that flows smoothly from one chapter to the next. This is critical as the next chapter probably wont have any beats written yet.'
				},
				{
					element: '.update-chapter-btn',
					intro: 'Click this button to save your changes to the chapter.'
				},
				{
					element: '.editBeatsLink',
					intro: 'Click here to edit and generate the beats for this chapter.'
				},
				{
					element: '.rewriteChapterBtn',
					intro: 'Click this button to rewrite the chapter using the AI engine. You wll get to see and change the prompt we send to the AI to get the new chapter structure.'
				}
			],
			exitOnOverlayClick: false,
			showStepNumbers: true,
			showBullets: false,
			showProgress: true,
			nextLabel: "{{__('default.Next')}}",
			prevLabel: "{{__('default.Prev')}}",
			stepNumbersOfLabel: "{{__('default.of')}}",
			doneLabel: "{{__('default.Done')}}",
			
			
		});
		
		intro.onafterchange(function (targetElement) {
			// if (targetElement.tagName.toLowerCase() === 'textarea') {
			// 	var nextButton = document.querySelector('.introjs-nextbutton');
			// 	nextButton.classList.add('introjs-disabled');
			// 	nextButton.classList.add('custom-disabled'); // Add this line
			//
			// 	$(targetElement).on('input', function () {
			// 		if ($(this).val().trim() !== '') {
			// 			nextButton.classList.remove('introjs-disabled');
			// 			nextButton.classList.remove('custom-disabled'); // Add this line
			// 		} else {
			// 			nextButton.classList.add('introjs-disabled');
			// 			nextButton.classList.add('custom-disabled'); // Add this line
			// 		}
			// 	});
			// }
		});
		
		intro.oncomplete(function () {
			localStorage.setItem('editBookIntroCompleted', 'true');
		});
		
		intro.start();
		
	}
	
	
	let createCoverFileName = '';
	let bookToDelete = null;
	
	$(document).ready(function () {
		toggleIntroJsStylesheet();
		
		// Start the tour if it's the user's first time
		if (!localStorage.getItem('editBookIntroCompleted')) {
			setTimeout(function () {
				startIntro();
			}, 500);
		}
		
		document.addEventListener('click', function (event) {
			if (event.target.classList.contains('introjs-nextbutton') &&
				event.target.classList.contains('custom-disabled')) {
				event.preventDefault();
				event.stopPropagation();
			}
		}, true);
		
		
		// Restart tour button
		$('#restartTour').on('click', function (e) {
			e.preventDefault();
			localStorage.removeItem('editBookIntroCompleted');
			startIntro();
		});
		
		$('#showBookDetailsBtn').on('click', function() {
			$('#bookDetailsModal').modal('show');
		});
		
		
		getLLMsData().then(function (llmsData) {
			const llmSelect = $('#llmSelect');
			
			llmsData.forEach(function (model) {
				
				// Calculate and display pricing per million tokens
				let promptPricePerMillion = ((model.pricing.prompt || 0) * 1000000).toFixed(2);
				let completionPricePerMillion = ((model.pricing.completion || 0) * 1000000).toFixed(2);
				
				llmSelect.append($('<option>', {
					value: model.id,
					text: model.name + ' - $' + promptPricePerMillion + ' / $' + completionPricePerMillion,
					'data-description': model.description,
					'data-prompt-price': model.pricing.prompt || 0,
					'data-completion-price': model.pricing.completion || 0,
				}));
			});
			
			// Set the saved LLM if it exists
			if (savedLlm) {
				llmSelect.val(savedLlm);
			}
			
			llmSelect.on('click', function() {
				$('#modelInfo').removeClass('d-none');
			});
			
			// Show description on change
			llmSelect.change(function () {
				const selectedOption = $(this).find('option:selected');
				const description = selectedOption.data('description');
				const promptPrice = selectedOption.data('prompt-price');
				const completionPrice = selectedOption.data('completion-price');
				$('#modelDescription').html(linkify(description || ''));
				
				// Calculate and display pricing per million tokens
				const promptPricePerMillion = (promptPrice * 1000000).toFixed(2);
				const completionPricePerMillion = (completionPrice * 1000000).toFixed(2);
				
				$('#modelPricing').html(`
                <strong>Pricing (per million tokens):</strong> Prompt: $${promptPricePerMillion} - Completion: $${completionPricePerMillion}
            `);
			});
			
			// Trigger change to show initial description
			llmSelect.trigger('change');
		}).catch(function (error) {
			console.error('Error loading LLMs data:', error);
		});
		
		$("#llmSelect").on('change', function () {
			localStorage.setItem('edit-book-llm', $(this).val());
			savedLlm = $(this).val();
		});
		
		// change $llmSelect to savedLlm
		console.log('set llmSelect to ' + savedLlm);
		var dropdown = document.getElementById('llmSelect');
		var options = dropdown.getElementsByTagName('option');
		
		for (var i = 0; i < options.length; i++) {
			if (options[i].value === savedLlm) {
				dropdown.selectedIndex = i;
			}
		}
		
		$('.closeAndRefreshButton').on('click', function () {
			location.reload();
		});
		
		$('.delete-book-btn').on('click', function (e) {
			e.preventDefault();
			bookToDelete = $(this).data('book-id');
			$('#deleteConfirmModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		});
		
		$('#confirmDeleteBtn').on('click', function () {
			if (bookToDelete) {
				$.ajax({
					url: `/book/${bookToDelete}`,
					type: 'DELETE',
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					success: function (response) {
						if (response.success) {
							$('#deleteConfirmModal').modal('hide');
							window.location.href = '/my-books';
						} else {
							$("#alertModalContent").html(response.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
					},
					error: function () {
						$("#alertModalContent").html('{{__('default.An error occurred while deleting the book.')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				});
			}
		});
		
		$('.update-chapter-btn').on('click', function () {
			var chapterFilename = $(this).data('chapter-filename');
			var chapterCard = $(this).closest('.card');
			
			var chapterData = {
				chapter_filename: chapterFilename,
				name: chapterCard.find('.chapterName').val(),
				order: chapterCard.find('.chapterOrder').val(),
				short_description: chapterCard.find('.chapterShortDescription').val(),
				events: chapterCard.find('.chapterEvents').val(),
				people: chapterCard.find('.chapterPeople').val(),
				places: chapterCard.find('.chapterPlaces').val(),
				from_previous_chapter: chapterCard.find('.chapterFromPreviousChapter').val(),
				to_next_chapter: chapterCard.find('.chapterToNextChapter').val()
			};
			saveChapter(chapterData);
		});
		
		$('#generateAllBeatsBtn').on('click', function (e) {
			e.preventDefault();
			generateAllBeats(parseInt($('#beatsPerChapter').val()), $("#writingStyle").val(), $("#narrativeStyle").val());
		});
		
		$('#rewriteChapterModal').on('shown.bs.modal', function () {
			$('#rewriteUserPrompt').focus();
		});
		
		$('#beatsPerChapter').on('change', function () {
			localStorage.setItem('beats-per-chapter', $(this).val());
			beatsPerChapter = $(this).val();
		});
		
		// Open the edit book details modal
		$('#editBookDetailsBtn').on('click', function () {
			$('#editBlurb').val(bookData.blurb);
			$('#editBackCoverText').val(bookData.back_cover_text);
			$('#editCharacterProfiles').val(bookData.character_profiles);
			$('#editAuthorName').val(bookData.author_name);
			$('#editPublisherName').val(bookData.publisher_name);
			$('#editBookDetailsModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		});
		
		
		$(".alert-modal-close-button").on('click', function () {
			if (reload_window) {
				location.reload();
			}
		});
		
		// Save book details
		$('#saveBookDetailsBtn').on('click', function () {
			const updatedBookData = {
				blurb: $('#editBlurb').val(),
				back_cover_text: $('#editBackCoverText').val(),
				character_profiles: $('#editCharacterProfiles').val(),
				author_name: $('#editAuthorName').val(),
				publisher_name: $('#editPublisherName').val()
			};
			
			$.ajax({
				url: `/book/${bookSlug}/details`,
				type: 'POST',
				data: updatedBookData,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function (response) {
					if (response.success) {
						// Update the bookData object
						Object.assign(bookData, updatedBookData);
						
						// Update the displayed information
						$('#bookBlurb').text(bookData.blurb);
						$('#backCoverText').html(bookData.back_cover_text.replace(/\n/g, '<br>'));
						$('#bookCharacters').html('<em>{{__("default.Character Profiles:")}}</em><br>' + bookData.character_profiles.replace(/\n/g, '<br>'));
						
						reload_window = true;
						$('#editBookDetailsModal').modal('hide');
						$("#alertModalContent").html('{{__("default.Book details updated successfully!")}}');
						$("#alertModal").modal('show');
					} else {
						$("#alertModalContent").html('{{__("default.Failed to update book details:")}}' + response.message);
						$("#alertModal").modal('show');
					}
				},
				error: function () {
					$("#alertModalContent").html('{{__("default.An error occurred while updating book details.")}}');
					$("#alertModal").modal('show');
				}
			});
		});
		
		
		// Open LLM Prompt Modal
		$('#openLlmPromptModalBtn').on('click', function () {
			$('#llmPromptModal').modal({backdrop: 'static', keyboard: true}).modal('show');
		});
		
		// Chat with AI
		$('#sendPromptBtn').on('click', function () {
			const userPrompt = $('#userPrompt').val();
			const llm = savedLlm; // Assuming you have a savedLlm variable
			
			// Disable buttons and show loading state
			$('#sendPromptBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...');
			$('#llmResponse').val('Processing...');
			
			$.ajax({
				url: '/send-llm-prompt/' + bookSlug,
				method: 'POST',
				data: {
					user_prompt: userPrompt,
					llm: llm
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						$('#llmResponse').val(response.result);
					} else {
						$('#llmResponse').val('Error: ' + response.message);
					}
				},
				error: function (xhr, status, error) {
					$('#llmResponse').val('An error occurred while processing the request.');
				},
				complete: function () {
					// Re-enable button and restore original text
					$('#sendPromptBtn').prop('disabled', false).text('Send Prompt');
				}
			});
		});
		
		
	});


</script>
</body>
</html>
