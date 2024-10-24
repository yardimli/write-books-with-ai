<!DOCTYPE html>
<html lang="en">
<head>
	<title>{{__('default.Write Books With AI')}} - {{__('default.Your Story, Our AI - Write Books Faster, Smarter, Better with AI')}}</title>
	
	<!-- Meta Tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="author" content="Webestica.com">
	<meta name="description"
	      content="{{__('default.Write Books With AI')}} - {{__('default.Your Story, Our AI - Write Books Faster, Smarter, Better with AI')}}">
	
	<!-- Dark mode -->
	<script>
		const storedTheme = localStorage.getItem('theme')
		
		const getPreferredTheme = () => {
			if (storedTheme) {
				return storedTheme
			}
			return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light'
		}
		
		const setTheme = function (theme) {
			if (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches) {
				document.documentElement.setAttribute('data-bs-theme', 'dark')
			} else {
				document.documentElement.setAttribute('data-bs-theme', theme)
			}
		}
		
		setTheme(getPreferredTheme())
		
		window.addEventListener('DOMContentLoaded', () => {
			var el = document.querySelector('.theme-icon-active');
			if (el != 'undefined' && el != null) {
				const showActiveTheme = theme => {
					const activeThemeIcon = document.querySelector('.theme-icon-active use')
					const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`)
					const svgOfActiveBtn = btnToActive.querySelector('.mode-switch use').getAttribute('href')
					
					document.querySelectorAll('[data-bs-theme-value]').forEach(element => {
						element.classList.remove('active')
					})
					
					btnToActive.classList.add('active')
					activeThemeIcon.setAttribute('href', svgOfActiveBtn)
				}
				
				window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
					if (storedTheme !== 'light' || storedTheme !== 'dark') {
						setTheme(getPreferredTheme())
					}
				})
				
				showActiveTheme(getPreferredTheme())
				
				document.querySelectorAll('[data-bs-theme-value]')
					.forEach(toggle => {
						toggle.addEventListener('click', () => {
							const theme = toggle.getAttribute('data-bs-theme-value')
							localStorage.setItem('theme', theme)
							setTheme(theme)
							showActiveTheme(theme)
						})
					})
				
			}
		})
	
	</script>
	
	<!-- Favicon -->
	<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
	<link rel="manifest" href="/site.webmanifest">
	
	<!-- Google Font -->
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
	
	<!-- Plugins CSS -->
	<link rel="stylesheet" type="text/css" href="/assets/vendor/font-awesome/css/all.min.css">
	<link rel="stylesheet" type="text/css" href="/assets/vendor/bootstrap-icons/bootstrap-icons.css">
	<link rel="stylesheet" type="text/css" href="/assets/vendor/plyr/plyr.css">
	
	<!-- Theme CSS -->
	<link rel="stylesheet" type="text/css" href="/assets/css/style.css">

</head>
<body>

<!-- =======================
Header START -->

<header class="navbar-light header-static bg-transparent">
	<!-- Navbar START -->
	<nav class="navbar navbar-expand-lg">
		<div class="container">
			<!-- Logo START -->
			<a class="navbar-brand" href="{{ route('login') }}">
				<img class="light-mode-item navbar-brand-item" src="/images/logo.png" alt="logo">
				<img class="dark-mode-item navbar-brand-item" src="/images/logo.png" alt="logo">
			</a>
			<!-- Logo END -->
			
			<!-- Responsive navbar toggler -->
			<button class="navbar-toggler ms-auto icon-md btn btn-light p-0" type="button" data-bs-toggle="collapse"
			        data-bs-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false"
			        aria-label="Toggle navigation">
        <span class="navbar-toggler-animation">
          <span></span>
          <span></span>
          <span></span>
        </span>
			</button>
			
			<!-- Main navbar START -->
			<div class="collapse navbar-collapse" id="navbarCollapse">
				<ul class="navbar-nav navbar-nav-scroll me-auto">
					<!-- Nav item -->
					<li class="nav-item">
						<a class="nav-link" href="{{route('login')}}">{{__('default.Login')}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link" href="{{route('register')}}">{{__('default.Register')}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" href="{{route('binshopsblog.index',['en_US'])}}">{{__('default.Blog')}}</a>
					</li>
					<li class="nav-item">
						<a class="nav-link active" href="{{route('showcase-library')}}">{{__('default.Showcase Library')}}</a>
					</li>
				</ul>
			</div>
			<!-- Main navbar END -->
			
			<!-- Nav right START -->
			<div class="ms-3 ms-lg-auto">
				{{--          <a class="btn btn-dark" href="app-download.html"> Download app </a>--}}
			</div>
			<!-- Nav right END -->
		</div>
	</nav>
	<!-- Navbar END -->
</header>

<!-- =======================
Header END -->

<main>
	
	<!-- **************** MAIN CONTENT START **************** -->
	
	<!-- Main banner START -->
	<section class="pt-3 pb-0 position-relative">
		
		<!-- Container START -->
		<div class="container">
			<!-- Row START -->
			<div class="row text-center position-relative z-index-1">
				<div class="col-lg-7 col-12 mx-auto">
					<!-- Heading -->
					<h1 class="display-4">{{__('default.Write Books With AI')}}</h1>
					<p class="lead">"{{__('default.Your Story, Our AI - Write Books Faster, Smarter, Better with AI')}}"</p>
					<div class="d-sm-flex justify-content-center">
						<!-- button -->
						<a href="{{route('register')}}" class="btn btn-primary">{{__('default.Sign up')}}</a>
						<div class="mt-2 mt-sm-0 ms-sm-3">
							<!-- Rating START -->
							<div class="hstack justify-content-center justify-content-sm-start gap-1">
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star text-warning"></i></div>
								<div><i class="fa-solid fa-star-half-stroke text-warning"></i></div>
							</div>
							<!-- Rating END -->
							<i>"{{__('default.I can\'t believe it\'s free!')}}"</i>
						</div>
					</div>
					<br>
				</div>
			</div>
			<!-- Row END -->
		</div>
		<!-- Container END -->
		
		<!-- Svg decoration START -->
		<div class="position-absolute top-0 end-0 mt-5 pt-5">
			<img class="h-300px blur-9 mt-5 pt-5" src="/assets/images/elements/07.svg" alt="">
		</div>
		<div class="position-absolute top-0 start-0 mt-n5 pt-n5">
			<img class="h-300px blur-9" src="/assets/images/elements/01.svg" alt="">
		</div>
		<div class="position-absolute top-50 start-50 translate-middle">
			<img class="h-300px blur-9" src="/assets/images/elements/04.svg" alt="">
		</div>
		<!-- Svg decoration END -->
	
	</section>
	<!-- Main banner END -->
	
	<!-- Messaging feature START -->
	<section>
		<div class="container">
			<div class="row justify-content-center">
				<!-- Title -->
				<div class="col-lg-7 col-12 mx-auto  text-center mb-4">
					<h2 class="h1">{{__('default.Craft Your Novel and Short Stories')}}</h2>
					<p>{{__('default.Within a few steps create your story by choosing genre, reviewing book and character details.')}}</p>
				</div>
			</div>
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">{{__('default.Start Your Book')}}</h4>
						<p class="mb-3">{{__('default.Write your book description and choose the structure, AI model, and language. Set up the genre, writing style, and narrative. Fill in author details, then click Submit to start.')}}</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/add-book-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			<!-- Row START -->
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">{{__('default.Go over the AI\'s suggestions to your story.')}}</h4>
						<p class="mb-3">{{__('default.After the first step now the AI has the book title, a blurb and a back cover text written for you. It also has character profiles for the book. Here you can edit these to your liking before moving to the next step that will start writing the content of your book.')}}</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/add-book-step-2-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">{{__('default.Review the chapters.')}}</h4>
						<p class="mb-3">{{__('default.Now the overview of each chapter is written. They all have name, description, event, people, places as well as how they link to the previous or next chapter. Your job is to review the texts, verify that the story follows a smooth path, that events, people and places are as they should be.')}}</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/book-chapters-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">{{__('default.Time the beats.')}}</h4>
						<p class="mb-3">{{__('default.Chapters done, it\'s time to fill them up. Add as many beats as you want. Let the AI write short descriptions for each beat. Verify them and then fill them out together with the AI. Also don\'t forget to update the Codex along the way.')}}</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/chapter-beats-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
			
			
			<!-- Row START -->
			<div class="row justify-content-center">
				<!-- Feature START -->
				<div class="col-lg-9 col-12 mx-auto  text-center mb-4">
					<div class="card card-body bg-mode shadow-none border-1">
						<!-- Info -->
						<h4 class="mt-0 mb-3">{{__('default.Your book is ready to be read.')}}</h4>
						<p class="mb-3">{{__('default.Everything is done, you have your chapters and beats, you have a good book cover. Ready to export and publish your book!<br>Good Job!')}}</p>
					</div>
					<img class="mb-4 mt-4" src="/images/screenshot/edit-book-dark.png" alt="">
				</div>
				<!-- Feature END -->
			</div>
		</div>
		
		<div style="text-align: center;">
		<a href="https://www.producthunt.com/posts/write-books-with-ai?embed=true&utm_source=badge-featured&utm_medium=badge&utm_souce=badge-write&#0045;books&#0045;with&#0045;ai" target="_blank"><img src="https://api.producthunt.com/widgets/embed-image/v1/featured.svg?post_id=493674&theme=neutral" alt="Write&#0032;Books&#0032;with&#0032;AI - Your&#0032;Story&#0032;Our&#0032;AI&#0044;&#0032;Write&#0032;Books&#0032;Faster&#0032;Smarter&#0032;Better&#0032;with&#0032;AI | Product Hunt" style="width: 250px; height: 54px;" width="250" height="54" /></a>
		</div>
	</section>
	<!-- Messaging feature END -->
	
	
	
	<!-- Main content END -->
</main>
<!-- **************** MAIN CONTENT END **************** -->
{{--<script src="https://everperfectassistant.com/chat/chat.js?id=Gy4nA4OB5o"></script>--}}

@include('layouts.footer')

<!-- =======================
JS libraries, plugins and custom scripts -->

<!-- Bootstrap JS -->
<script src="/assets/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

<!-- Vendors -->
<script src="/assets/vendor/plyr/plyr.js"></script>

<!-- Theme Functions -->
<script src="/assets/js/functions.js"></script>

</body>
</html>
