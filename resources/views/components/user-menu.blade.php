@auth
	<div class="relative inline-block text-left shrink-0" id="user-menu-container">
		<button type="button"
		        id="user-menu-button"
		        aria-expanded="false"
		        aria-haspopup="true"
		        class="flex items-center gap-1.5 min-[480px]:gap-2 text-xs sm:text-sm font-medium text-gray-100 hover:text-white bg-purple-950/60 hover:bg-purple-900/80 border border-purple-700/40 px-2 py-1.5 min-[480px]:px-3 min-[480px]:py-2 rounded-xl transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-purple-500/50 shadow-sm active:scale-95">
			<!-- User Avatar Icon -->
			<span
				class="flex items-center justify-center w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-purple-600/40 border border-purple-400/30 text-purple-200 shrink-0">
				<svg class="w-3.5 h-3.5 sm:w-4 sm:h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
					<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
					      d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
				</svg>
			</span>

			<!-- User Name (shows early from 480px+ with smooth proportional truncation) -->
			<span
				class="hidden min-[480px]:inline font-semibold text-white tracking-wide truncate max-w-18.75 min-[540px]:max-w-27.5 sm:max-w-35 md:max-w-45">
				{{ auth()->user()->name }}
			</span>

			<!-- Chevron Down Icon -->
			<svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-300 transition-transform duration-200 shrink-0"
			     id="user-menu-chevron" fill="none"
			     stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
			</svg>
		</button>

		<!-- Dropdown Panel -->
		<div id="user-menu-dropdown"
		     class="hidden absolute right-0 mt-2 w-60 rounded-2xl bg-[#1d0d3a] border border-purple-800/60 shadow-2xl shadow-purple-950/90 p-1.5 text-sm text-gray-200 z-50 divide-y transition-all duration-150 origin-top-right">

			<!-- User Profile Info Header inside Dropdown -->
			<div class="px-3.5 py-2.5 border-b border-purple-800/50 mb-1 bg-purple-950/40 rounded-t-xl">
				<p class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</p>
				<p class="text-xs text-purple-300/80 truncate mt-0.5">{{ auth()->user()->email }}</p>
			</div>

			<div class="space-y-0.5 pb-1">
				<a href="{{ route('dashboard') }}"
				   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-gray-200 hover:bg-purple-800/50 hover:text-white transition-all duration-150 active:scale-95">
					<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
						      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/>
					</svg>
					<span>{{ __('messages.dashboard') }}</span>
				</a>

				@if(auth()->user()->isAdmin())
					<a href="{{ route('admin.dashboard') }}"
					   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-gray-200 hover:bg-purple-800/50 hover:text-white transition-all duration-150 active:scale-95">
						<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							      d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
						</svg>
						<span>{{ __('messages.admin_panel') }}</span>
					</a>
				@endif

				<a href="{{ route('settings.profile') }}"
				   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-gray-200 hover:bg-purple-800/50 hover:text-white transition-all duration-150 active:scale-95">
					<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
						      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
						      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
					</svg>
					<span>{{ __('messages.settings') }}</span>
				</a>

				<a href="{{ route('settings.password') }}"
				   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-gray-200 hover:bg-purple-800/50 hover:text-white transition-all duration-150 active:scale-95">
					<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
						      d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 0121 9z"/>
					</svg>
					<span>{{ __('messages.change_password') }}</span>
				</a>

				{{-- Subscription Management Link just for subscribed users --}}
				@if(auth()->user()->subscribed())
					<a href="{{ route('subscription.manage') }}"
					   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-gray-200 hover:bg-purple-800/50 hover:text-white transition-all duration-150 active:scale-95">
						<svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							      d="M3 10h18M7 15h1m4 0h1m-7 4h12a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
						</svg>
						<span>{{ __('messages.manage_subscription') }}</span>
					</a>
				@endif
			</div>

			<div class="pt-1">
				<form method="POST" action="{{ route('logout') }}" class="w-full">
					@csrf
					<button type="submit" data-loading-text="{{ __('messages.logging_out') }}"
					        class="w-full text-left flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-red-400 hover:bg-red-500/10 hover:text-red-300 transition-all duration-150 cursor-pointer active:scale-95">
						<svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
							      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
						</svg>
						<span>{{ __('messages.logout') }}</span>
					</button>
				</form>
			</div>
		</div>
	</div>
@endauth
