@extends('layouts.app')

@section('title', 'All Books')

@section('content')
	
	<!-- **************** MAIN CONTENT START **************** -->
	<main class="pt-5">
		
		<!-- Page header START -->
		<div class="py-5"
		     style="background-image:url(/assets/images/header/pexels-repuding-12064.jpg); background-position: center center; background-size: cover; background-repeat: no-repeat;">
			<div class="container">
				
				<div class="row justify-content-center py-5">
					<div class="col-md-6 text-center">
						<!-- Title -->
						<h1 class="text-white" style="background-color: rgba(0,0,0,0.5)">{{__('default.Write Books With AI')}}</h1>
						<span class="mb-4 text-white" style="background-color: rgba(0,0,0,0.5)">{{__('default.Your Story, Our AI - Write Books Faster, Smarter, Better with AI')}}</span>
					</div>
				</div>
			</div>
		</div>
		<!-- Page header END -->
		
		<!-- Container START -->
		<div class="py-1">
			<div class="container">
				
				<div class="tab-content mb-0 pb-0">
					<!-- For you tab START -->
					<div class="tab-pane fade show active" id="tab-1">
						
						<div class="row g-4 mt-2">
							<div class="col-12">
								<div class="card p-1">
									<h2 class="p-1" style="margin:0px;">{{__('default.Your Private Library')}}</h2>
									<div class="ps-1">
										{{__('default.Here are all the books both complete and in progress.')}} {{__('default.Click on the book cover to read or edit the full book.')}}
									</div>
								</div>
							</div>
							
							@foreach ($books as $book)
								<div class="col-12 col-sm-6 col-lg-3">
									<!-- Card feed item START -->
									<div class="card h-100">
										<a class="text-body" href="{{route('book-details',$book['id'] ?? '0')}}"><img
												class="card-img-top" style="min-height: 300px"
												src="{{$book['cover_filename'] ?? ''}}"
												alt="Book"></a>
										@if ($book['language'] !== 'English')
											<div class="badge bg-info text-white mt-2 ms-2 me-2 position-absolute top-0 end-0"
											     style="z-index: 9">
												<span class="badge text-bg-info">{{ $book['language'] }}</span>
											</div>
										@endif
										<div class="position-absolute ms-2 mt-2 top-0 start-0"
										     style="z-index: 9"><a
												href="{{route('showcase-library-genre',[$book['genre'] ?? ''])}}"
												class="badge bg-primary">{{$book['genre'] ?? ''}}</a>
										</div>
										
										
										<!-- Card body START -->
										<div class="card-body">
											<!-- Info -->
											
											<h3 class="title mb-0"><a href="{{route('book-details',$book['id'] ?? '0')}}">{{$book['title'] ?? ''}}</a>
											</h3>
											
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
															<h6 class="card-title mb-0 w-100"><a
																	href=""> {{$book['author_name'] ?? ''}} </a>
															</h6>
															
															<span class="small me-2">{{$book['publisher_name'] ?? ''}}</span>
														</div>
													</div>
												</div>
											</div>
											
											<p>{{$book['blurb'] ?? ''}}</p>
										</div>
										<!-- Card body END -->
									</div>
								</div>
							@endforeach
						</div>
						
						
						
					</div>
				</div>
			</div>
		</div>
		<!-- Container END -->
	
	</main>
	<!-- **************** MAIN CONTENT END **************** -->
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
		var current_page = 'read_stories';
		$(document).ready(function () {
		});
	</script>

@endpush