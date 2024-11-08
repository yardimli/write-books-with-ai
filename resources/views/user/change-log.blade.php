@extends('layouts.app')

@section('title', 'Change Log')

@section('content')
	
	<!-- **************** MAIN CONTENT START **************** -->
	<main>
			<!-- Container START -->
		<div class="container" style="min-height: calc(88vh);">
			<div class="row g-4">
				<!-- Main content START -->
				<div class="col-lg-8 mx-auto">
					<!-- Card START -->
					<div class="card">
						<div class="card-header py-3 border-0 d-flex align-items-center justify-content-between">
							<h1 class="h5 mb-0">Change Log</h1>
						</div>
						<div class="card-body p-3 mb-3">
							<div style="text-align: center; ">
								<img src="{{ asset('/images/logo-big.png') }}"
								     style="max-width: 300px; width: 300px;" alt="Thank You" class="img-fluid">
							</div>
							
							<br>
							<strong>v0.1.0 - 09/28/2024</strong>
							<br>
							- Change the Write Books with AI's UI, so except the showcase all books are private. <br>There is no need for users to publish their stories on the site.
							<br>
							- Add Blog code.
							
							<br><br>
							<strong>v0.1.1 - 09/29/2024</strong>
							<br>
							- Improve the UI for viewing the library and reading the books.

							<br><br>
							<strong>v0.1.2 - 09/30/2024</strong>
							<br>
							- Allow users to insert empty beats after existing beats and at the beginning of the chapter.
							
							<br><br>
							<strong>v0.1.3 - 10/01/2024</strong>
							<br>
							- Frontend UI improvements.<br>
							- Add two new structures that are better for short stories.<br>
							- Delete book feature.
							
							<br><br>
							<strong>v0.1.4 - 10/05/2024</strong>
							<br>
							- Add option to change writing style and narrative style later in the writing process.<br>
							- Anthropic system prompt bug fix.<br>
							
							
							<br><br>
							<strong>v0.1.5 - 10/10/2024</strong>
							<br>
							- Split lore into codex with four parts.<br>
							- Add diff tool for comparing original and updated version of the codex.<br>
							- Improve the UI for reading the books.<br>
							- Start writing bug fix.<br>
							- change to allow to use all models from openRouter<br>
							- more error handling<br>
							- add modals to preview and edit prompts before sending to the model<br>
							
							<br><br>
							<strong>v0.1.6 - 10/12/2024</strong>
							<br>
							- Add intro.js for onboarding.<br>
							- Bug fixes.<br>
							- Allow users to input their own API key for OpenAI, Anthropic, and Open Router.<br>
							
							<br><br>
							<strong>v0.1.7 - 10/17/2024</strong>
							<br>
							- Move all texts to lang files. For translation of UI.<br>
							- Update the change log page. with all the changes since v0.1.2<br>
							
							<br><br>
							<strong>v0.1.8 - 10/19/2024</strong>
							<br>
							- Add UGI (Uncensored Index) and writing index to model list.<br>
							- Move Blurb, Back Cover Text, Prompt and Character Profiles to modal.<br>
							- Remember beats per chapter setting.<br>
							- Add "Edit All Beats" button.<br>
							
							<br><br>
							<strong>v0.1.9 - 11/03/2024</strong>
							<br>
							- Move beats per chapter to book creation.<br>
						
						</div>
					</div>
					<!-- Card END -->
				</div>
			</div> <!-- Row END -->
		</div>
		<!-- Container END -->
	
	</main>
	
	
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script>
      var current_page = 'my.change-log';
      $(document).ready(function () {
      });
	</script>
	
@endpush
