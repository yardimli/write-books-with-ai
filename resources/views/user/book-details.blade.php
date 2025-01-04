@extends('layouts.app')

@section('title', 'All Books')

@section('content')
	<link rel="stylesheet" href="/css/introjs.css">
	<link rel="stylesheet" href="/css/introjs-dark.css" disabled>
	
	<script>
		let bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
	</script>
	<main class="pt-5">
		
		<!-- Container START -->
		<div class="container pt-4">
			<div class="row g-4">
				<div class="col-xl-5 col-lg-5 col-12 vstack gap-4">
					<div class="card">
						<div class="card-body py-3">
							<img src="{{$book['cover_filename']}}" alt="book" class="pb-4" style="min-height: 400px;" id="bookCover">
							<br>
							<a href="{{route('read-book',$book_slug)}}" id="readBookBtn"
							   class="btn btn-primary-soft">{{__('default.Read Book')}}</a>
							
							@if ( (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email)) || (Auth::user() && Auth::user()->isAdmin()) )
								<a href="{{route('edit-book',$book_slug)}}" id="editBookBtn"
								   class="btn btn-danger-soft">{{__('default.Edit Book')}}</a>
								
								<button class="btn btn-primary-soft" title="{{__('default.Cover Image')}}" id="createCoverBtn">
									<i class="bi bi-image"></i> {{__('default.Cover Image')}}
								</button>
								
								<div class="mb-3 mt-3 small">
									<a href="#" id="restartTour">{{ __('default.Restart Tour') }}</a>
								</div>
							
							@endif
						</div>
					</div>
				</div>
				
				<!-- Main content START -->
				<div class="col-xl-7 col-lg-7 col-12 vstack gap-4">
					<!-- My profile START -->
					<div class="card">
						<div class="card-body py-0">
							
							<h1 class="title mb-0"><a href="{{route('read-book',$book_slug)}}">{{$book['title'] ?? ''}}</a>
							</h1>
							
							<div class="d-flex align-items-center justify-content-between my-3">
								<div class="d-flex align-items-center">
									<!-- Avatar -->
									<div class="avatar avatar-story me-2">
										<a href=""> <img
												class="avatar-img rounded-circle"
												src="{{$book['author_avatar'] ?? ''}}"
												alt=""> </a>
									</div>
									<!-- Info -->
									<div>
										<div class="nav nav-divider">
											<h6 class="nav-item card-title mb-0"><a
													href=""> {{$book['author_name'] ?? ''}} </a>
											</h6>
											
											<span class="nav-item small">{{$book['publisher_name'] ?? ''}}</span>
											{{--											<span class="nav-item small"> <i class="bi bi-clock pe-1"></i>55 min read</span>--}}
											<span class="nav-item small">{{date("Y-m-d", $book['file_time'] ?? 1923456789)}}</span>
										</div>
									</div>
								</div>
							</div>
							
							<div class="d-sm-flex align-items-start text-center text-sm-start">
								<div class="mt-4">
									
									<a
										href="{{route('showcase-library-genre',[$book['genre'] ?? ''])}}"
										class="badge bg-primary mb-2 me-1">{{$book['genre'] ?? ''}}</a>
									
									<span class="badge bg-secondary  me-1 mb-2">English</span>
									
									<br>
									@if (isset($book['keywords']))
										@foreach ($book['keywords'] as $keyword)
											<a href="{{route('showcase-library-keyword',[$keyword])}}"
											   class="badge bg-info  me-1 mb-2 ">{{$keyword}}</a>
										@endforeach
									@endif
								
								</div>
							</div>
							
							<p class="mt-4">{!! str_replace("\n","<br>", $book['back_cover_text'] ?? '')!!}</p>
							
							<button class="btn btn-sm btn-danger delete-book-btn mb-1 mt-1"
							        data-book-id="<?php echo urlencode($book_slug); ?>"><i
									class="bi bi-trash-fill"></i> {{__('default.Delete Book')}}
							</button>
						
						</div>
					
					</div>
					
					<figure class="bg-light rounded p-3 p-sm-4 my-4">
						<blockquote class="blockquote" style="font-size: 14px;">
							<span class="strong">Blurb:</span><br>
							{{$book['blurb'] ?? ''}}
						</blockquote>
						<figcaption class="blockquote-footer mb-0">
							<span class="strong">Character Profiles:</span><br>
							{!! str_replace("\n","<br>", $book['character_profiles'] ?? ''  ) !!}
						</figcaption>
					</figure>
				</div>
			</div>
		</div>
	</main>
	
	<!-- Modal for Creating Book Cover -->
	<div class="modal fade" id="createCoverModal" tabindex="-1" aria-labelledby="createCoverModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog ">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="createCoverModalLabel">{{__('default.Create Cover')}}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
				</div>
				<div class="modal-body modal-body-color">
					<div class="row">
						<div class="col-md-8">
						<textarea class="form-control" id="coverPrompt" rows="5"
						          placeholder="{{__('default.Enter cover description')}}"></textarea>
							<input type="text" id="coverBookTitle" class="form-control mt-2"
							       placeholder="{{__('default.Book Title')}}">
							<input type="text" id="coverBookAuthor" class="form-control mt-2"
							       placeholder="{{__('default.Book Author')}}">
							<div class="mb-1 form-check mt-2">
								<input type="checkbox" class="form-check-input" id="enhancePrompt" checked>
								<label class="form-check-label" for="enhancePrompt">
									{{__('default.Enhance Prompt')}}
								</label>
							</div>
							<span
								style="font-size: 14px; margin-left:24px;">{{__('default.AI will optimize for creative visuals')}}</span>
						</div>
						<div class="col-md-4">
							<img src="/images/placeholder-cover.jpg" alt="{{__('default.Generated Cover')}}"
							     style="width: 100%; height: auto;"
							     id="generatedCover">
						</div>
					</div>
				</div>
				<div class="modal-footer modal-footer-color justify-content-start">
					<button type="button" class="btn btn-primary-soft" id="generateCoverBtn"> {{__('default.Generate')}}</button>
					<button type="button" class="btn btn-success-soft" id="saveCoverBtn" disabled>{{__('default.Save')}}</button>
					<button type="button" class="btn btn-secondary-soft" data-bs-dismiss="modal"> {{__('default.Close')}}</button>
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
	
	<!-- Goto Edit Book Modal -->
	<div class="modal fade" id="gotoEditBookModal" tabindex="-1" aria-labelledby="gotoEditBookModalLabel"
	     aria-hidden="true">
		<div class="modal-dialog modal-dialog-scrollable">
			<div class="modal-content modal-content-color">
				<div class="modal-header modal-header-color">
					<h5 class="modal-title" id="gotoEditBookModalLabel">{{__('default.Start Writing')}}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{__('default.Close')}}"></button>
				</div>
				<div class="modal-body modal-body-color">
					{{__('default.Click "start" to edit the book chapters and beats.')}}
				</div>
				<div class="modal-footer modal-footer-color justify-content-start">
					<button type="button" class="btn btn-primary-soft" id="gotoEditBookBtn">{{__('default.Start')}}</button>
					<button type="button" class="btn btn-secondary-soft" data-bs-dismiss="modal">{{__('default.Close')}}</button>
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
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<script src="/js/intro.min.js"></script>
	
	@if ( (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email)) || (Auth::user() && Auth::user()->isAdmin()) )
		<script>
			let user_is_owner = true;
		</script>
	@else
		<script>
			let user_is_owner = false;
		</script>
	@endif
	
	<!-- Inline JavaScript code -->
	<script>
		var current_page = 'book_details';
		let bookToDelete = null;
		let createCoverFileName = '';
		
		function startIntro() {
			let intro = introJs().setOptions({
				steps: [
					{
						element: '#readBookBtn',
						intro: "As the button suggests, you can read the book by clicking this button. You'll also be able to export the book to PDF or DOCX formats here.",
					},
					{
						element: '#editBookBtn',
						intro: "If you're the owner of this book, you can edit the book chapters and beats by clicking this button.",
					},
					{
						element: '#createCoverBtn',
						intro: "This button will open the cover image creation modal. You can create a cover image for your book here. You'll be able to describe the image and the AI will generate a cover image for you. Including your pen name and book title will make the cover more personalized.",
					},
					{
						element: '.delete-book-btn',
						intro: 'Click this button to delete the book. This action cannot be undone.'
					},
				
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
			
			
			// intro.onafterchange(function (targetElement) {
			// 	if (targetElement.tagName.toLowerCase() === 'textarea') {
			// 		var nextButton = document.querySelector('.introjs-nextbutton');
			// 		nextButton.classList.add('introjs-disabled');
			// 		nextButton.classList.add('custom-disabled'); // Add this line
			//
			// 		$(targetElement).on('input', function () {
			// 			if ($(this).val().trim() !== '') {
			// 				nextButton.classList.remove('introjs-disabled');
			// 				nextButton.classList.remove('custom-disabled'); // Add this line
			// 			} else {
			// 				nextButton.classList.add('introjs-disabled');
			// 				nextButton.classList.add('custom-disabled'); // Add this line
			// 			}
			// 		});
			// 	}
			// });
			
			intro.oncomplete(function () {
				localStorage.setItem('bookDetailsCompleted', 'true');
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
			
			if (user_is_owner) {
				$("#gotoEditBookModal").modal({backdrop: 'static', keyboard: true}).modal('show');
			}
			
			$('#gotoEditBookBtn').on('click', function () {
				window.location.href = '{{route('edit-book',$book_slug)}}';
			});
			
			toggleIntroJsStylesheet();
			
			// Start the tour if it's the user's first time
			@if (Auth::user() && (($book['owner'] ?? '') === Auth::user()->email))
			if (!localStorage.getItem('bookDetailsCompleted')) {
				setTimeout(function () {
					startIntro();
				}, 500);
			}
			@endif
			
			
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
				localStorage.removeItem('bookDetailsCompleted');
				startIntro();
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
			
			
			$('#createCoverBtn').on('click', function (e) {
				e.preventDefault();
				$('#createCoverModal').modal({backdrop: 'static', keyboard: true}).modal('show');
				$("#coverBookTitle").val(bookData.title);
				$("#coverBookAuthor").val(bookData.author_name);
				$("#coverPrompt").val('{{__('default.An image describing: ')}}' + bookData.blurb);
			});
			
			$('#generateCoverBtn').on('click', function () {
				$('#generateCoverBtn').prop('disabled', true).text('{{__('default.Generating...')}}');
				
				$.ajax({
					url: '/make-cover-image/' + bookSlug,
					method: 'POST',
					data: {
						theme: $("#coverPrompt").val(),
						title_1: $("#coverBookTitle").val(),
						author_1: $("#coverBookAuthor").val(),
						creative: $("#enhancePrompt").is(':checked') ? 'more' : 'no',
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (data) {
						if (data.success) {
							$('#generatedCover').attr('src', "/storage/ai-images/" + data.output_filename);
							createCoverFileName = data.output_filename;
							$('#saveCoverBtn').prop('disabled', false);
						} else {
							$("#alertModalContent").html('{{__('default.Failed to generate cover: ')}}' + data.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
						$('#generateCoverBtn').prop('disabled', false).text('{{__('default.Generate')}}');
					}
				});
			});
			
			$('#saveCoverBtn').on('click', function () {
				$.ajax({
					url: '/book/' + bookSlug + '/cover',
					method: 'POST',
					data: {
						cover_filename: createCoverFileName
					},
					headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
					},
					dataType: 'json',
					success: function (data) {
						if (data.success) {
							$("#alertModalContent").html('{{__('default.Cover saved successfully!')}}');
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
							
							$('#bookCover').attr('src', '/storage/ai-images/' + createCoverFileName);
						} else {
							$("#alertModalContent").html('{{__('default.Failed to save cover: ')}}' + data.message);
							$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
						}
					},
					error: function (xhr, status, error) {
						$("#alertModalContent").html('{{__('default.An error occurred while saving the cover.')}}');
						$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
					}
				});
			});
			
		});
	</script>

@endpush
