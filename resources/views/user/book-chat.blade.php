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


<script>
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
	
	function showFormatHints() {
		return `Required format:
###### order
[Chapter Sort Order here]
###### name
[Chapter name here]
###### short_description
[Short description here]
###### events
[Events here]
###### people
[People here]
###### places
[Places here]
###### from_previous_chapter
[Previous chapter connection here]
###### to_next_chapter
[Next chapter connection here]`;
	}
	
	function showFormatHelp() {
		$("#alertModalContent").html(`
        <h5>{{__('default.Chapter Details Format')}}</h5>
        <pre>${showFormatHints()}</pre>
    `);
		$("#alertModal").modal({backdrop: 'static', keyboard: true}).modal('show');
	}
	
	
	$(document).ready(function () {
		toggleIntroJsStylesheet();
		
		// Start the tour if it's the user's first timew
		// if (!localStorage.getItem('editBookIntroCompleted')) {
		// 	setTimeout(function () {
		// 		startIntro();
		// 	}, 500);
		// }
		
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
