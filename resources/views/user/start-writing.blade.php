@extends('layouts.app')

@section('title', 'Start Writing')

@section('content')
	<link rel="stylesheet" href="/css/introjs.css">
	<link rel="stylesheet" href="/css/introjs-dark.css" disabled>
	
	<style>
      #fullScreenOverlay {
          position: fixed;
          top: 0;
          left: 0;
          width: 100%;
          height: 100%;
          background-color: rgba(0, 0, 0, 0.7);
          z-index: 9999;
          display: flex;
          justify-content: center;
          align-items: center;
      }

      .overlay-content {
          text-align: center;
      }


      #charCount {
          font-size: 0.9em;
      }

      #charCount.valid {
          color: green;
      }

      #charCount.invalid {
          color: red;
      }

      #modelDescription a {
          color: #007bff;
          text-decoration: none;
      }

      #modelDescription a:hover {
          text-decoration: underline;
      }
	
	</style>
	
	<main class="pt-5">
		
		<!-- Content -->
		<div class="page-content site-theme-div">
			<!-- contact area -->
			<div class="content-block">
				<!-- Browse Jobs -->
				<section class="content-inner site-theme-div">
					<div class="container">
						<div class="row">
							<div class="col-xl-3 col-lg-4 mb-5">
								<img alt="" src="{{$coverFilename}}">
								<div class="mb-3 mt-3">
									<a href="#" id="restartTour">{{ __('default.Restart Tour') }}</a>
								</div>
							</div>
							<div class="col-xl-9 col-lg-8 mb-5">
								<div class="shop-bx shop-profile">
									<div class="shop-bx-title clearfix">
										<h5 class="text-uppercase">{{__('default.Add Book')}}</h5>
									</div>
									
									
									<div class="mb-3">
										<label for="user_blurb" class="form-label">{{__('default.Book Description')}}:</label>
										<textarea class="form-control" id="user_blurb" name="user_blurb" required
										          placeholder="{{__('default.describe your books story, people and events. While you can just say \'A Boy Meets World\' the longer and more detailed your blurb is the more creative and unique the writing will be.')}}"
										          rows="8"></textarea>
										<div id="charCount" class="mt-2">0/2000</div>
									</div>
									<div class="mb-3">
										<label for="language" class="form-label">{{__('default.Language')}}:</label>
										
										<select class="form-control" id="language" name="language" required>
											<option
												value="{{__('default.English')}}" <?php if (__('default.Default Language') === __('default.English')) {
												echo " SELECTED";
											} ?>>{{__('default.English')}}</option>
											<option
												value="{{__('default.Norwegian')}}" <?php if (__('default.Default Language') === __('default.Norwegian')) {
												echo " SELECTED";
											} ?>>{{__('default.Norwegian')}}</option>
											<option
												value="{{__('default.Turkish')}}" <?php if (__('default.Default Language') === __('default.Turkish')) {
												echo " SELECTED";
											} ?>>{{__('default.Turkish')}}</option>
										</select>
									</div>
									
									<div class="row">
										<div class="mb-1 col-12 col-xl-6">
											<label for="language" class="form-label">
												{{__('default.Book Structure')}}:
												<i class="fas fa-info-circle" data-bs-toggle="modal" data-bs-target="#bookStructureModal"
												   style="cursor: pointer;"></i>
											</label>
											
											<select class="form-control" id="bookStructure" name="bookStructure" required>
												<option
													value="{{__('default.the_1_act_story.txt')}}">{{__('default.The 1 Act Story (1 Act, 3 Chapters)')}}</option>
												<option
													value="{{__('default.abcde_short_story.txt')}}">{{__('default.ABCDE (1 Acts, 6 Chapters)')}}</option>
												<option
													value="{{__('default.fichtean_curve.txt')}}">{{__('default.Fichtean Curve (3 Acts, 8 Chapters)')}}</option>
												<option
													value="{{__('default.freytags_pyramid.txt')}}">{{__('default.Freytag\'s Pyramid (5 Acts, 9 Chapters)')}}</option>
												<option
													value="{{__('default.heros_journey.txt')}}">{{__('default.Hero\'s Journey (3 Acts, 12 Chapters)')}}</option>
												<option
													value="{{__('default.story_clock.txt')}}">{{__('default.Story Clock (4 Acts, 12 Chapters)')}}</option>
												<option
													value="{{__('default.save_the_cat.txt')}}">{{__('default.Save The Cat (4 Acts, 15 Chapters)')}}</option>
												<option
													value="{{__('default.dan_harmons_story_circle.txt')}}">{{__('default.Dan Harmon\'s Story Circle (8 Acts, 15 Chapters)')}}</option>
											</select>
										</div>
										
										<div class="mb-1 col-12 col-xl-6">
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
									</div>
									<div class="mt-1 small col-12 alert alert-primary">
										<div id="modelDescription"></div>
										<div id="modelPricing"></div>
									</div>
									
									<div class="row">
										<div class="mb-3 col-12 col-xl-6">
											<label for="adultContent" class="form-label">{{__('default.Content Type')}}:</label>
											<select class="form-control" id="adultContent" name="adultContent" required>
												<option value="non-adult">{{__('default.Non-Adult')}}</option>
												<option value="adult">{{__('default.Adult')}}</option>
											</select>
										</div>
										
										<div class="mb-3 col-12 col-xl-6">
											<label for="genre" class="form-label">{{__('default.Genre')}}:</label>
											<select class="form-control" id="genre" name="genre" required>
												<!-- Options will be populated dynamically -->
											</select>
										</div>
									</div>
									<div class="row">
										<div class="mb-3 col-12 col-xl-6">
											<label for="authorName" class="form-label">{{__('default.Author Name')}}:</label>
											<input type="text" class="form-control" id="authorName" name="authorName" required
											       value="{{ Auth::user()->name ?? 'Pen Name' }}">
										</div>
										<div class="mb-3 col-12 col-xl-6">
											<label for="publisherName" class="form-label">{{__('default.Publisher Name')}}:</label>
											<input type="text" class="form-control" id="publisherName" name="publisherName" required
											       value="WBWAI Publishing">
										</div>
									</div>
									<div class="row">
										<div class="mb-3 col-12 col-xl-6">
											<label for="writingStyle" class="form-label">{{__('default.Writing Style')}}:</label>
											<select class="form-control" id="writingStyle" name="writingStyle" required>
												@foreach($writingStyles as $style)
													<option value="{{ $style['value'] }}">{{ $style['label'] }}</option>
												@endforeach
											</select>
										</div>
										
										<div class="mb-3 col-12 col-xl-6">
											<label for="narrativeStyle" class="form-label">{{__('default.Narrative Style')}}:</label>
											<select class="form-control" id="narrativeStyle" name="narrativeStyle" required>
												@foreach($narrativeStyles as $style)
													<option value="{{ $style['value'] }}">{{ $style['value'] }}</option>
												@endforeach
											</select>
										</div>
									</div>
									
									<div class="mb-3" style="font-size: 14px;" id="hint_1">
										{{__('default.After clicking the submit button, the AI will first write the book\'s title and blurb and characters. You\'ll need to confirm the characters before the AI writes the book.')}}
									</div>
									<div class="mb-3 d-none alert alert-primary" style="font-size: 16px;" id="hint_2">
										{{__('default.Please verify the title, blurb the back cover text of the book and the characters of the story.')}}
										<br>
										{{__('default.After clicking the submit button, The AI will start creating all the chapters for the book. This process may take a few minutes.')}}
									</div>
									
									<div id="book_details" class="d-none">
										<div class="mb-3">
											<label for="book_title" class="form-label">{{__('default.Book Title')}}:</label>
											<input type="text" class="form-control" id="book_title" name="book_title" required>
										</div>
										
										<div class="mb-3">
											<label for="book_blurb" class="form-label">{{__('default.Book Blurb')}}:</label>
											<textarea class="form-control" id="book_blurb" name="book_blurb" required rows="8"></textarea>
										</div>
										
										<div class="mb-3">
											<label for="back_cover_text" class="form-label">{{__('default.Back Cover Text')}}:</label>
											<textarea class="form-control" id="back_cover_text" name="back_cover_text" required
											          rows="9"></textarea>
										</div>
										
										<div class="mb-3">
											<label for="character_profiles" class="form-label">{{__('default.Character Profiles')}}</label>
											<textarea class="form-control" id="character_profiles" name="character_profiles" required
											          rows="9"></textarea>
										</div>
									
									
									</div>
									<button id="addBookStepOneBtn" class="btn btn-primary btnhover"
									        style="min-width: 180px;">{{__('default.Submit')}}</button>
									<button id="addBookStepTwoBtn" class="btn btn-primary btnhover d-none"
									        style="min-width: 180px;">{{__('default.Submit')}}</button>
									<button id="tryAgainBtn" class="btn btn-danger btnhover d-none"
									        style="min-width: 180px;">{{__('default.Try Again')}}</button>
								
								</div>
							</div>
						</div>
					</div>
				</section>
				<!-- Browse Jobs END -->
			</div>
		</div>
	</main>
	<!-- Content END-->
	
	@include('layouts.footer')
	
	<div id="fullScreenOverlay" class="d-none">
		<div class="overlay-content">
			<div class="spinner-border text-light" role="status">
				<span class="visually-hidden">{{__('Loading...')}}</span>
			</div>
			<p class="mt-3 text-light">{{__('default.Processing your request. This may take a few minutes...')}}</p>
		</div>
	</div>
	
	
	
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
	
	<!-- Book Structure Info Modal -->
	<div class="modal fade" id="bookStructureModal" tabindex="-1" aria-labelledby="bookStructureModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable modal-lg">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="bookStructureModalLabel">Book Structure Types</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body modal-body-color">
					<div class="accordion" id="bookStructureAccordion">
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading1">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
									The 1 Act Story (1 Act, 3 Chapters)
								</button>
							</h2>
							<div id="collapse1" class="accordion-collapse collapse" aria-labelledby="heading1" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									A simple structure for short stories or novellas. It consists of a single act divided into three chapters: setup, confrontation, and resolution. This structure is straightforward and focuses on a single main conflict or plot point.
								</div>
							</div>
						</div>
						
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading2">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
									ABCDE (1 Act, 6 Chapters)
								</button>
							</h2>
							<div id="collapse2" class="accordion-collapse collapse" aria-labelledby="heading2" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									The ABCDE structure stands for Action, Background, Conflict, Development, and Ending. It's a concise structure that works well for short stories or novellas, guiding the narrative through these five key elements across six chapters.
								</div>
							</div>
						</div>
						
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading3">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
									Fichtean Curve (3 Acts, 8 Chapters)
								</button>
							</h2>
							<div id="collapse3" class="accordion-collapse collapse" aria-labelledby="heading3" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									The Fichtean Curve is a three-act structure that focuses on rising action through a series of crises. It starts with immediate conflict and continues to build tension through multiple crises before reaching the climax and resolution. This structure is good for action-packed or suspenseful stories.
								</div>
							</div>
						</div>
						
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading4">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
									Freytag's Pyramid (5 Acts, 9 Chapters)
								</button>
							</h2>
							<div id="collapse4" class="accordion-collapse collapse" aria-labelledby="heading4" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									Freytag's Pyramid is a five-act structure: exposition, rising action, climax, falling action, and denouement. It provides a balanced approach to storytelling, with equal emphasis on the build-up and the aftermath of the climax. This structure works well for complex plots with significant character development.
								</div>
							</div>
						</div>
						
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading5">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
									Hero's Journey (3 Acts, 12 Chapters)
								</button>
							</h2>
							<div id="collapse5" class="accordion-collapse collapse" aria-labelledby="heading5" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									Based on Joseph Campbell's monomyth, the Hero's Journey is a three-act structure that follows a protagonist's adventure through 12 stages. It includes the departure, initiation, and return of the hero. This structure is ideal for epic tales, fantasy, and coming-of-age stories.
								</div>
							</div>
						</div>
						
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading6">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
									Story Clock (4 Acts, 12 Chapters)
								</button>
							</h2>
							<div id="collapse6" class="accordion-collapse collapse" aria-labelledby="heading6" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									The Story Clock is a four-act structure that visualizes the story as a clock face, with 12 chapters corresponding to the hours. It provides a clear framework for pacing and plot development, ensuring key events occur at regular intervals throughout the narrative.
								</div>
							</div>
						</div>
						
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading7">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
									Save The Cat (4 Acts, 15 Chapters)
								</button>
							</h2>
							<div id="collapse7" class="accordion-collapse collapse" aria-labelledby="heading7" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									Save The Cat is a four-act structure popularized by screenwriter Blake Snyder. It breaks down the narrative into 15 specific beats or plot points. This structure is highly detailed and works well for both novels and screenplays, providing a clear roadmap for the story's progression.
								</div>
							</div>
						</div>
						
						<div class="accordion-item">
							<h2 class="accordion-header" id="heading8">
								<button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
									Dan Harmon's Story Circle (8 Acts, 15 Chapters)
								</button>
							</h2>
							<div id="collapse8" class="accordion-collapse collapse" aria-labelledby="heading8" data-bs-parent="#bookStructureAccordion">
								<div class="accordion-body">
									Dan Harmon's Story Circle is an eight-act structure based on the Hero's Journey but simplified for modern storytelling. It follows a character who goes on a journey, adapts to a new situation, and undergoes a change. This structure is versatile and can be applied to various genres and story lengths.
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer modal-footer-color justify-content-start">
					<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
				</div>
			</div>
		</div>
	</div>

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script src="/js/intro.min.js"></script>
	<script>
		let savedLlm = localStorage.getItem('book-llm') || 'anthropic/claude-3-haiku:beta';
		
		let exampleQuestion = '';
		let exampleAnswer = '';
		let bookKeywords = '';
		let bookEditUrl = '';
		
		// Function to update genre dropdown
		function updateGenreDropdown(genres) {
			const genreDropdown = $('#genre');
			genreDropdown.empty();
			genres.forEach(genre => {
				genreDropdown.append($('<option></option>').val(genre).text(genre));
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
		
		function startIntro() {
			let intro = introJs().setOptions({
				steps: [
					{
						element: '#user_blurb',
						intro: "{{__('default.Describe your book\'s story, characters, and events here. The more detailed your description, the more creative and unique the writing will be.')}}"
					},
					{
						element: '#language',
						intro: "{{__('default.Select the language for your book.')}}"
					},
					{
						element: '#bookStructure',
						intro: "{{__('default.Choose the structure for your book. This determines the number of acts and chapters.')}}"
					},
					{
						element: '#llmSelect',
						intro: "{{__('default.Select the AI engine to use for generating your book.')}}"
					},
					{
						element: '#adultContent',
						intro: "{{__('default.Specify whether your book contains adult content or not.')}}"
					},
					{
						element: '#genre',
						intro: "{{__('default.Choose the genre for your book.')}}"
					},
					{
						element: '#writingStyle',
						intro: "{{__('default.Select the writing style for your book.')}}"
					},
					{
						element: '#narrativeStyle',
						intro: "{{__('default.Choose the narrative style for your book.')}}"
					},
					{
						element: '#addBookStepOneBtn',
						intro: "{{__('default.Click this button to generate the book\'s title, blurb, and characters.')}}",
						position: 'top'
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
				localStorage.setItem('startWritePart1IntroCompleted', 'true');
			});
			
			intro.start();
		}
		
		// Function to continue the tour for the second part
		function continueTour() {
			let intro = introJs().setOptions({
				steps: [
					{
						element: '#book_title',
						intro: "{{__('default.This is the generated title for your book. You can edit it if needed.')}}"
					},
					{
						element: '#book_blurb',
						intro: "{{__('default.This is the generated blurb for your book. Feel free to make adjustments.')}}"
					},
					{
						element: '#back_cover_text',
						intro: "{{__('default.This text will appear on the back cover of your book. You can modify it as needed.')}}"
					},
					{
						element: '#character_profiles',
						intro: "{{__('default.These are the generated character profiles for your book. Review and edit if necessary.')}}"
					},
					{
						element: '#addBookStepTwoBtn',
						intro: "{{__('default.Click this button to start generating the full content of your book.')}}",
						position: 'top'
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
				localStorage.setItem('startWritePart2IntroCompleted', 'true');
			});
			
			intro.start();
		}
		
		function isDarkMode() {
			return document.documentElement.getAttribute('data-bs-theme') === 'dark';
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
		
		
		$(document).ready(function () {
			toggleIntroJsStylesheet();
			
			// Start the tour if it's the user's first time
			if (!localStorage.getItem('startWritePart1IntroCompleted')) {
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
				localStorage.removeItem('startWritePart1IntroCompleted');
				localStorage.removeItem('startWritePart2IntroCompleted');
				startIntro();
			});
			
			getLLMsData().then(function (llmsData) {
				const llmSelect = $('#llmSelect');
				
				llmsData.forEach(function (model) {
					// Calculate and display pricing per million tokens
					let promptPricePerMillion = ((model.pricing.prompt || 0) * 1000000).toFixed(2);
					let completionPricePerMillion = ((model.pricing.completion || 0) * 1000000).toFixed(2);
					
					let model_score = ' - ' + model.score;
					if (model.score === 0) {
						model_score = '';
					}
					let model_ugi = ' - Uncensored Score: ' + model.ugi;
					if (model.ugi === 0) {
						model_ugi = '';
					}
					
					llmSelect.append($('<option>', {
						value: model.id,
						text: model.name + model_ugi + ' - $' + promptPricePerMillion + ' / $' + completionPricePerMillion,
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
				localStorage.setItem('book-llm', $(this).val());
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
			
			
			
			$('#alertModal').on('hidden.bs.modal', function (e) {
				if (bookEditUrl) {
					window.location.href = bookEditUrl;
				}
			});
			
			const maxChars = 2000;
			const userBlurb = $('#user_blurb');
			const charCount = $('#charCount');
			const addBookStepOneBtn = $('#addBookStepOneBtn');
			
			function updateCharCount() {
				const remaining = maxChars - userBlurb.val().length;
				charCount.text(userBlurb.val().length + '/' + maxChars);
				
				if (remaining < 0) {
					charCount.removeClass('valid').addClass('invalid');
					addBookStepOneBtn.prop('disabled', true);
				} else {
					charCount.removeClass('invalid').addClass('valid');
					addBookStepOneBtn.prop('disabled', false);
				}
			}
			
			userBlurb.on('input', updateCharCount);
			
			// Initial call to set the correct state
			updateCharCount();
			
			// Define genre arrays
			const adultGenres = {!! json_encode($adult_genres_array) !!};
			const nonAdultGenres = {!! json_encode($genres_array) !!};
			
			// Initial genre dropdown population
			updateGenreDropdown(nonAdultGenres);
			
			// Handle adult content dropdown change
			$('#adultContent').on('change', function () {
				const selectedValue = $(this).val();
				if (selectedValue === 'adult') {
					updateGenreDropdown(adultGenres);
				} else {
					updateGenreDropdown(nonAdultGenres);
				}
			});
			
			
			$("#tryAgainBtn").on('click', function (event) {
				event.preventDefault();
				$('#addBookStepOneBtn').removeClass('d-none');
				$('#hint_1').removeClass('d-none');
				$('#hint_2').addClass('d-none');
				$('#book_details').addClass('d-none');
				$('#addBookStepTwoBtn').addClass('d-none');
				$(this).addClass('d-none');
			});
			
			
			$("#addBookStepOneBtn").on('click', function (event) {
				event.preventDefault();
				$('#fullScreenOverlay').removeClass('d-none');
				
				console.log("user_blurb value:", $('#user_blurb').val());
				
				$.ajax({
					url: '{{ route("write-book-character-profiles") }}',
					type: 'POST',
					data: {
						user_blurb: $('#user_blurb').val(),
						language: $('#language').val(),
						book_structure: $('#bookStructure').val(),
						author_name: $('#authorName').val(),
						publisher_name: $('#publisherName').val(),
						llm: savedLlm,
						adultContent: $('#adultContent').val(),
						genre: $('#genre').val(),
						writingStyle: $('#writingStyle').val(),
						narrativeStyle: $('#narrativeStyle').val(),
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (data) {
						console.log(data);
						$('#fullScreenOverlay').addClass('d-none');
						if (data.success) {
							$('#addBookStepOneBtn').addClass('d-none');
							$('#hint_1').addClass('d-none');
							$('#hint_2').removeClass('d-none');
							$('#tryAgainBtn').removeClass('d-none');
							
							$('#book_details').removeClass('d-none');
							$('#addBookStepTwoBtn').removeClass('d-none');
							$('#book_title').val(data.data.title);
							$('#book_blurb').val(data.data.blurb);
							$('#back_cover_text').val(data.data.back_cover_text);
							
							exampleQuestion = data.data.example_question;
							exampleAnswer = data.data.example_answer;
							bookKeywords = data.data.keywords;
							
							let characterProfiles = '';
							data.data.character_profiles.forEach(function (profile) {
								characterProfiles += (profile.name || '') + '\n' + (profile.description || '') + '\n\n';
							});
							
							$('#character_profiles').val(characterProfiles);
							
							if (!localStorage.getItem('startWritePart2IntroCompleted')) {
								setTimeout(function () {
									if ($('#book_details').is(':visible')) {
										continueTour();
									}
								}, 500); // Adjust this delay if needed
							}
							
						} else {
							$("#alertModalContent").html("Error: " + data.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
					},
					error: function (xhr, status, error) {
						$('#fullScreenOverlay').addClass('d-none');
						
						$("#alertModalContent").html("Error: " + error);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				});
			});
			
			$("#addBookStepTwoBtn").on('click', function (event) {
				event.preventDefault();
				$('#fullScreenOverlay').removeClass('d-none');
				
				$.ajax({
					url: '{{ route("write-book") }}',
					type: 'POST',
					data: {
						user_blurb: $('#user_blurb').val(),
						language: $('#language').val(),
						book_structure: $('#bookStructure').val(),
						author_name: $('#authorName').val(),
						publisher_name: $('#publisherName').val(),
						book_title: $('#book_title').val(),
						book_blurb: $('#book_blurb').val(),
						back_cover_text: $('#back_cover_text').val(),
						character_profiles: $('#character_profiles').val(),
						example_question: exampleQuestion,
						example_answer: exampleAnswer,
						book_keywords: bookKeywords,
						llm: savedLlm,
						adult_content: $('#adultContent').val(),
						genre: $('#genre').val(),
						writing_style: $('#writingStyle').val(),
						narrative_style: $('#narrativeStyle').val(),
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (data) {
						$('#fullScreenOverlay').addClass('d-none');
						if (data.success) {
							bookEditUrl = '{{ route("book-details", "") }}/' + data.bookSlug;
							$("#alertModalContent").html("{{ __('default.Book created successfully.') }} <a href='" + bookEditUrl + "'>{{ __('default.Click here to edit the book.') }}</a>");
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						} else {
							$("#alertModalContent").html("Error: " + data.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
					},
					error: function (xhr, status, error) {
						$('#fullScreenOverlay').addClass('d-none');
						
						$("#alertModalContent").html("Error: " + error);
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				});
			});
			
		});
	
	</script>
@endpush



