<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ config('app.name', 'CashTracker') }} - @yield('title', __('messages.dashboard'))</title>

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
	<div class="w-full px-4 sm:px-6 lg:px-8">
		<div class="flex justify-between items-center h-16 sm:h-20">
			<!-- Logo -->
			<div class="shrink-0 flex items-center">
				<a href="{{ route('dashboard') }}"
				   class="flex items-center gap-2 group transition-transform duration-200 active:scale-95">
					<img src="{{ asset('logo.png') }}" alt="CashTracker Logo" class="h-8 sm:h-10 w-auto">
				</a>
			</div>

			<!-- User Profile & Header Actions -->
			<div class="flex items-center space-x-3 sm:space-x-5">
				<x-user-menu/>

				<!-- Language Switcher -->
				<a href="{{ request()->fullUrlWithQuery(['lang' => app()->getLocale() === 'es' ? 'en' : 'es']) }}"
				   class="flex items-center bg-[#13082a] p-1 rounded-full border border-purple-500/40 shrink-0 shadow-inner hover:border-purple-400/70 transition-all duration-200 cursor-pointer"
				   title="{{ app()->getLocale() === 'es' ? __('messages.switch_to_english') : __('messages.switch_to_spanish') }}">
					<span
						class="px-3 py-1 rounded-full text-xs sm:text-sm font-bold transition-all duration-200 {{ app()->getLocale() === 'es' ? 'bg-purple-600/60 text-white shadow-sm' : 'text-purple-300/50' }}">
						ES
					</span>
					<span
						class="px-3 py-1 rounded-full text-xs sm:text-sm font-bold transition-all duration-200 {{ app()->getLocale() === 'en' ? 'bg-purple-600/60 text-white shadow-sm' : 'text-purple-300/50' }}">
						EN
					</span>
				</a>
			</div>
		</div>
	</div>
</header>

<!-- Main Page Content -->
<main class="grow">
	@yield('contents')
</main>

<!-- Footer -->
<footer
	class="py-6 sm:py-8 bg-[#1b0e35] border-t border-purple-900/30 text-center text-xs text-purple-200/70 mt-auto select-none">
	<div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
		<div class="flex items-center">
			<a href="{{ route('dashboard') }}" class="inline-block transition-opacity hover:opacity-100 opacity-90">
				<img src="{{ asset('logo.png') }}" alt="CashTracker Logo" class="h-6 w-auto">
			</a>
		</div>
		<p>&copy; {{ date('Y') }} CashTracker. {{ __('messages.welcome_subtitle') }}</p>
	</div>
</footer>

<script>
	(function () {
		function setupUserMenu() {
			const menuBtn = document.getElementById('user-menu-button');
			const menuDropdown = document.getElementById('user-menu-dropdown');
			const chevron = document.getElementById('user-menu-chevron');

			if (!menuBtn || !menuDropdown) return;

			function toggleMenu(show) {
				if (show) {
					menuDropdown.classList.remove('hidden');
					menuBtn.setAttribute('aria-expanded', 'true');
					if (chevron) chevron.classList.add('rotate-180');
				} else {
					menuDropdown.classList.add('hidden');
					menuBtn.setAttribute('aria-expanded', 'false');
					if (chevron) chevron.classList.remove('rotate-180');
				}
			}

			if (menuBtn.hasAttribute('data-has-listener')) return;
			menuBtn.setAttribute('data-has-listener', 'true');

			menuBtn.addEventListener('click', function (e) {
				e.stopPropagation();
				const isHidden = menuDropdown.classList.contains('hidden');
				toggleMenu(isHidden);
			});

			document.addEventListener('click', function (e) {
				if (!menuDropdown.contains(e.target) && !menuBtn.contains(e.target)) {
					toggleMenu(false);
				}
			});

			document.addEventListener('keydown', function (e) {
				if (e.key === 'Escape') {
					toggleMenu(false);
				}
			});

			const menuItems = menuDropdown.querySelectorAll('a, button[type="submit"]');
			menuItems.forEach(function (item) {
				item.addEventListener('click', function (e) {
					if (item.hasAttribute('data-clicked')) {
						e.preventDefault();
						e.stopPropagation();
						return false;
					}

					item.setAttribute('data-clicked', 'true');

					menuItems.forEach(function (el) {
						el.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
					});

					menuBtn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');

					const svgIcon = item.querySelector('svg');
					if (svgIcon) {
						const spinner = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
						spinner.setAttribute('class', 'animate-spin w-4 h-4 text-purple-300 shrink-0');
						spinner.setAttribute('fill', 'none');
						spinner.setAttribute('viewBox', '0 0 24 24');
						spinner.innerHTML = '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>';
						svgIcon.replaceWith(spinner);
					}
				});
			});
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', setupUserMenu);
		} else {
			setupUserMenu();
		}
	})();

	document.addEventListener('DOMContentLoaded', function () {

		// Double submission guard
		document.querySelectorAll('form').forEach(function (form) {
			form.addEventListener('submit', function (_e) {
				document.body.setAttribute('data-navigating', 'true');

				const submitButtons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
				submitButtons.forEach(function (button) {
					button.disabled = true;
					button.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');

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

				form.querySelectorAll('a, button:not([type="submit"])').forEach(function (el) {
					el.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
				});
			});
		});
	});
</script>

</body>
</html>
