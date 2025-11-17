@php
	$host = request()->getHttpHost();
@endphp

@if (!str_contains($host, 'localhost') && !str_contains($host, 'staging') && (1===2))
	<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	
	gtag('config', 'G-');
</script>
@endif
