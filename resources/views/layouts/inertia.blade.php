<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>{{ config('app.name', 'CashTracker') }}</title>

	<link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

	@fonts

	<meta name="csrf-token" content="{{ csrf_token() }}">
	@routes
	@viteReactRefresh
	<x-inertia::head/>

	@routes
	@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
		@vite(['resources/css/app.css', 'resources/js/inertia.tsx'])
	@endif

	<script src="https://cdn.jsdelivr.net/npm/@tailwindplus/elements@1" type="module"></script>
</head>

<body class="min-h-screen flex flex-col font-sans select-none bg-gray-50 text-gray-900">
<header
	class="bg-[#1b0e35] border-b border-purple-900/30 text-white sticky top-0 z-50 shadow-sm backdrop-blur-md bg-opacity-95 select-none">
	<div class="w-full px-3 sm:px-6 lg:px-8">
		<div class="flex justify-between items-center h-14 sm:h-20">
			<div class="shrink-0 flex items-center">
				<a href="{{ route('dashboard') }}"
				   class="flex items-center gap-2 group transition-transform duration-200 active:scale-95">
					<img src="{{ asset('logo.png') }}" alt="CashTracker Logo" class="h-7 sm:h-10 w-auto">
				</a>
			</div>
			<div class="flex items-center gap-1.5 sm:gap-4 shrink-0">
				@auth
					<x-subscription-badge/>
					<x-user-menu/>
				@endauth

				<!-- Language Switcher -->
				@include('components.lang-switcher')
			</div>
		</div>
	</div>
</header>

<main class="grow max-w-5xl mx-auto p-5 lg:p-10 w-full">
	<x-inertia::app/>
</main>

<footer
	class="py-6 sm:py-8 bg-[#1b0e35] border-t border-purple-900/30 text-center text-xs text-purple-200/70 mt-auto select-none">
	<div class="w-full px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4">
		<div class="flex items-center">
			<a href="{{ route('dashboard') }}" class="inline-block transition-opacity hover:opacity-100 opacity-90">
				<img src="{{ asset('logo.png') }}" alt="CashTracker Logo" class="h-6 w-auto">
			</a>
		</div>
		<p>&copy; {{ date('Y') }} CashTracker.</p>
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
</script>
</body>
</html>

