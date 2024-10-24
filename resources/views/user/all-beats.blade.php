<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>{{__('default.Write Books With AI - Book Beats')}}</title>
	
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
	<link href="/css/custom.css" rel="stylesheet"> <!-- If you have custom CSS -->
	
	<link rel="stylesheet" href="/css/introjs.css">
	<link rel="stylesheet" href="/css/introjs-dark.css" disabled>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<div class="mb-1 mt-1 w-100" style="text-align: right;">
			<button class="btn btn-sm btn-info mb-1 mt-1 me-2" id="showBookDetailsBtn">
				<i class="bi bi-info-circle"></i> {{__('default.Book Details')}}
			</button>
			<a href="{{route('edit-book', $book_slug)}}" class="btn btn-sm btn-primary mb-1 mt-1"
			   title="{{__('default.Back to Chapters')}}"><i
					class="bi bi-book"></i> {{__('default.Back to Chapters')}}</a>
		</div>
		
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<h3 style="margin:10px;" class="text-center" id="bookTitle">{{$book['title']}}</h3>
			</div>
			<div class="card-body modal-content modal-content-color">
				<!-- Image Div -->
				<div class="row">
					<!-- Text Blocks Div -->
					
					
					<div class="col-12 col-xl-6">
						<span for="llmSelect" class="form-label">{{__('default.AI Engines:')}}
							@if (Auth::user() && Auth::user()->isAdmin())
								<label class="badge bg-danger">Admin</label>
							@endif
						</span>
						<select id="llmSelect" class="form-select mx-auto mb-1">
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
						
						<span class="form-label">{{__('default.Number of beats per chapter:')}}</span>
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
				</div>
				
				<div class="mt-1 small d-none" id="modelInfo" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px;">
					<div id="modelDescription"></div>
					<div id="modelPricing"></div>
				</div>
				
				<div class="row">
					<div class="col-12 col-lg-6">
						<button type="button" class="btn btn-success mt-2 mb-3" id="recreateBeats"><i
								class="bi bi-pencil"></i> {{__('default.Recreate Beats')}}</button>
						<a href="{{route('book-codex',[$book_slug])}}" class="btn btn-primary mb-3 mt-2"
						   id="openCodexBtn">
							<i class="bi bi-book"></i> {{__('default.Open Codex')}}
						</a>
					</div>
					
					<div class="col-12">
						<div class="mt-2 alert alert-info d-none" id="noBeatsInfo" role="alert">
							{{__('default.No beats have been generated for this chapter. Please click the "Recreate Beats" button to generate beats. You will need to save the beats before proceeding to write the beat contents.')}}
						</div>
						<a href="#" id="restartTour">{{ __('default.Restart Tour') }}</a>
					
					</div>
				
				</div>
			</div>
		</div>
		
		@php
			//dd($book);
			$chapter_index = 0;
			if ($selected_chapter_index !== 0) {
				$chapter_index = $selected_chapter_index -1;
			}
		@endphp
		
		@foreach($book['acts'] as $act)
			
			@foreach($act['chapters'] as $chapter)
				@php
					$chapter_index++;
				@endphp
				<div class="card general-card">
					<div class="card-body modal-content-color">
						<h5>{{__('default.Chapter')}} #{{$chapter_index}} - {{$chapter['name'] ?? 'noname'}}</h5>
						<em>{{__('default.Description')}}</em>: <span
							id="chapterDescription">{{$chapter['short_description'] ?? 'no description'}}</span><br>
						<em>{{__('default.Events')}}</em>: <span
							id="chapterEvents">{{$chapter['events'] ?? 'no events'}}</span><br>
						<em>{{__('default.People')}}</em>: <span
							id="chapterPeople">{{$chapter['people'] ?? 'no people'}}</span><br>
						<em>{{__('default.Places')}}</em>: <span
							id="chapterPlaces">{{$chapter['places'] ?? 'no places'}}</span><br>
						<em>{{__('default.Previous Chapter')}}</em>: <span
							id="chapterFromPreviousChapter">{{$chapter['from_previous_chapter']}}</span><br>
						<em>{{__('default.Next Chapter')}}</em>: <span
							id="chapterToNextChapter">{{$chapter['to_next_chapter']}}</span><br>
					</div>
				</div>
				
				<div class="card general-card">
					<div class="card-body modal-content-color">
						<div id="beatsList">
							@php
								$index = -1;
							@endphp
							@foreach($chapter['beats'] as $beat)
								@php
									$index++;
								@endphp
								
								<div class="mb-4 beat-outer-container" data-chapter-index="{{$chapter_index}}"
								     data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}">
									
									<div class="dropdown d-inline-block" style="vertical-align: top;">
										<button class="btn btn-info dropdown-toggle beat-dropdown-button" type="button"
										        data-bs-toggle="dropdown"
										        aria-expanded="false">
											{{__('default.Beat')}} {{$index+1}}
										</button>
										<ul class="dropdown-menu">
											@if($index == 0)
												<li>
													<button class="addEmptyBeatBtn dropdown-item" data-position="before"
													        data-chapter-index="{{$chapter_index}}"
													        data-chapter-filename="{{$chapter['chapterFilename']}}"
													        data-beat-index="{{$index}}">{{__('default.Add Empty Beat Before')}}</button>
												</li>
											@endif
											<li>
												<button class="addEmptyBeatBtn dropdown-item" data-position="after"
												        data-chapter-index="{{$chapter_index}}"
												        data-chapter-filename="{{$chapter['chapterFilename']}}"
												        data-beat-index="{{$index}}">{{__('default.Add Empty Beat After')}}</button>
											</li>
											@if ( ($beat['description'] ?? '') !== '')
												<li>
													<button class="writeBeatDescriptionBtn dropdown-item"
													        id="writeBeatDescriptionBtn_{{$chapter_index}}_{{$index}}"
													        data-chapter-index="{{$chapter_index}}"
													        data-chapter-filename="{{$chapter['chapterFilename']}}"
													        data-beat-index="{{$index}}">{{__('default.Rewrite Beat Description')}}</button>
												</li>
											@endif
											
											<li>
												<button class="toggle-beat-description dropdown-item"
												        data-chapter-index="{{$chapter_index}}" data-beat-index="{{$index}}">
													{{__('default.Description')}}
												</button>
											</li>
											
											<li>
												<button class="toggle-beat-text dropdown-item"
												        data-chapter-index="{{$chapter_index}}" data-beat-index="{{$index}}">
													{{__('default.Text')}}
												</button>
											</li>
											
											<li>
												<button class="toggle-beat-summary dropdown-item"
												        data-chapter-index="{{$chapter_index}}" data-beat-index="{{$index}}">
													{{__('default.Summary')}}
												</button>
											</li>
										</ul>
									</div>
									
									
									@php $hideDescription = 'd-none'; $showTextFlag = false; @endphp
									@if ( ($beat['description'] ?? '') === '')
										@php $hideDescription = ''; $showTextFlag = true; @endphp
									@endif
									
									<div id="beatDescriptionContainer_{{$chapter_index}}_{{$index}}"
									     class="{{$hideDescription}} mt-3 beat-description-container">
										<label for="beatDescription_{{$chapter_index}}_{{$index}}"
										       class="form-label">{{__('default.Beat Description')}}</label>
										<textarea id="beatDescription_{{$chapter_index}}_{{$index}}"
										          class="form-control beat-description-textarea"
										          rows="3">{{$beat['description'] ?? ''}}</textarea>
									</div>
									
									@php $hideText = 'd-none'; @endphp
									@if ( ($beat['description'] ?? '') !== '' || ($beat['beat_text'] ?? '') !== '')
										@php $hideText = ''; @endphp
									@endif
									
									<div id="beatTextArea_{{$chapter_index}}_{{$index}}"
									     class="mt-3 {{$hideText}} beat-text-area-container">
										<div class="small text-info mb-2"
										     id="beatDescriptionLabel_{{$chapter_index}}_{{$index}}">{{__('default.Beat Description')}}:
											{{$beat['description'] ?? ''}}</div>
										<label for="beatText_{{$chapter_index}}_{{$index}}"
										       class="form-label">{{__('default.Beat Text')}}</label>
										<textarea id="beatText_{{$chapter_index}}_{{$index}}" class="form-control beat-text-textarea"
										          rows="10">{{$beat['beat_text'] ?? ''}}</textarea>
									</div>
									
									<div id="beatSummaryArea_{{$chapter_index}}_{{$index}}"
									     class="mt-2  d-none beat-summary-area-container">
										<label for="beatSummary_{{$chapter_index}}_{{$index}}"
										       class="form-label mt-2">{{__('default.Beat Summary')}}</label>
										<textarea id="beatSummary_{{$chapter_index}}_{{$index}}"
										          class="form-control beat-summary-textarea"
										          rows="3">{{$beat['beat_summary'] ?? ''}}</textarea>
									</div>
									
									
									<div class="beat-write-buttons">
										@if ( ($beat['description'] ?? '') !== '')
											<button id="writeBeatTextBtn_{{$chapter_index}}_{{$index}}"
											        data-chapter-index="{{$chapter_index}}"
											        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
											        class="writeBeatTextBtn btn btn-primary mt-3 me-2">{{__('default.Write Beat Text')}}</button>
										@else
											<button id="writeBeatDescriptionBtn_{{$chapter_index}}_{{$index}}"
											        data-chapter-index="{{$chapter_index}}"
											        data-chapter-filename="{{$chapter['chapterFilename']}}" data-beat-index="{{$index}}"
											        class="writeBeatDescriptionBtn btn btn-primary mt-3 me-2">{{__('default.Write Beat Description')}}</button>
										@endif
										
										
										<button class="saveBeatBtn btn btn-success mt-3 me-2" data-chapter-index="{{$chapter_index}}"
										        data-chapter-filename="{{$chapter['chapterFilename']}}"
										        data-beat-index="{{$index}}">{{__('default.Save')}}</button>
										
										<div class="me-auto small text-info" style="max-height: 80px; overflow: auto;"
										     id="beatBlockResults_{{$chapter_index}}_{{$index}}"></div>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			@endforeach
		@endforeach
		
		<button type="button" class="btn btn-primary mt-2 mb-5 w-100"
		        id="saveBeatsBtn"><i
				class="bi bi-file-earmark-text-fill"></i> {{__('default.Save Beats')}}</button>
	
	</div>
</main>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="alertModalLabel">{{__('default.Alert')}}</h5>
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

<!-- Beat Summary Edit Modal -->
<div class="modal fade" id="beatSummaryEditModal" tabindex="-1" aria-labelledby="beatSummaryEditModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="beatSummaryEditModalLabel">{{__('default.Edit Beat Summary')}}</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body modal-body-color">
				<textarea id="editableBeatSummary" class="form-control" rows="5"></textarea>
			</div>
			<div class="modal-footer modal-footer-color justify-content-start">
				<button type="button" class="btn btn-primary" id="saveBeatSummaryBtn">{{__('default.Save Summary')}}</button>
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('default.Cancel')}}</button>
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

<script>
	let selectedChapter = "{{$selected_chapter}}";
	let selectedChapterIndex = "{{$selected_chapter_index}}";
	let savedLlm = localStorage.getItem('beats-llm') || 'anthropic/claude-3-haiku:beta';
	let beatsPerChapter = localStorage.getItem('beats-per-chapter') || 3;
	$("#beatsPerChapter").val(beatsPerChapter);
	
	function recreateBeats(selectedChapter, beatsPerChapter = 3, writingStyle = 'Minimalist', narrativeStyle = 'Third Person - The narrator has a godlike perspective') {
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
				beats_per_chapter: beatsPerChapter,
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
					$("#saveBeatsBtn").show();
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
	function writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, showOverlay = true, save_results = false) {
		return new Promise((resolve, reject) => {
			if (showOverlay) {
				$('#fullScreenOverlay').removeClass('d-none');
			}
			$('#beatBlockResults_' + chapterIndex + '_' + beatIndex).prepend("{{__('default.Writing beat summary...')}}<br>");
			
			$.ajax({
				url: `/book/write-beat-summary/{{$book_slug}}/${chapterFilename}`,
				method: 'POST',
				data: {
					llm: savedLlm,
					beat_index: beatIndex,
					current_beat_description: beatDescription,
					current_beat_text: beatText,
					save_results: save_results,
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					if (response.success) {
						$('#beatSummary_' + chapterIndex + '_' + beatIndex).val(response.prompt);
						$('#beatBlockResults_' + chapterIndex + '_' + beatIndex).prepend("{{__('default.Beat summary generated successfully!')}}<br>");
						resolve(response.prompt);
					} else {
						$('#beatBlockResults_' + chapterIndex + '_' + beatIndex).prepend("{{__('default.Failed to write summary: ')}}" + response.message + "<br>");
						reject("{{__('default.Failed to write summary: ')}}" + response.message);
					}
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$('#beatBlockResults_' + chapterIndex + '_' + beatIndex).prepend("{{__('default.Failed to write beat summary.')}}");
					reject("{{__('default.Failed to write beat summary.')}}<br>");
				}
			});
		});
	}
	
	//------------------------------------------------------------
	function saveBeat(beatText, beatSummary, beatDescription, beatIndex, chapterIndex, chapterFilename) {
		$.ajax({
			url: `/book/save-single-beat/{{$book_slug}}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beat_index: beatIndex,
				beat_description: beatDescription,
				beat_text: beatText,
				beat_summary: beatSummary,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					$("#beatBlockResults_" + chapterIndex + "_" + beatIndex).prepend("{{__('default.Beat saved successfully!')}}<br>");
					if (beatText !== '') {
						writeBeatSummary(beatText, beatDescription, beatIndex, chapterIndex, chapterFilename, true, false)
							.then(summary => {
								// Show the summary edit modal
								$('#editableBeatSummary').val(summary);
								$('#beatSummaryEditModal').modal({backdrop: 'static', keyboard: true}).modal('show');
								
								// Handle saving the edited summary
								$('#saveBeatSummaryBtn').off('click').on('click', function () {
									let editedSummary = $('#editableBeatSummary').val();
									saveBeatWithSummary(beatText, editedSummary, beatDescription, beatIndex, chapterIndex, chapterFilename);
									$('#beatSummaryEditModal').modal('hide');
								});
							});
					} else {
						//reload the page
						location.reload();
					}
				} else {
					$("#beatBlockResults_" + chapterIndex + "_" + beatIndex).prepend("{{__('default.Failed to save beat: ')}}" + response.message + "<br>");
				}
			}
		});
	}
	
	function saveBeatWithSummary(beatText, beatSummary, beatDescription, beatIndex, chapterIndex, chapterFilename) {
		$.ajax({
			url: `/book/save-single-beat/{{$book_slug}}/${chapterFilename}`,
			method: 'POST',
			data: {
				llm: savedLlm,
				beat_index: beatIndex,
				beat_description: beatDescription,
				beat_text: beatText,
				beat_summary: beatSummary,
			},
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},
			dataType: 'json',
			success: function (response) {
				if (response.success) {
					$("#beatBlockResults_" + chapterIndex + "_" + beatIndex).prepend("{{__('default.Beat and summary saved successfully!')}}<br>");
					$('#beatSummary_' + chapterIndex + '_' + beatIndex).val(beatSummary);
					
					//reload the page
					location.reload();
					
				} else {
					$("#beatBlockResults_" + chapterIndex + "_" + beatIndex).prepend("{{__('default.Failed to save beat with summary: ')}}" + response.message + "<br>");
				}
			}
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
	
	function writeBeat(chapterFilename, writeMode, beatIndex, chapterIndex, textInput) {
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
			if (writeMode === 'write_beat_description') {
				$('#beatDescription_' + chapterIndex + '_' + beatIndex).val($('#writeResult').val());
				$('#writeBeatDescriptionBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
			}
			
			if (writeMode === 'write_beat_text') {
				$('#beatText_' + chapterIndex + '_' + beatIndex).val($('#writeResult').val());
				$('#writeBeatTextBtn_' + chapterIndex + '_' + beatIndex).prop('disabled', false);
			}
			
			$('#writeBeatModal').modal('hide');
		});
		
	}
	
	function isDarkMode() {
		let savedTheme = localStorage.getItem('theme') || 'light';
		if (savedTheme === 'dark') {
			return true;
		}
	}
	
	// Function to toggle Intro.js stylesheets based on theme
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
		//make the first beat text and summary and the save beats button visible
		$('.beat-description-container').first().removeClass('d-none');
		$('.beat-text-area-container').first().removeClass('d-none');
		$('.beat-summary-area-container').first().removeClass('d-none');
		$('#saveBeatsBtn').show();
		let intro = introJs().setOptions({
			steps: [
				{
					element: '#llmSelect',
					intro: 'Select an AI engine to use for writing both beat descriptions and the beat text itself.'
				},
				{
					element: '#beatsPerChapter',
					intro: 'Choose how many beats should be generated for each chapter.'
				},
				{
					element: '#writingStyle',
					intro: 'Select the writing style for your book. You can change this later.'
				},
				{
					element: '#narrativeStyle',
					intro: 'Choose the narrative style for your book. Again this can be changed later for individual beats.'
				},
				{
					element: '#recreateBeats',
					intro: 'Click this button to generate all the beat descriptions for this chapter.'
				},
				{
					element: '#openCodexBtn',
					intro: 'Click here to open the Codex, you will be able to auto update the codex from the beats already written.'
				},
				
				{
					element: '.beat-dropdown-button',
					intro: 'From this dropdown you can "Add Empty Beat Before/After", "Rewrite Beat Description", "Edit Beat Text" and "Edit Beat Summary".'
				},
				{
					element: '.beat-description-textarea',
					intro: 'This is where you can write or rewrite the beat description. The AI engine will both write the description and also later use the description to write the Beat Text itself.'
				},
				{
					element: '.beat-text-textarea',
					intro: 'This is where the full text of the beat is written. You can edit this text as needed.'
				},
				{
					element: '.beat-summary-textarea',
					intro: 'This is where the summary of the beat is written. The beat summary is used to help the AI writing the next beat.'
				},
				{
					element: '.beat-write-buttons',
					intro: 'Click this button to initiate for the AI to write the beat description or beat text.'
				},
				{
					element: '.saveBeatBtn',
					intro: 'Click this button to save the beat description and text. When saving beat text the AI will also write the beat summary.'
				},
				{
					element: '#saveBeatsBtn',
					intro: 'Click this button to save all the beats you have written. You will need to save the beat descriptions before proceeding to write the beat contents.'
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
		
		intro.oncomplete(function () {
			localStorage.setItem('beatsIntroCompleted', 'true');
		});
		
		intro.start();
		
	}
	
	
	//------------------------------------------------------------
	$(document).ready(function () {
		toggleIntroJsStylesheet();
		
		if (!localStorage.getItem('beatsIntroCompleted')) {
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
			localStorage.removeItem('beatsIntroCompleted');
			startIntro();
		});
		
		if (localStorage.getItem('hideWriteBeatHelp') === 'true') {
			$('#writeBeatHelp').hide();
		}

		$('#hideWriteBeatHelp').on('click', function(e) {
			e.preventDefault();
			localStorage.setItem('hideWriteBeatHelp', 'true');
			$('#writeBeatHelp').hide();
		});

		$('#writeBeatModal').on('show.bs.modal', function () {
			if (localStorage.getItem('hideWriteBeatHelp') !== 'true') {
				$('#writeBeatHelp').show();
			}
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
                <strong>Pricing (million tokens):</strong> Prompt: $${promptPricePerMillion} - Completion: $${completionPricePerMillion}
            `);
			});
			
			// Trigger change to show initial description
			llmSelect.trigger('change');
		}).catch(function (error) {
			console.error('Error loading LLMs data:', error);
		});
		
		$("#llmSelect").on('change', function () {
			localStorage.setItem('beats-llm', $(this).val());
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
		
		
		if (selectedChapter !== '') {
			$("#saveBeatsBtn").hide();
			$('#recreateBeats').show();
			$('#beatsPerChapter').show();
			$('#beatsPerChapterLabel').show();
		} else {
			$("#saveBeatsBtn").hide();
			$('#recreateBeats').hide();
			$('#beatsPerChapter').hide();
			$('#beatsPerChapterLabel').hide();
		}
		
		//check if the beat-description-textarea is empty
		let emptyBeatDescriptions = true;
		$('.beat-description-textarea').each(function (index, element) {
			if ($(element).val().trim() !== '') {
				emptyBeatDescriptions = false;
			}
		});
		
		if (emptyBeatDescriptions) {
			$('#noBeatsInfo').removeClass('d-none');
		}
		
		$('.toggle-beat-description').on('click', function () {
			
			
			let chapterIndex = $(this).data('chapter-index');
			let beatIndex = $(this).data('beat-index');
			
			if (!$("#beatDescriptionContainer_" + chapterIndex + "_" + beatIndex).hasClass('d-none') &&
				$("#beatTextArea_" + chapterIndex + "_" + beatIndex).hasClass('d-none') &&
				$("#beatSummaryArea_" + chapterIndex + "_" + beatIndex).hasClass('d-none')) {
				return;
			}
			
			$('#beatDescriptionContainer_' + chapterIndex + '_' + beatIndex).toggleClass('d-none');
			if ($('#beatDescriptionContainer_' + chapterIndex + '_' + beatIndex).hasClass('d-none')) {
				$("#beatDescriptionLabel_" + chapterIndex + "_" + beatIndex).removeClass('d-none');
			} else {
				$("#beatDescriptionLabel_" + chapterIndex + "_" + beatIndex).addClass('d-none');
			}
		});
		
		// Toggle beat text visibility
		$('.toggle-beat-text').on('click', function () {
			let chapterIndex = $(this).data('chapter-index');
			let beatIndex = $(this).data('beat-index');
			
			if (!$("#beatTextArea_" + chapterIndex + "_" + beatIndex).hasClass('d-none') &&
				$("#beatDescriptionContainer_" + chapterIndex + "_" + beatIndex).hasClass('d-none') &&
				$("#beatSummaryArea_" + chapterIndex + "_" + beatIndex).hasClass('d-none')) {
				return;
			}
			
			$('#beatTextArea_' + chapterIndex + '_' + beatIndex).toggleClass('d-none');
		});
		
		// Toggle beat summary visibility
		$('.toggle-beat-summary').on('click', function () {
			let chapterIndex = $(this).data('chapter-index');
			let beatIndex = $(this).data('beat-index');
			
			if (!$("#beatSummaryArea_" + chapterIndex + "_" + beatIndex).hasClass('d-none') &&
				$("#beatDescriptionContainer_" + chapterIndex + "_" + beatIndex).hasClass('d-none') &&
				$("#beatTextArea_" + chapterIndex + "_" + beatIndex).hasClass('d-none')) {
				return;
			}
			
			$('#beatSummaryArea_' + chapterIndex + '_' + beatIndex).toggleClass('d-none');
		});
		
		$('.addEmptyBeatBtn').off('click').on('click', function () {
			let chapterIndex = $(this).data('chapter-index');
			let chapterFilename = $(this).data('chapter-filename');
			let beatIndex = $(this).data('beat-index');
			let position = $(this).data('position');
			
			let newBeat = {
				description: '',
				beat_text: '',
				beat_summary: ''
			};
			
			$.ajax({
				url: `/book/add-empty-beat/{{$book_slug}}/${chapterFilename}`,
				method: 'POST',
				data: {
					beat_index: beatIndex,
					position: position,
					new_beat: JSON.stringify(newBeat)
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				success: function (response) {
					if (response.success) {
						location.reload(); // Refresh the page
					} else {
						$("#alertModalContent").html('Failed to add empty beat: ' + response.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				error: function () {
					$("#alertModalContent").html('An error occurred while adding the empty beat.');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			});
		});
		
		$('.closeAndRefreshButton').on('click', function () {
			location.reload();
		});
		
		$('.saveBeatBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).attr('data-beat-index'));
			let chapterIndex = Number($(this).attr('data-chapter-index'));
			let chapterFilename = $(this).attr('data-chapter-filename');
			
			let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
			let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
			let beatSummary = $('#beatSummary_' + chapterIndex + '_' + beatIndex).val();
			
			saveBeat(beatText, beatSummary, beatDescription, beatIndex, chapterIndex, chapterFilename);
		});
		
		$('.writeBeatDescriptionBtn').off('click').on('click', function () {
			let chapterFilename = $(this).attr('data-chapter-filename');
			let beatIndex = Number($(this).attr('data-beat-index'));
			let chapterIndex = Number($(this).attr('data-chapter-index'));
			let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
			let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
			
			if (beatText !== '') {
				$('#beatTextArea_' + chapterIndex + '_' + beatIndex).addClass('d-none');
				$('#beatText_' + chapterIndex + '_' + beatIndex).val('');
				
				$('#beatDescriptionContainer_' + chapterIndex + '_' + beatIndex).removeClass('d-none');
				$("#writeBeatTextBtn_" + chapterIndex + "_" + beatIndex).addClass('d-none');
			}
			
			writeBeat(chapterFilename, 'write_beat_description', beatIndex, chapterIndex, beatDescription);
		});
		
		$('.writeBeatTextBtn').off('click').on('click', function () {
			let beatIndex = Number($(this).attr('data-beat-index'));
			let chapterIndex = Number($(this).attr('data-chapter-index'));
			let chapterFilename = $(this).attr('data-chapter-filename');
			
			let beatText = $('#beatText_' + chapterIndex + '_' + beatIndex).val();
			let beatDescription = $('#beatDescription_' + chapterIndex + '_' + beatIndex).val();
			
			writeBeat(chapterFilename, 'write_beat_text', beatIndex, chapterIndex, beatDescription + "\n" + beatText);
		});
		
		$('#beatsPerChapter').on('change', function () {
			localStorage.setItem('beats-per-chapter', $(this).val());
			beatsPerChapter = $(this).val();
		});
		
		$("#recreateBeats").on('click', function (e) {
			e.preventDefault();
			recreateBeats(selectedChapter + '.json', parseInt($('#beatsPerChapter').val()), $('#writingStyle').val(), $('#narrativeStyle').val());
		});
		
		$('#saveBeatsBtn').on('click', function (e) {
			e.preventDefault();
			
			let beats = [];
			
			$('#beatsList').find('.beat-outer-container').each(function (index, element) {
				let beatDescription = $(element).find('.beat-description-textarea').val();
				let beatText = '';
				let beatSummary = '';
				beats.push({description: beatDescription, beat_text: beatText, beat_summary: beatSummary});
			});
			
			$.ajax({
				url: `/book/save-beats/{{$book_slug}}/${selectedChapter}.json`,
				method: 'POST',
				data: {
					llm: savedLlm,
					beats: JSON.stringify(beats)
				},
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				dataType: 'json',
				success: function (response) {
					if (response.success) {
						
						$("#alertModalContent").html("{{__('default.Beats saved successfully!')}}");
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						setTimeout(function () {
							location.reload();
						}, 2500);
						
					} else {
						$("#alertModalContent").html("{{__('default.Failed to save beats: ')}}" + response.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				}
			});
		});
		
	});


</script>

</body>
</html>
