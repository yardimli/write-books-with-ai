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
		let bookData = @json($bookData);
		let bookSlug = "{{$bookSlug}}";
	</script>

</head>
<body>

<main class="py-1">
	
	<div class="container mt-2">
		<div class="mb-1 mt-1 w-100" style="text-align: right;">
			<a href="{{route('edit-book', $bookSlug)}}" class="btn btn-primary btn-sm">Back to Book</a>
		</div>
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color">
				<h3 style="margin:10px;" class="text-center" id="bookTitle">Codex for {{ $bookData['title'] }}</h3>
			</div>
			<div class="card-body modal-content modal-content-color">
				
				<div class="mb-5">
					<label for="characters" class="form-label h4">Characters</label>
					<div class="row">
						<div class="col-12" id="charactersCol">
            <textarea class="form-control" id="characters"
                      rows="10"
                      data-original="{{ $bookData['codex']['characters'] }}">{{ $bookData['codex']['characters'] }}</textarea>
						</div>
						<div class="col-6" id="charactersDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="charactersDiff"></div>
						</div>
					</div>
				</div>
				
				<div class="mb-5">
					<label for="locations" class="form-label h4">Locations</label>
					<div class="row">
						<div class="col-12" id="locationsCol">
            <textarea class="form-control" id="locations"
                      rows="10"
                      data-original="{{ $bookData['codex']['locations'] }}">{{ $bookData['codex']['locations'] }}</textarea>
						</div>
						<div class="col-6" id="locationsDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="locationsDiff"></div>
						</div>
					</div>
				</div>
				
				<div class="mb-5">
					<label for="objects" class="form-label h4">Objects/Items</label>
					<div class="row">
						<div class="col-12" id="objectsCol">
            <textarea class="form-control" id="objects"
                      rows="10"
                      data-original="{{ $bookData['codex']['objects'] }}">{{ $bookData['codex']['objects'] }}</textarea>
						</div>
						<div class="col-6" id="objectsDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="objectsDiff"></div>
						</div>
					</div>
				</div>
				
				<div class="mb-3">
					<label for="lore" class="form-label h4">Lore</label>
					<div class="row">
						<div class="col-12" id="loreCol">
            <textarea class="form-control" id="lore"
                      rows="10"
                      data-original="{{ $bookData['codex']['lore'] }}">{{ $bookData['codex']['lore'] }}</textarea>
						</div>
						<div class="col-6" id="loreDiffCol" style="display: none;">
							<div class="mb-3 modal-header-color" id="loreDiff"></div>
						</div>
					</div>
				</div>
				<button id="saveCodex" class="btn btn-primary">Save Codex</button>
			</div>
		
		</div>
		
		<hr>
		
		<div class="card general-card">
			<div class="card-header modal-header modal-header-color" style="display: block;">
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
				</div>
				<div class="mt-1 mb-1 small" style="border: 1px solid #ccc; border-radius: 5px; padding: 5px;">
					<div id="modelDescription"></div>
					<div id="modelPricing"></div>
				</div>
				<br>
				<a href="#" id="restartTour">{{ __('default.Restart Tour') }}</a>
			
			
			</div>
			<div class="card-body modal-content modal-content-color">
				
				
				<h5>Chapters and Beats</h5>
				<div id="chaptersAndBeats">
					@php $foundBeats = false; @endphp
					@foreach($bookData['acts'] as $act)
						@foreach($act['chapters'] as $chapter)
							@php $showChapter = false; @endphp
							@if(isset($chapter['beats']))
								@foreach($chapter['beats'] as $beatIndex => $beat)
									@if(!empty($beat['beat_text']))
										@php
											$beat_added_before = '';
											if (!empty($bookData['codex']['beats'])) {
												for ($i = 0; $i < count($bookData['codex']['beats']); $i++) {
													if ($bookData['codex']['beats'][$i]['chapterFilename'] === $chapter['chapterFilename'] && $bookData['codex']['beats'][$i]['beatIndex'] == $beatIndex) {
														$beat_added_before = '<label class="badge bg-success">' . __('default.Already Added') . '</label>';
														break;
													}
												}
											}
											$foundBeats = true;

											if (!$showChapter) {
												$showChapter = true;
											echo '<div class="border p-3 mb-3"><h5>'.$chapter['name'].'</h5>';
											}
										@endphp
										<div class="form-check mb-3">
											<input class="form-check-input beat-checkbox" type="checkbox"
											       value="{{ $chapter['chapterFilename'] }}-!-!-{{ $beatIndex }}"
											       id="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}"
											       name="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}">
											<label class="form-check-label" for="beat-{{ $chapter['chapterFilename'] }}-{{ $beatIndex }}">
												{!! $beat_added_before !!} Beat {{ $beatIndex + 1 }} - {{ $beat['description'] ?? '' }}
											</label>
										</div>
									@endif
								@endforeach
							@endif
							@php
								if ($showChapter) { echo  '</div>'; }
							@endphp
						@endforeach
					@endforeach
					@if (!$foundBeats)
						<div class="border p-3 mb-3">
							<h5>Sample Chapter 1</h5>
							<div class="form-check mb-3">
								<input class="form-check-input beat-checkbox" type="checkbox"
								       value="sample-chapter-1-0"
								       id="beat-sample-chapter-1-0"
								       name="beat-sample-chapter-1-0">
								<label class="badge bg-success">{{__('default.Already Added')}}</label> <label
									class="form-check-label" for="beat-sample-chapter-1-0">
									Beat 1 - Sample Beat
								</label>
							</div>
							<div class="form-check mb-3">
								<input class="form-check-input beat-checkbox" type="checkbox"
								       value="sample-chapter-1-0"
								       id="beat-sample-chapter-1-0"
								       name="beat-sample-chapter-1-0">
								<label
									class="form-check-label" for="beat-sample-chapter-1-0">
									Beat 2 - Sample Beat
								</label>
							</div>
						</div>
					@endif
				</div>
				@if (!$foundBeats)
					<div class="alert alert-warning" role="alert">
						{{__('default.Showing sample beats. Please add beats to your chapters to generate codex data.')}}
					</div>
					<button class="btn btn-primary updateCodexFromBeatsBtn" id="dummyUpdateCodexFromBeats">Update Codex from Selected Beats</button>
				@else
					<button id="updateCodexFromBeats" class="btn btn-primary mt-3 updateCodexFromBeatsBtn">Update Codex from Selected Beats</button>
				@endif
			</div>
		</div>
	</div>
</main>

<!-- Alert Modal -->
<div class="modal fade" id="alertModal" tabindex="-1" aria-labelledby="alertModalLabel"
     aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content modal-content-color">
			<div class="modal-header modal-header-color">
				<h5 class="modal-title" id="alertModalLabel">Alert</h5>
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
<script src="/js/bootstrap.min.js"></script>
<script src="/js/moment.min.js"></script>

<script src="/js/diff.js"></script>

<!-- Your custom scripts -->
<script src="/js/custom-ui.js"></script>
<script src="/js/intro.min.js"></script>


<script>
	let savedLlm = localStorage.getItem('codex-llm') || 'anthropic/claude-3-haiku:beta';
	
	function setTextareaHeight(field) {
		var textarea = document.getElementById(field);
		var diffDiv = document.getElementById(field + 'Diff');
		textarea.style.height = diffDiv.offsetHeight + 'px';
	}
	
	function showDiff(field, newText) {
		var oldText = $('#' + field).data('original');
		var diff = Diff.diffLines(oldText, newText);
		var display = document.getElementById(field + 'Diff');
		display.innerHTML = '';
		
		diff.forEach((part) => {
			var color = part.added ? 'green' :
				part.removed ? 'red' : 'grey';
			var span = document.createElement('span');
			span.style.color = color;
			
			var lines = part.value.split('\n');
			lines.forEach((line, index) => {
				if (index > 0) {
					span.appendChild(document.createElement('br'));
				}
				span.appendChild(document.createTextNode(line));
			});
			
			display.appendChild(span);
		});
		
		$('#' + field).val(newText).data('original', newText);
		
		// Adjust layout
		$('#' + field + 'Col').removeClass('col-12').addClass('col-6');
		$('#' + field + 'DiffCol').show();
		
		setTextareaHeight(field);
		
	}
	
	function resetLayout() {
		['characters', 'locations', 'objects', 'lore'].forEach(field => {
			$('#' + field + 'Col').removeClass('col-6').addClass('col-12');
			$('#' + field + 'DiffCol').hide();
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
		let intro = introJs().setOptions({
			steps: [
				{
					element: '#characters',
					intro: 'Enter characters data here. Keep in mind that this data will be used to generate content. So keep it concise and relevant.'
				},
				{
					element: '#locations',
					intro: 'Enter locations data here. When using the AI engine to update the codex, you will see a diff view of the changes to each codex section.'
				},
				{
					element: '#objects',
					intro: 'Enter objects/items data here. Like with the other fields you can enter manual data or use the AI engine to generate content from the beats.'
				},
				{
					element: '#lore',
					intro: 'Enter lore data here. You know the drill by now. Keep it relevant and concise.'
				},
				{
					element: '#llmSelect',
					intro: 'Select an AI engine to use for when you want to update the codex from the beats automatically.'
				},
				{
					element: '#chaptersAndBeats',
					intro: 'Select the beats you want to use to update the codex. You can select multiple beats. Beats that have already been added to the codex are marked with a green badge.'
				},
				{
					element: '.updateCodexFromBeatsBtn',
					intro: 'Click this button to have the AI update the codex with the selected beats. You will see a diff view of the changes before saving.'
				},
				{
					element: '#saveCodex',
					intro: 'Click this button to save the codex data. Don\'t forget to save after updating the codex from the beats.'
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
			localStorage.setItem('codexIntroCompleted', 'true');
		});
		
		intro.start();
		
	}
	
	$(document).ready(function () {
		toggleIntroJsStylesheet();
		
		if (!localStorage.getItem('codexIntroCompleted')) {
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
			localStorage.removeItem('codexIntroCompleted');
			startIntro();
		});
		
		
		$('#alertModal').on('hidden.bs.modal', function () {
			if ($('#alertModalContent').text().trim() === 'Codex saved successfully') {
				location.reload();
			}
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
			localStorage.setItem('codex-llm', $(this).val());
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
		
		
		$('#saveCodex').on('click', function (e) {
			e.preventDefault();
			$('#fullScreenOverlay').removeClass('d-none');
			
			// Collect checked beats
			let checkedBeats = [];
			$('.beat-checkbox:checked').each(function () {
				let [chapterFilename, beatIndex] = $(this).val().split('-!-!-');
				checkedBeats.push({chapterFilename, beatIndex: parseInt(beatIndex)});
			});
			
			$.ajax({
				url: '/book/{{ $bookSlug }}/codex',
				method: 'POST',
				data: {
					characters: $('#characters').val(),
					locations: $('#locations').val(),
					objects: $('#objects').val(),
					lore: $('#lore').val(),
					beats: checkedBeats,
					_token: '{{ csrf_token() }}'
				},
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html('Codex saved successfully');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html('Error saving codex');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			});
		});
		
		$('#updateCodexFromBeats').on('click', function () {
			let selectedBeats = [];
			$('.beat-checkbox:checked').each(function () {
				let [chapterFilename, beatIndex] = $(this).val().split('-!-!-');
				selectedBeats.push({chapterFilename, beatIndex: parseInt(beatIndex)});
			});
			
			if (selectedBeats.length === 0) {
				$("#alertModalContent").html('{{__('default.Please select at least one beat to update the codex.')}}');
				$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				return;
			}
			
			$('#fullScreenOverlay').removeClass('d-none');
			$.ajax({
				url: '/book/{{ $bookSlug }}/update-codex-from-beats',
				method: 'POST',
				data: {
					llm: $('#llmSelect').val(),
					selectedBeats: selectedBeats,
					_token: '{{ csrf_token() }}'
				},
				success: function (response) {
					$('#fullScreenOverlay').addClass('d-none');
					if (response.success) {
						showDiff('characters', response.codex.characters);
						showDiff('locations', response.codex.locations);
						showDiff('objects', response.codex.objects);
						showDiff('lore', response.codex.lore);
						
						$("#diffView").show();
						
						$("#alertModalContent").html('{{__('default.Codex Data Update, please verify then save')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					} else {
						$("#alertModalContent").html('{{__('default.Error updating codex')}}: ' + response.message);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				},
				error: function () {
					$('#fullScreenOverlay').addClass('d-none');
					$("#alertModalContent").html('{{__('default.Error updating codex')}}');
					$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
				}
			});
		});
		
	});
</script>


</body>
</html>
