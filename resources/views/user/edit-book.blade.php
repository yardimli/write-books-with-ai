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
	<link rel="stylesheet" href="/css/simplemde-theme-bootstrap-dark.min.css"/>
	
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
			<a class="btn btn-sm btn-primary" href="{{route('book-details',$book_slug)}}"><i
					class="bi bi-book"></i> {{__('default.Back to Book Page')}}</a>
			<button class="btn btn-sm btn-secondary mb-1 mt-1" id="aiSettingsBtn">
				<i class="bi bi-gear"></i> {{__('default.AI Settings')}}
			</button>
			<button class="btn btn-sm btn-info mb-1 mt-1" id="editBookDetailsBtn">
				<i class="bi bi-info-circle"></i> {{__('default.Edit Book Details')}}
			</button>
			<button class="btn btn-sm btn-danger mb-1 mt-1" id="generateAllBeatsBtn"
			        title="{{__('default.Write All Beats')}}"><i
					class="bi bi-lightning-charge"></i> {{__('default.Write All Beats')}}
			</button>
			
			<a href="{{route('book-codex',[$book_slug])}}" class="btn btn-sm btn-secondary mb-1 mt-1" id="openCodexBtn">
				<i class="bi bi-book"></i> {{__('default.Open Codex')}}
			</a>
			
			@if (Auth::user())
				@if (Auth::user()->email === $book['owner'] || Auth::user()->name === $book['owner'] || Auth::user()->isAdmin())
					<button class="btn btn-sm btn-primary mb-1 mt-1" id="openLlmPromptModalBtn">
						<i class="bi bi-chat-dots"></i> {{__('default.Chat with AI')}}
					</button>
				@endif
			@endif
		</div>
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<h3 style="margin:10px;" class="text-center" id="bookTitle">{{$book['title']}}</h3>
			</div>
			<div class="card-body modal-content modal-content-color d-flex flex-row">
				<!-- Image Div -->
				<div class="row">
					<div class="col-12">
						<div class="mt-2 alert alert-primary d-none" id="noBeatsInfo" role="alert">
							{{__('default.No beats have been generated for this chapter. Please click the "Recreate Beats" button to generate beats. You will need to save the beats before proceeding to write the beat contents.')}}
						</div>
					</div>
				
				</div>
			
			</div>
		</div>
		
		@php
			$chapter_index = 0;
		@endphp
		
		<div class="book-chapter-board" id="bookBoard">
			@foreach ($book['acts'] as $act)
				<div class="card general-card mb-1">
					<div class="card-header modal-header modal-header-color">
						<div class="card-title">{{__('default.act_with_number', ['id' => $act['id']])}} — {{$act['title']}}</div>
					</div>
				</div>
				
				@foreach ($act['chapters'] as $chapter)
					@php
						$chapter_index++;
					@endphp
					<div class="card general-card">
						<div class="card-body modal-content modal-content-color">
							<div class="row">
								<div class="col-12">
								
        <textarea rows="10" class="form-control chapterDetailsTextarea"   data-chapter-filename="{{$chapter['chapterFilename']}}" id="chapter_edit_{{$chapter_index}}">###### {{__('default.Order')}}
{{$chapter['order']}}
###### {{__('default.Name')}}
{{$chapter['name']}}
###### {{__('default.Short Description')}}
{{$chapter['short_description']}}
###### {{__('default.Events')}}
{{$chapter['events']}}
###### {{__('default.People')}}
{{$chapter['people']}}
###### {{__('default.Places')}}
{{$chapter['places']}}
###### {{__('default.Previous Chapter')}}
{{$chapter['from_previous_chapter']}}
###### {{__('default.Next Chapter')}}
{{$chapter['to_next_chapter']}}

##### Beats
	        @php
		        $index = -1;
						if (!array_key_exists('beats', $chapter)) {
							$chapter['beats'] = [];
						}
	        @endphp
	        @foreach($chapter['beats'] as $beat)
		        @php
			        $index++;
		        @endphp

###### Beat {{$index+1}} Description
> {{$beat['description'] ?? ''}}

###### Beat {{$index+1}} Text
{{$beat['beat_text'] ?? ''}}

###### Beat {{$index+1}} Summary
> {{$beat['beat_summary'] ?? ''}}
	        @endforeach
        </textarea>
								</div>
							</div>
							<div class="row" style="margin-left: -15px; margin-right: -15px;">
								<div class="col-12 col-xl-4 col-lg-4 mb-2 mt-1">
									<button class="btn bt-lg btn-secondary w-100 update-chapter-btn"
									        data-chapter-filename="{{$chapter['chapterFilename']}}">
										{{__('default.Update Chapter')}}
									</button>
								</div>
								
							</div>
						</div>
					</div>
				@endforeach
			@endforeach
		
		</div>
		<a href="#" id="restartTour">{{ __('default.Restart Tour') }}</a>
		<br>
		<br>
		<br>
		<br>
	
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

<!-- Add this modal definition after your other modals -->
<div class="modal fade" id="aiSettingsModal" tabindex="-1" aria-labelledby="aiSettingsModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="aiSettingsModalLabel">{{__('default.AI Settings')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div class="mb-3">
					<label for="llmSelect" class="form-label">{{__('default.AI Engines:')}}
						@if (Auth::user() && Auth::user()->isAdmin())
							<label class="badge bg-danger">Admin</label>
						@endif
					</label>
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
					<div class="mt-2 d-none" id="modelInfo">
						<div class="small" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px;">
							<div id="modelDescription"></div>
							<div id="modelPricing"></div>
						</div>
					</div>
				</div>
				
				<div class="mb-3">
					<label for="writingStyle" class="form-label">{{__('default.Writing Style')}}:</label>
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
				
				<div class="mb-3">
					<label for="narrativeStyle" class="form-label">{{__('default.Narrative Style')}}:</label>
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
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-primary" id="saveAiSettingsBtn">{{__('default.Save Settings')}}</button>
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

<!-- Write/Rewrite Beat Description/Text Modal -->
<div class="modal fade" id="writeBeatModal" tabindex="-1" aria-labelledby="writeBeatModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="writeBeatModalLabel">{{__('default.Write Beat')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<div id="writeBeatHelp" class="alert alert-info mb-3">
					<p>{{__('default.The User Prompt you see here has been generated from the book details as well as current chapter and previous beats or chapters if they exist.')}}</p>
					<p>{{__('default.It also contains the beat description that the AI will write out.')}}</p>
					<p>{{__('You can modify the prompt to add specific details, change the tone, or provide additional context for the AI to consider when writing the beat.')}}</p>
					<p><a href="#" id="hideWriteBeatHelp">{{__('default.Don\'t show again')}}</a></p>
				</div>
				<div class="mb-3">
					<label for="writeUserPrompt" class="form-label">{{__('default.User Prompt')}}</label>
					<textarea class="form-control" id="writeUserPrompt" rows="10"></textarea>
				</div>
				<div class="mb-3">
					<h6>{{__('default.Output')}}:</h6>
					<textarea class="form-control" id="writeResult" rows="10"></textarea>
				</div>
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-primary"
				        id="sendWritePromptBtn">{{__('default.Write Beat')}}</button>
				<button type="button" class="btn btn-success" id="acceptWriteBtn"
				        style="display: none;">{{__('default.Accept Output')}}</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Close')}}</button>
			</div>
		</div>
	</div>
</div>

<div id="fullScreenOverlay" class="d-none">
	<div class="overlay-content">
		<div class="spinner-border text-light" role="status">
			<span class="visually-hidden">{{__('Loading...')}}</span>
		</div>
		<p class="mt-3 text-light">{{__('default.Processing your request. This may take a few minutes...')}}</p>
	</div>
</div>


<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="/js/jquery-3.7.0.min.js"></script>
<script src="/js/bootstrap.bundle.js"></script>
<script src="/js/moment.min.js"></script>

<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>
<script src="/js/intro.min.js"></script>

<script src="/js/simplemde.min.js"></script>


<script>
	
	let reload_window = false;
	let savedLlm = localStorage.getItem('edit-book-llm') || 'anthropic/claude-3-haiku:beta';
	
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
	
	function generateAllBeats(writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
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
		generateSingleChapterBeats(chapters, writingStyle, narrativeStyle, 0);
		
	}
	
	function generateSingleChapterBeats(chapters, writingStyle, narrativeStyle, chapter_index = 0) {
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
				generateSingleChapterBeats(chapters, writingStyle, narrativeStyle, chapter_index);
			}
		} else {
			
			$.ajax({
				url: `/book/write-beats/${bookSlug}/${chapter.chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
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
							generateSingleChapterBeats(chapters, writingStyle, narrativeStyle, chapter_index);
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
	
	
	function recreateBeats(selectedChapter, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
		$('#fullScreenOverlay').removeClass('d-none');
		$("#recreateBeats").prop('disabled', true);
		
		// Clear existing beats
		$('#beatsList').empty();
		
		// Now proceed with creating beats
		$.ajax({
			url: `/book/write-beats/{{$book_slug}}/${selectedChapter}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				writing_style: writingStyle,
				narrative_style: narrativeStyle,
				save_results: false,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				$('#fullScreenOverlay').addClass('d-none');
				if (response.success) {
					response.beats.forEach((beat, beatIndex) => {
						let chapterIndex = selectedChapterIndex;
						
						const beatHtml = `
        <div class="mb-3 beat-outer-container" data-chapter-index="${chapterIndex}"
             data-chapter-filename="${selectedChapter}"
             data-beat-index="${beatIndex}">
            <h6>Beat ${beatIndex + 1}</h6>
            <div id="beatDescriptionContainer_${chapterIndex}_${beatIndex}" class="mt-3 beat-description-container">
                <label for="beatDescription_${chapterIndex}_${beatIndex}"
                       class="form-label">{{__('default.Beat Description')}}</label>
                <textarea id="beatDescription_${chapterIndex}_${beatIndex}"
                          class="form-control beat-description-textarea"
                          rows="3">${beat.description}</textarea>
            </div>
        </div>
    `;
						$('#beatsList').append(beatHtml);
						
					});
					
					$("#alertModalContent").html("{{__('default.All chapter Beat Descriptions generated successfully.')}}<br>{{__('default.Please review the beats and click "Save Beats" to save the changes.')}}<br>{{__('default.You will need to save the beats before proceeding to write the beat contents.')}}");
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					$('#recreateBeats').prop('disabled', false);
					
				} else {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html("{{__('default.Failed to create beats: ')}}" + JSON.stringify(response.message));
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					
					$("#recreateBeats").prop('disabled', false);
				}
			},
			error: function () {
				$('#fullScreenOverlay').addClass('d-none');
				$("#alertModalContent").html("{{__('default.An error occurred while creating beats.')}}");
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			}
		});
	}
	
	//------------------------------------------------------------
	function writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, editor, currentLine) {
		return new Promise((resolve, reject) => {
			$('#fullScreenOverlay').removeClass('d-none');

			
			$.ajax({
				url: `/book/write-beat-summary/{{$book_slug}}/${chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					beat_index: beatIndex,
					current_beat_description: beatDescription,
					current_beat_text: beatText,
					save_results: false,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					if (response.success) {
						
						let doc = editor.getDoc();
						
						// Find the next section
						let nextSectionLine = currentLine + 1;
						while (nextSectionLine < doc.lineCount()) {
							let nextLine = doc.getLine(nextSectionLine);
							if (nextLine.startsWith('######')) {
								break;
							}
							nextSectionLine++;
						}
						
						// Insert the new content
						doc.replaceRange(
							'\n> ' + response.prompt + '\n',
							{line: currentLine + 1, ch: 0},
							{line: nextSectionLine - 1, ch: 0}
						);
						
						resolve(response.prompt);
					} else {
						reject("{{__('default.Failed to write summary: ')}}" + response.message);
					}
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					reject("{{__('default.Failed to write beat summary.')}}<br>");
				}
			});
		});
	}
	
	function writeBeat(chapterFilename, writeMode, beatIndex, chapterIndex, textInput, editor, currentLine) {
		const modal = $('#writeBeatModal');
		$('#writeResult').val('');
		
		if (writeMode === 'write_beat_description') {
			$('#fullScreenOverlay').removeClass('d-none');
			$.ajax({
				url: `/book/write-beat-description/{{$book_slug}}/${chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					create_prompt: true,
					writing_style: $('#writingStyle').val(),
					narrative_style: $('#narrativeStyle').val(),
					beat_index: beatIndex,
					current_beat: textInput,
					save_results: false,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					$('#writeUserPrompt').val(response.prompt);
					// Show the modal
					modal.modal({backdrop: 'static', keyboard: true}).modal('show');
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
				}
			});
		}
		
		if (writeMode === 'write_beat_text') {
			$('#fullScreenOverlay').removeClass('d-none');
			$.ajax({
				url: `/book/write-beat-text/{{$book_slug}}/${chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					create_prompt: true,
					writing_style: $('#writingStyle').val(),
					narrative_style: $('#narrativeStyle').val(),
					beat_index: beatIndex,
					current_beat: textInput,
					save_results: false,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					$('#writeUserPrompt').val(response.prompt);
					// Show the modal
					modal.modal({backdrop: 'static', keyboard: true}).modal('show');
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
				}
			});
		}
		
		// Handle the rewrite button click
		$('#sendWritePromptBtn').off('click').on('click', function () {
			const userPrompt = $('#writeUserPrompt').val();
			$('#sendWritePromptBtn').prop('disabled', true).text('{{__('default.Rewriting...')}}');
			
			if (writeMode === 'write_beat_description') {
				$('#fullScreenOverlay').removeClass('d-none');
				$.ajax({
					url: `/book/write-beat-description/{{$book_slug}}/${chapterFilename}`,
					method: 'POST',
					data: {
						llm: savedLlm,
						create_prompt: false,
						beat_prompt: userPrompt,
						writing_style: $('#writingStyle').val(),
						narrative_style: $('#narrativeStyle').val(),
						beat_index: beatIndex,
						current_beat: textInput,
						save_results: false,
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (response) {
						$('#fullScreenOverlay').addClass('d-none');
						
						if (response.success) {
							// Display the rewritten chapter in the modal
							$('#writeResult').val(response.prompt);
							$('#acceptWriteBtn').show();
						} else {
							$("#alertModalContent").html('{{__('default.Failed to write beat:')}}' + response.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
						$('#sendWritePromptBtn').prop('disabled', false).text('{{__('default.Write Beat')}}');
					},
					error: function () {
						$("#alertModalContent").html('{{__('default.Error write beat')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						$('#sendWritePromptBtn').prop('disabled', false).text('{{__('default.Write Beat')}}');
						
						$('#fullScreenOverlay').addClass('d-none');
					}
				});
			}
			
			if (writeMode === 'write_beat_text') {
				$('#fullScreenOverlay').removeClass('d-none');
				$.ajax({
					url: `/book/write-beat-text/{{$book_slug}}/${chapterFilename}`,
					method: 'POST',
					data: {
						llm: savedLlm,
						create_prompt: false,
						beat_prompt: userPrompt,
						writing_style: $('#writingStyle').val(),
						narrative_style: $('#narrativeStyle').val(),
						beat_index: beatIndex,
						current_beat: textInput,
						save_results: false,
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (response) {
						$('#fullScreenOverlay').addClass('d-none');
						
						if (response.success) {
							// Display the rewritten chapter in the modal
							$('#writeResult').val(response.prompt);
							$('#acceptWriteBtn').show();
						} else {
							$("#alertModalContent").html('{{__('default.Failed to write beat:')}}' + response.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
						$('#sendWritePromptBtn').prop('disabled', false).text('{{__('default.Write Beat')}}');
					},
					error: function () {
						$("#alertModalContent").html('{{__('default.Error write beat')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						$('#sendWritePromptBtn').prop('disabled', false).text('{{__('default.Write Beat')}}');
						
						$('#fullScreenOverlay').addClass('d-none');
					}
				});
			}
			
		});
		
		$('#acceptWriteBtn').off('click').on('click', function () {
			
			let doc = editor.getDoc();
			
			// Find the next section
			let nextSectionLine = currentLine + 1;
			while (nextSectionLine < doc.lineCount()) {
				let nextLine = doc.getLine(nextSectionLine);
				if (nextLine.startsWith('######')) {
					break;
				}
				nextSectionLine++;
			}
			
			let start_character = '';
			if (writeMode === 'write_beat_description') {
				start_character = '> ';
			}
			// Insert the new content
			doc.replaceRange(
				'\n' + start_character + $('#writeResult').val() + '\n',
				{line: currentLine + 1, ch: 0},
				{line: nextSectionLine - 1, ch: 0}
			);
			
			$('#writeBeatModal').modal('hide');
		});
		
	}
	
	
	//------------------------------------------------------------
	
	function parseChapterDetails(text) {
		const fieldMappings = {
			'{{__('default.Order')}}': 'order',
			'{{__('default.Name')}}': 'name',
			'{{__('default.Short Description')}}': 'short_description',
			'{{__('default.Events')}}': 'events',
			'{{__('default.People')}}': 'people',
			'{{__('default.Places')}}': 'places',
			'{{__('default.Previous Chapter')}}': 'from_previous_chapter',
			'{{__('default.Next Chapter')}}': 'to_next_chapter'
		};
		
		const requiredFields = Object.keys(fieldMappings);
		
		let parsedData = {};
		let missingFields = [];
		let beats = [];
		
		//remove the "##### Beats" line
		text = text.replace('##### Beats', '');
		
		// Split the text into sections based on ###### fieldname
		const sections = text.split('###### ').filter(Boolean);
		
		let currentBeat = null;
		sections.forEach(section => {
			const lines = section.trim().split('\n');
			let fieldName = lines[0].trim();
			const fieldContent = lines.slice(1).join('\n').trim();
			
			// Check if this is a beat section
			const beatMatch = fieldName.match(/^Beat (\d+) (Description|Text|Summary)$/);
			if (beatMatch) {
				const beatNumber = parseInt(beatMatch[1]) - 1;
				const beatType = beatMatch[2].toLowerCase();
				
				// Initialize beat object if it doesn't exist
				if (!beats[beatNumber]) {
					beats[beatNumber] = {
						description: '',
						beat_text: '',
						beat_summary: '',
						lastUpdated: moment().format()
					};
				}
				
				// Remove '> ' prefix if it exists
				let cleanContent = fieldContent.replace(/^> /, '').trim();
				cleanContent = fieldContent.replace(/^>/, '').trim();
				
				// Map the content to the appropriate beat property
				if (beatType === 'description') {
					beats[beatNumber].description = cleanContent;
				} else if (beatType === 'text') {
					beats[beatNumber].beat_text = cleanContent;
				} else if (beatType === 'summary') {
					beats[beatNumber].beat_summary = cleanContent;
				}
			} else {
				parsedData[fieldName] = fieldContent;
			}
		});
		
		// Check for missing required fields
		requiredFields.forEach(field => {
			if (!parsedData.hasOwnProperty(field)) {
				missingFields.push(field);
			}
		});
		
		// Transform field names if validation passed
		if (missingFields.length === 0) {
			const transformedData = {};
			Object.entries(parsedData).forEach(([key, value]) => {
				const newKey = fieldMappings[key] || key;
				transformedData[newKey] = value;
			});
			parsedData = transformedData;
		}
		
		// Add beats to parsed data
		parsedData.beats = beats;
		
		return {
			isValid: missingFields.length === 0,
			missingFields: missingFields,
			data: parsedData
		};
	}
	
	let createCoverFileName = '';
	
	$(document).ready(function () {
		
		@php
			$chapter_index = 0;
		@endphp
		@foreach ($book['acts'] as $act)
		@foreach ($act['chapters'] as $chapter)
		@php
			$chapter_index++;
		@endphp
		let simplemde_{{$chapter_index}} = new SimpleMDE({
			element: document.getElementById('chapter_edit_{{$chapter_index}}'),
			forceSync: true,
			lineWrapping: true,
			toolbar: ["bold", "italic", "heading", "quote", "|", "preview", "fullscreen", "|", "guide"],
			
		});
		
		simplemde_{{$chapter_index}}.codemirror.setSize(null,'80vh');
		
		simplemde_{{$chapter_index}}.codemirror.on('cursorActivity', function () {
			var cursor = simplemde_{{$chapter_index}}.codemirror.getCursor();
			var line_number = cursor.line;
			var current_line = simplemde_{{$chapter_index}}.codemirror.getLine(line_number);
			
			$('.beat-action-button').remove();
			
			if (current_line.trim() === '###### Name') {
				var cursorCoords = simplemde_{{$chapter_index}}.codemirror.cursorCoords(true);
				// Create Add Beat button
				var $button = $('<button>')
					.addClass('btn btn-sm btn-success beat-action-button')
					.text('Rewrite Chapter')
					.css({
						position: 'absolute',
						left: (cursorCoords.left + 50) + 'px',
						top: cursorCoords.top + 'px',
						zIndex: 1000
					})
					.on('click', function (e) {
						
						let chapterIndex = simplemde_{{$chapter_index}}.element.id.replace('chapter_edit_', '');
						let chapterFilename = $(simplemde_{{$chapter_index}}.element)
							.closest('textarea').data('chapter-filename');
						
						rewriteChapter(chapterFilename);
						
						e.preventDefault();
						e.stopPropagation();
					});
				
				// Add button to the document
				$('body').append($button);
			}
			
				// Check if cursor is on ##### Beats line
			if (current_line.trim() === '##### Beats') {
				var cursorCoords = simplemde_{{$chapter_index}}.codemirror.cursorCoords(true);
				
				// Count existing beats
				let doc = simplemde_{{$chapter_index}}.codemirror.getDoc();
				let totalLines = doc.lineCount();
				let beatCount = 0;
				for (let i = 0; i < totalLines; i++) {
					let lineContent = doc.getLine(i);
					if (lineContent.match(/^###### Beat \d+ Description/)) {
						beatCount++;
					}
				}
				
				// Create Add Beat button
				var $button = $('<button>')
					.addClass('btn btn-sm btn-success beat-action-button')
					.text('Add Beat')
					.css({
						position: 'absolute',
						left: (cursorCoords.left + 50) + 'px',
						top: cursorCoords.top + 'px',
						zIndex: 1000
					})
					.on('click', function (e) {
						let newBeatNumber = beatCount + 1;
						let newBeatContent = `\n###### Beat ${newBeatNumber} Description\n\n###### Beat ${newBeatNumber} Text\n\n###### Beat ${newBeatNumber} Summary\n`;
						
						// Get current content and append new beat
						let currentContent = simplemde_{{$chapter_index}}.codemirror.getValue();
						simplemde_{{$chapter_index}}.codemirror.setValue(currentContent + newBeatContent);
						
						e.preventDefault();
						e.stopPropagation();
					});
				
				// Add button to the document
				$('body').append($button);
			}
			
			let beatMatch = current_line.match(/^###### Beat (\d+) (Description|Text|Summary)/i);
			if (beatMatch) {
				let beatNumber = beatMatch[1];
				let beatLabel = beatMatch[2];
				let doc = simplemde_{{$chapter_index}}.codemirror.getDoc();
				let totalLines = doc.lineCount();
				
				// Initialize objects to store all beat sections
				let beatContent = {
					Description: '',
					Text: '',
					Summary: ''
				};
				
				// Function to extract content until next section or beat
				function extractContent(startLine) {
					let content = '';
					let currentLine = startLine + 1;
					while (currentLine < totalLines) {
						let lineContent = doc.getLine(currentLine);
						if (lineContent.startsWith('#')) {
							break;
						}
						content += lineContent + '\n';
						currentLine++;
					}
					return content.trim();
				}
				
				// Search for all sections of this beat
				let star_line_number = 0;
				
				for (let i = star_line_number; i < totalLines; i++) {
					let lineContent = doc.getLine(i);
					
					// Check for each section type
					let sectionMatch = lineContent.match(new RegExp(`^###### Beat ${beatNumber} (Description|Text|Summary)`, 'i'));
					
					if (sectionMatch) {
						let sectionType = sectionMatch[1];
						beatContent[sectionType] = extractContent(i);
					}
				}
				
				// Get cursor coordinates for button placement
				var cursorCoords = simplemde_{{$chapter_index}}.codemirror.cursorCoords(true);
				
				// Create and position the button
				var $button = $('<button>')
					.addClass('btn btn-sm btn-primary beat-action-button')
					.text('Edit Beat ' + beatNumber + ' ' + beatLabel)
					.css({
						position: 'absolute',
						left: (cursorCoords.left + 50) + 'px',
						top: cursorCoords.top + 'px',
						zIndex: 1000
					})
					.on('click', function (e) {
						let beatIndex = Number(beatNumber) - 1;
						let chapterIndex = simplemde_{{$chapter_index}}.element.id.replace('chapter_edit_', '');
						let chapterFilename = $(simplemde_{{$chapter_index}}.element)
							.closest('textarea').data('chapter-filename');
						
						if (beatContent.Description.startsWith('> ')) {
							beatContent.Description = beatContent.Description.replace('> ', '');
						}
						if (beatContent.Summary.startsWith('> ')) {
							beatContent.Summary = beatContent.Summary.replace('> ', '');
						}


						console.log('Chapter Index:', chapterIndex);
						console.log('Chapter Filename:', chapterFilename);
						console.log('Beat Number:', beatNumber);
						console.log('Beat Label:', beatLabel);
						console.log('Description:', beatContent.Description);
						console.log('Text:', beatContent.Text);
						console.log('Summary:', beatContent.Summary);
						
						
						if (beatLabel==='Text') {
							writeBeat(chapterFilename, 'write_beat_text', beatIndex, chapterIndex, beatContent.Description + "\n" + beatContent.Text, simplemde_{{$chapter_index}}.codemirror,line_number);
						}
						
						if (beatLabel==='Description') {
							writeBeat(chapterFilename, 'write_beat_description', beatIndex, chapterIndex, beatContent.Description, simplemde_{{$chapter_index}}.codemirror,line_number);
						}
						
						if (beatLabel==='Summary') {
							writeBeatSummary(beatContent.Text, beatContent.Description, beatIndex, chapterIndex, chapterFilename, simplemde_{{$chapter_index}}.codemirror,line_number);
						}
						
						e.preventDefault();
						e.stopPropagation();
					});
				
				// Add button to the document
				$('body').append($button);
			}
		});
		
		// Clean up when editor loses focus
		simplemde_{{$chapter_index}}.codemirror.on('blur', function () {
			// $('.beat-action-button').remove();
		});

		
		@endforeach
		@endforeach
		
		
		
		$('#aiSettingsBtn').on('click', function() {
			$('#aiSettingsModal').modal('show');
		});
		
		$('#saveAiSettingsBtn').on('click', function() {
			// Save settings to localStorage
			localStorage.setItem('edit-book-llm', $('#llmSelect').val());
			localStorage.setItem('writing-style', $('#writingStyle').val());
			localStorage.setItem('narrative-style', $('#narrativeStyle').val());
			
			savedLlm = $('#llmSelect').val();
			
			$('#aiSettingsModal').modal('hide');
			
			$("#alertModalContent").html('{{__("default.AI settings saved successfully!")}}');
			$("#alertModal").modal('show');
		});
		
		$('#editBookDetailsBtn').on('click', function () {
			$('#editBlurb').val(bookData.blurb);
			$('#editBackCoverText').val(bookData.back_cover_text);
			$('#editCharacterProfiles').val(bookData.character_profiles);
			$('#editAuthorName').val(bookData.author_name);
			$('#editPublisherName').val(bookData.publisher_name);
			$('#editBookDetailsModal').modal({backdrop: 'static', keyboard: true}).modal('show');
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
			
			llmSelect.on('click', function () {
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
		
		$('.update-chapter-btn').on('click', function () {
			var chapterFilename = $(this).data('chapter-filename');
			var chapterCard = $(this).closest('.card');
			var chapterText = chapterCard.find('.chapterDetailsTextarea').val();
			
			// Parse and validate the chapter details
			const parsedChapter = parseChapterDetails(chapterText);
			
			if (!parsedChapter.isValid) {
				$("#alertModalContent").html(
					'{{__("default.Missing required fields:")}} ' +
					parsedChapter.missingFields.join(', ') +
					'<br><br>{{__("default.Please ensure all fields are present with their ###### fieldname markers.")}}'
				);
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				return;
			}
			
			
			var chapterData = {
				chapter_filename: chapterFilename,
				...parsedChapter.data
			};
			console.log(chapterData);
			saveChapter(chapterData);
		});
		
		$('#generateAllBeatsBtn').on('click', function (e) {
			e.preventDefault();
			generateAllBeats($("#writingStyle").val(), $("#narrativeStyle").val());
		});
		
		$('#rewriteChapterModal').on('shown.bs.modal', function () {
			$('#rewriteUserPrompt').focus();
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
		
		//------------------- BEATS -------------------
		
		if (localStorage.getItem('hideWriteBeatHelp') === 'true') {
			$('#writeBeatHelp').hide();
		}
		
		$('#hideWriteBeatHelp').on('click', function (e) {
			e.preventDefault();
			localStorage.setItem('hideWriteBeatHelp', 'true');
			$('#writeBeatHelp').hide();
		});
		
		$('#writeBeatModal').on('show.bs.modal', function () {
			if (localStorage.getItem('hideWriteBeatHelp') !== 'true') {
				$('#writeBeatHelp').show();
			}
		});
		
		$("#recreateBeats").on('click', function (e) {
			e.preventDefault();
			recreateBeats(selectedChapter + '.json', $('#writingStyle').val(), $('#narrativeStyle').val());
		});
		
		
	});


</script>
@include('user.book-chat')
</body>
</html>
