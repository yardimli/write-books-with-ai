@extends('layouts.app')

@section('title', 'All Books')

@section('content')
	
	<script>
		let bookData = @json($book);
		let bookSlug = "{{$book_slug}}";
	</script>
	<main class="pt-5">
		
		<!-- Container START -->
		<div class="container pt-4">
			<div class="row g-4">
				<div class="col-xl-4 col-lg-4 col-12 vstack gap-4">
					<div class="card">
						<div class="card-body py-3">
							<img src="{{$book['cover_filename']}}" alt="book" class="pb-1" style="min-height: 400px;">
							
							<button class="btn btn-success-soft mb-1 mt-1" id="exportPdfBtn" title="{{__('default.Export as PDF')}}">
								<i class="bi bi-file-earmark-pdf"></i> {{__('default.PDF')}}
							</button>
							
							<button class="btn btn-success-soft mb-1 mt-1" id="exportTxtBtn" title="{{__('default.Export as DocX')}}">
								<i class="bi bi-file-earmark-word"></i> {{__('default.DocX')}}
							</button>
						</div>
					</div>
				</div>
				
				<!-- Main content START -->
				<div class="col-xl-8 col-lg-8 col-12 vstack gap-4">
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
											<a
												href="{{route('showcase-library-genre',[$book['genre'] ?? ''])}}"
												class="nav-item small">{{$book['genre'] ?? ''}}</a>
										
										</div>
									</div>
								</div>
							</div>
							
							
							@php
								$chapter_counter = 0;
							@endphp
							@foreach ($book['acts'] as $act)
								@php
									#chapter_counter++;
								@endphp
								<h3>{{$act['title'] ?? 'Act'}}</h3>
								@foreach ($act['chapters'] as $chapter)
									<h4>{{$chapter['name'] ?? 'Chapter '.$chapter_counter}}</h4>
									@if (1===2)
										<p>{{$chapter['short_description'] ?? ''}}</p>
										<ul>
											<li><i>{{__('Events')}}</i>: {{$chapter['events'] ?? ''}}</li>
											<li><i>{{__('People')}}</i>: {{$chapter['people'] ?? ''}}</li>
											<li><i>{{__('Places')}}</i>: {{$chapter['places'] ?? ''}}</li>
										</ul>
									@endif
									@if (isset($chapter['beats']))
										@foreach ($chapter['beats'] as $beat)
											<p>{!! str_replace("\n","<br>",$beat['beat_text'] ?? '') !!}</p>
										@endforeach
									@endif
								@endforeach
							@endforeach
							
							<p class="mt-4">{!! str_replace("\n","<br>", $book['back_cover_text'] ?? '')!!}</p>
						</div>
					
					</div>
					
					<figure class="bg-light rounded p-3 p-sm-4 my-4">
						<blockquote class="blockquote" style="font-size: 14px;">
							<span class="strong">Blurb:</span><br>
							{{$book['blurb'] ?? ''}}
						</blockquote>
						
						<blockquote class="blockquote-footer text-dark-emphasis" style="font-size: 14px;">
							<span class="strong">Back Cover Text:</span><br>
							{{$book['back_cover_text'] ?? ''}}
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
	
	
	@include('layouts.footer')

@endsection

@push('scripts')
	<!-- Inline JavaScript code -->
	<script src="/js/jspdf.umd.min.js"></script>
	<script src="/js/docx.js"></script>
	
	<script>
		function exportAsPdf(bookStructure) {
			console.log(bookStructure);
			const {jsPDF} = window.jspdf;
			const doc = new jsPDF({
				unit: 'in',
				format: [6, 9]
			});
			
			// Load a Unicode font
			doc.addFont('/assets/fonts/NotoSans-Regular.ttf', 'NotoSans', 'normal');
			doc.addFont('/assets/fonts/NotoSans-Bold.ttf', 'NotoSans', 'bold');
			doc.addFont('/assets/fonts/NotoSans-Italic.ttf', 'NotoSans', 'italic');
			
			// Set default font to Roboto
			doc.setFont('NotoSans', 'normal');
			
			// Set font to a serif font
			// doc.setFont('times', 'normal');
			
			const lineHeight = 0.25; // Increased line height
			let yPosition = 0.75; // Increased top margin
			const pageHeight = 8.5;
			const pageWidth = 6;
			const margin = 0.75; // Increased side margins
			let pageNumber = 1;
			let currentFontSize = 12;
			let currentFontStyle = 'normal';
			
			function setFont(fontSize = 12, isBold = false) {
				currentFontSize = fontSize;
				currentFontStyle = isBold ? 'bold' : 'normal';
				doc.setFontSize(fontSize);
				// doc.setFont('times', currentFontStyle);
				doc.setFont('NotoSans', currentFontStyle);
			}
			
			function addText(text, fontSize = 12, isBold = false, align = 'left') {
				setFont(fontSize, isBold);
				const splitText = doc.splitTextToSize(text, pageWidth - 2 * margin);
				splitText.forEach(line => {
					if (yPosition > pageHeight - margin) {
						addPageNumber();
						doc.addPage();
						yPosition = margin;
						pageNumber++;
						setFont(currentFontSize, currentFontStyle === 'bold');
					}
					
					doc.text(line, align === 'center' ? pageWidth / 2 : margin, yPosition, {align: align});
					
					yPosition += lineHeight;
				});
				yPosition += 0.2; // Add a small gap after each text block
			}
			
			function addPageBreak() {
				addPageNumber();
				doc.addPage();
				yPosition = margin;
				pageNumber++;
				setFont(currentFontSize, currentFontStyle === 'bold');
			}
			
			function addPageNumber() {
				const currentFont = doc.getFont();
				const currentFontSize = doc.getFontSize();
				doc.setFontSize(10);
				doc.setFont('NotoSans', 'normal');
				doc.text(String(pageNumber), pageWidth - margin + 0.2, pageHeight - margin + 0.4, {align: 'right'});
				doc.setFontSize(currentFontSize);
				doc.setFont(currentFont.fontName, currentFont.fontStyle);
			}
			
			
			function addCenteredPage(text, fontSize = 18, isBold = true) {
				addPageBreak();
				setFont(fontSize, isBold);
				const textLines = doc.splitTextToSize(text, pageWidth - 2 * margin);
				const textHeight = textLines.length * lineHeight;
				const startY = (pageHeight - textHeight) / 2;
				doc.text(textLines, pageWidth / 2, startY, {align: 'center'});
			}
			
			// Title
			addCenteredPage(bookStructure.title, 18, true);
			
			// Blurb
			addCenteredPage(bookStructure.blurb, 14, true);
			addPageBreak();
			
			// Back Cover Text
			addText(bookStructure.back_cover_text, 14, false);
			addPageBreak();
			
			bookStructure.acts.forEach((act, actIndex) => {
				if (bookStructure.language === 'Turkish') {
					act.title = act.title.replace('Act', 'Perde');
				}
				
				addCenteredPage(`${act.title}`); //Act ${actIndex + 1}:
				act.chapters.forEach((chapter, chapterIndex) => {
					addPageBreak();
					
					if (bookStructure.language === 'Turkish') {
						chapter.name = chapter.name.replace('Chapter', 'Bölüm');
					}
					
					// Chapter title
					addText(chapter.name, 14, true);
					
					// Beats
					if (chapter.beats && chapter.beats.length > 0) {
						
						chapter.beats.forEach((beat, beatIndex) => {
							if (beat.beat_text) {
								addText(beat.beat_text);
								// addText('____________________');
								addText('');
							}
						});
					}
				});
			});
			
			addPageNumber(); // Add page number to the last page
			let simpleFilename = bookStructure.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
			doc.save(simpleFilename + '.pdf');
		}
		
		async function exportAsDocx(bookStructure) {
			console.log(bookStructure);
			
			const {Document, Packer, Paragraph, TextRun, HeadingLevel, AlignmentType, PageBreak} = docx;
			
			let doc_children = [];
			
			function addText(text, size = 24, bold = false, alignment = AlignmentType.LEFT) {
				doc_children.push(new Paragraph({
					alignment: alignment,
					spacing: {
						line: 1.5 * 240
					},
					children: [
						new TextRun({
							text: text,
							bold: bold,
							size: size
						})
					]
				}));
			}
			
			function addPageBreak() {
				doc_children.push(new Paragraph({
					children: [new PageBreak()]
				}));
			}
			
			function addCenteredPage(text, size = 36, bold = true) {
				addText('');
				addText('');
				addText('');
				addText('');
				addText('');
				addText('');
				addText('');
				addText('');
				addText(text, size, bold, AlignmentType.CENTER);
			}
			
			// Title
			addCenteredPage(bookStructure.title);
			addPageBreak();
			
			// Blurb
			addText('');
			addText('');
			addText('');
			addText('');
			addText(bookStructure.blurb, 28, false, AlignmentType.JUSTIFIED);
			addPageBreak();
			
			// Back Cover Text
			addText(bookStructure.back_cover_text, 28, false, AlignmentType.JUSTIFIED);
			addPageBreak();
			
			bookStructure.acts.forEach((act, actIndex) => {
				if (bookStructure.language === 'Turkish') {
					act.title = act.title.replace('Act', 'Perde');
				}
				
				addCenteredPage(`${act.title}`);
				addPageBreak();
				
				act.chapters.forEach((chapter, chapterIndex) => {
					if (bookStructure.language === 'Turkish') {
						chapter.name = chapter.name.replace('Chapter', 'Bölüm');
					}
					
					// Chapter title
					addText('');
					addText(chapter.name, 32, true, AlignmentType.CENTER);
					addText('');
					addText('');
					
					// Beats
					if (chapter.beats && chapter.beats.length > 0) {
						chapter.beats.forEach((beat, beatIndex) => {
							if (beat.beat_text) {
								let beat_texts = beat.beat_text.split('\n');
								beat_texts.forEach((beat_text) => {
									addText(beat_text, 24, false, AlignmentType.JUSTIFIED);
									// addText('');
								});
							}
						});
					}
					
					addPageBreak();
					
					
				});
			});
			
			// Generate and save the document
			
			const doc = new Document({
				sections: [{
					properties: {
						page: {
							size: {
								width: 6 * 1440, // 6 inches in twips (1 inch = 1440 twips)
								height: 9 * 1440, // 9 inches in twips
							},
						},
					},
					children: doc_children
				}]
			});
			
			const blob = await Packer.toBlob(doc);
			const url = URL.createObjectURL(blob);
			const link = document.createElement('a');
			link.href = url;
			let simpleFilename = bookStructure.title.replace(/[^a-z0-9]/gi, '_').toLowerCase();
			link.download = simpleFilename + '.docx';
			document.body.appendChild(link);
			link.click();
			document.body.removeChild(link);
			URL.revokeObjectURL(url);
		}
		
		var current_page = 'read_book';
		$(document).ready(function () {
			
			$('#exportPdfBtn').on('click', function (e) {
				e.preventDefault();
				exportAsPdf(bookData);
			});
			
			$('#exportTxtBtn').on('click', function (e) {
				e.preventDefault();
				exportAsDocx(bookData);
			});
			
			
		});
	</script>

@endpush
