<!-- footer START -->
<footer class="bg-mode py-3">
	<div class="container">
		<div class="row">
			<div class="col-md-6">
				<!-- Footer nav START -->
				<ul class="nav justify-content-center justify-content-md-start lh-1">
					<li class="nav-item">
						<a class="nav-link" href="{{route('about-page')}}">{{__('default.About')}}</a>
					</li>
{{--					<li class="nav-item">--}}
{{--						<a class="nav-link" href="{{route('onboarding-page')}}">{{__('default.Onboarding')}}</a>--}}
{{--					</li>--}}
					<li class="nav-item">
						<a class="nav-link" href="{{route('help-page')}}">{{__('default.Help')}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{route('terms-page')}}">{{__('default.Terms')}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{route('privacy-page')}}">{{__('default.Privacy')}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{route('change-log-page')}}">{{__('default.Change Log')}}</a>
					</li>
				</ul>
				<!-- Footer nav START -->
			</div>
			<div class="col-md-6">
				<!-- Copyright START -->
				<p class="text-center text-md-end mb-0">©2024 <a class="text-body" href="https://www.writebookswithai.com"> {{__('default.Write Books With AI')}} </a>{{__('default.All rights reserved.')}}</p>
				<!-- Copyright END -->
			</div>
		</div>
	</div>
</footer>
<!-- footer END -->


@include('layouts.modals')

<?php
