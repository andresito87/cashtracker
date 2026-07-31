<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ config('app.name', 'CashTracker') }} - @yield('title')</title>

	<meta name="csrf-token" content="{{ csrf_token() }}">

	<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

	@fonts

	<!-- Styles / Scripts -->
	@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
		@vite(['resources/css/app.css', 'resources/js/app.js'])
	@endif
</head>
<body class="min-h-screen flex flex-col font-sans select-none bg-gray-50 text-gray-900">

<header
	class="bg-[#1b0e35] border-b border-purple-900/30 text-white sticky top-0 z-50 shadow-sm backdrop-blur-md bg-opacity-95 select-none">
	<div class="w-full px-3 sm:px-6 lg:px-8">
		<div class="flex justify-between items-center h-14 sm:h-20">
			<!-- Logo -->
			<div class="shrink-0 flex items-center">
				<a href="{{ route('welcome') }}"
				   class="flex items-center gap-2 group transition-transform duration-200 active:scale-95">
					<img src="{{ asset('logo.png') }}" alt="CashTracker Logo" class="h-7 sm:h-10 w-auto">
				</a>
			</div>

			<!-- Header Action Area -->
			<div class="flex items-center gap-1.5 sm:gap-4 shrink-0">
				<!-- Navigation Links -->
				<nav class="flex items-center gap-1.5 sm:gap-3">
					@auth
						<x-subscription-badge/>
						<x-user-menu/>
					@else
						@unless(request()->routeIs('welcome'))
							<a href="{{ route('login') }}"
							   class="text-xs sm:text-sm font-semibold text-orange-400 hover:text-white bg-transparent hover:bg-orange-500/10 border border-orange-500/40 hover:border-orange-500/80 px-2 py-1 sm:px-4 sm:py-2 rounded-lg transition-all duration-200 active:scale-95">
								{{ __('messages.login') }}
							</a>
							<a href="{{ route('register') }}"
							   class="text-xs sm:text-sm font-semibold text-white bg-linear-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 px-2 py-1 sm:px-4 sm:py-2 rounded-lg shadow-md shadow-orange-500/10 hover:shadow-orange-500/25 transition-all duration-200 active:scale-95">
								{{ __('messages.register') }}
							</a>
						@endunless
					@endauth
				</nav>

				<!-- Language Switcher -->
				<a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'es' ? 'en' : 'es']) }}"
				   class="flex items-center bg-[#13082a] p-0.5 sm:p-1 rounded-full border border-purple-500/40 shrink-0 shadow-inner hover:border-purple-400/70 transition-all duration-200 cursor-pointer"
				   title="{{ app()->getLocale() === 'es' ? __('messages.switch_to_english') : __('messages.switch_to_spanish') }}">
					<span
						class="px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-bold transition-all duration-200 {{ app()->getLocale() === 'es' ? 'bg-purple-600/60 text-white shadow-sm' : 'text-purple-300/50' }}">
						ES
					</span>
					<span
						class="px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-bold transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-purple-600/60 text-white shadow-sm' : 'text-purple-300/50' }}">
						EN
					</span>
				</a>
			</div>
		</div>
	</div>
</header>

<!-- Page Content -->
<main class="grow">
	@yield('contents')
</main>

<!-- Footer -->
<footer
	class="py-6 sm:py-8 bg-[#1b0e35] border-t border-purple-900/30 text-center text-xs text-purple-200/70 mt-auto select-none">
	<div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
		<div class="flex items-center">
			<a href="{{ route('welcome') }}" class="inline-block transition-opacity hover:opacity-100 opacity-90">
				<img src="{{ asset('logo.png') }}" alt="CashTracker Logo" class="h-6 w-auto">
			</a>
		</div>
		<p>&copy; {{ date('Y') }} CashTracker. {{ __('messages.welcome_subtitle') }}</p>
	</div>
</footer>

<script>
	document.addEventListener('DOMContentLoaded', function () {
		// 1. Guard against duplicate form submissions
		document.querySelectorAll('form').forEach(function (form) {
			form.addEventListener('submit', function (_e) {
				// Block all other navigation clicks once a form is submitting
				document.body.setAttribute('data-navigating', 'true');

				const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
				submitButtons.forEach(function (button) {
					// Disable button immediately to prevent double submissions
					button.disabled = true;
					button.style.pointerEvents = 'none';
					button.classList.add('opacity-75', 'cursor-not-allowed');

					// Dynamically update text & add loading spinner
					const loadingText = button.getAttribute('data-loading-text');
					if (loadingText) {
						const spinner = '<svg class="animate-spin -ml-1 mr-2 h-5 w-5 text-white inline-block shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';
						if (button.tagName === 'INPUT') {
							button.value = loadingText;
						} else {
							button.innerHTML = spinner + '<span>' + loadingText + '</span>';
						}
					}
				});
			});
		});

		// 2. Guard against multiple clicks on header navigation links and switcher
		document.querySelectorAll('header a, nav a').forEach(function (link) {
			link.addEventListener('click', function (e) {
				const href = link.getAttribute('href');
				if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) {
					return;
				}

				if (document.body.getAttribute('data-navigating') === 'true') {
					e.preventDefault();
					return false;
				}

				document.body.setAttribute('data-navigating', 'true');
				link.style.pointerEvents = 'none';
				link.style.opacity = '0.6';
			});
		});
	});
</script>

</body>
</html>
