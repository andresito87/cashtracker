@extends('layouts.base')

@section('title', __('messages.landing_hero_badge'))

@section('contents')
	<div class="bg-gray-50 text-gray-900 select-none font-sans">
		
		<!-- Hero Section (Dark Purple Header Integration) -->
		<section class="relative overflow-hidden bg-linear-to-b from-[#1b0e35] via-[#160a2c] to-[#120724] text-white pt-12 pb-20 sm:pt-20 sm:pb-28">
			<!-- Subtle glow effect -->
			<div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-600/15 blur-3xl rounded-full pointer-events-none"></div>
			<div class="absolute top-1/3 left-1/3 w-80 h-80 bg-orange-500/10 blur-3xl rounded-full pointer-events-none"></div>

			<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
				<div class="text-center max-w-3xl mx-auto">
					
					<!-- Badge -->
					<div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-orange-500/10 border border-orange-500/30 text-orange-400 text-xs sm:text-sm font-semibold tracking-wide mb-6 backdrop-blur-sm">
						<svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
						</svg>
						<span>{{ __('messages.landing_hero_badge') }}</span>
					</div>

					<!-- Hero Headline -->
					<h1 class="text-4xl sm:text-6xl font-black text-white tracking-tight leading-tight sm:leading-none">
						{{ __('messages.landing_hero_title') }}
					</h1>

					<!-- Hero Subtitle -->
					<p class="mt-6 text-base sm:text-xl text-purple-200/80 leading-relaxed font-normal">
						{{ __('messages.landing_hero_subtitle') }}
					</p>

					<!-- Call to Action Buttons -->
					<div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
						@auth
							<a href="{{ route('dashboard') }}"
							   class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-white bg-linear-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-xl shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95">
								<span>{{ __('messages.landing_cta_dashboard') }}</span>
								<svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
								</svg>
							</a>
						@else
							<a href="{{ route('register') }}"
							   class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-white bg-linear-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-xl shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95">
								<span>{{ __('messages.landing_cta_primary') }}</span>
								<svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
									<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
								</svg>
							</a>
							<a href="{{ route('login') }}"
							   class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3.5 text-base font-semibold text-orange-400 hover:text-white bg-purple-950/40 hover:bg-purple-900/60 border border-orange-500/30 hover:border-orange-500/70 rounded-xl transition-all duration-200 active:scale-95">
								{{ __('messages.landing_cta_login') }}
							</a>
						@endauth
					</div>
				</div>

				<!-- Visual Dashboard Preview Card Mockup -->
				<div class="mt-14 max-w-4xl mx-auto rounded-2xl bg-[#180b30] border border-purple-800/40 shadow-2xl p-4 sm:p-6 backdrop-blur-xl relative group">
					<div class="flex items-center justify-between pb-4 mb-4 border-b border-purple-900/40">
						<div class="flex items-center space-x-2">
							<span class="w-3 h-3 rounded-full bg-red-500/80 inline-block"></span>
							<span class="w-3 h-3 rounded-full bg-yellow-500/80 inline-block"></span>
							<span class="w-3 h-3 rounded-full bg-green-500/80 inline-block"></span>
						</div>
						<div class="text-xs text-purple-300/60 font-mono tracking-wider">cashtracker.app/dashboard</div>
					</div>

					<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
						<!-- Total Balance Card -->
						<div class="bg-[#211140] p-4 sm:p-5 rounded-xl border border-purple-800/30">
							<p class="text-xs font-semibold uppercase tracking-wider text-purple-300/70">{{ __('messages.landing_demo_balance') }}</p>
							<p class="text-2xl sm:text-3xl font-extrabold text-white mt-1">$4,250.00</p>
							<span class="inline-block mt-2 text-xs font-semibold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-md">+12.5%</span>
						</div>

						<!-- Incomes Card -->
						<div class="bg-[#211140] p-4 sm:p-5 rounded-xl border border-purple-800/30">
							<p class="text-xs font-semibold uppercase tracking-wider text-purple-300/70">{{ __('messages.landing_demo_incomes') }}</p>
							<p class="text-2xl sm:text-3xl font-extrabold text-emerald-400 mt-1">$5,800.00</p>
							<span class="inline-block mt-2 text-xs font-medium text-purple-300/60">mensual</span>
						</div>

						<!-- Expenses Card -->
						<div class="bg-[#211140] p-4 sm:p-5 rounded-xl border border-purple-800/30">
							<p class="text-xs font-semibold uppercase tracking-wider text-purple-300/70">{{ __('messages.landing_demo_expenses') }}</p>
							<p class="text-2xl sm:text-3xl font-extrabold text-rose-400 mt-1">$1,550.00</p>
							<span class="inline-block mt-2 text-xs font-medium text-purple-300/60">mensual</span>
						</div>
					</div>

					<!-- Budget Progress Simulation -->
					<div class="mt-4 bg-[#211140] p-4 rounded-xl border border-purple-800/30">
						<div class="flex justify-between items-center text-xs sm:text-sm font-semibold mb-2">
							<span class="text-purple-200">{{ __('messages.landing_demo_budget_status') }}</span>
							<span class="text-orange-400">62% {{ __('messages.landing_demo_used') }}</span>
						</div>
						<div class="w-full bg-purple-950 rounded-full h-2.5 overflow-hidden">
							<div class="bg-linear-to-r from-orange-500 to-orange-400 h-2.5 rounded-full" style="width: 62%"></div>
						</div>
					</div>
				</div>
			</div>
		</section>

		<!-- Features Grid Section (Clean Light Theme matching rest of app) -->
		<section class="py-16 sm:py-24 bg-gray-50">
			<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
				
				<div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
					<span class="text-orange-500 font-semibold text-xs sm:text-sm uppercase tracking-widest">{{ __('messages.landing_features_badge') }}</span>
					<h2 class="text-3xl sm:text-4xl font-extrabold text-gray-900 mt-2">{{ __('messages.landing_features_title') }}</h2>
					<p class="text-gray-500 text-sm sm:text-base mt-3">{{ __('messages.landing_features_subtitle') }}</p>
				</div>

				<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
					<!-- Feature 1 -->
					<div class="bg-white border border-gray-100 rounded-2xl p-6 hover:-translate-y-1 hover:shadow-md transition-all duration-300 shadow-sm group">
						<div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 mb-5 group-hover:scale-110 transition-transform">
							<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
							</svg>
						</div>
						<h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('messages.landing_feature_1_title') }}</h3>
						<p class="text-sm text-gray-500 leading-relaxed">{{ __('messages.landing_feature_1_desc') }}</p>
					</div>

					<!-- Feature 2 -->
					<div class="bg-white border border-gray-100 rounded-2xl p-6 hover:-translate-y-1 hover:shadow-md transition-all duration-300 shadow-sm group">
						<div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-600 mb-5 group-hover:scale-110 transition-transform">
							<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
							</svg>
						</div>
						<h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('messages.landing_feature_2_title') }}</h3>
						<p class="text-sm text-gray-500 leading-relaxed">{{ __('messages.landing_feature_2_desc') }}</p>
					</div>

					<!-- Feature 3 -->
					<div class="bg-white border border-gray-100 rounded-2xl p-6 hover:-translate-y-1 hover:shadow-md transition-all duration-300 shadow-sm group">
						<div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-orange-500 mb-5 group-hover:scale-110 transition-transform">
							<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
							</svg>
						</div>
						<h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('messages.landing_feature_3_title') }}</h3>
						<p class="text-sm text-gray-500 leading-relaxed">{{ __('messages.landing_feature_3_desc') }}</p>
					</div>

					<!-- Feature 4 -->
					<div class="bg-white border border-gray-100 rounded-2xl p-6 hover:-translate-y-1 hover:shadow-md transition-all duration-300 shadow-sm group">
						<div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-600 mb-5 group-hover:scale-110 transition-transform">
							<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
							</svg>
						</div>
						<h3 class="text-lg font-bold text-gray-900 mb-2">{{ __('messages.landing_feature_4_title') }}</h3>
						<p class="text-sm text-gray-500 leading-relaxed">{{ __('messages.landing_feature_4_desc') }}</p>
					</div>
				</div>
			</div>
		</section>

		<!-- Bottom CTA Section -->
		<section class="py-16 sm:py-20 bg-gray-50">
			<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
				<div class="bg-linear-to-r from-[#1b0e35] via-[#211140] to-[#1b0e35] border border-purple-900/40 rounded-3xl p-8 sm:p-12 text-center shadow-xl relative overflow-hidden">
					<div class="relative z-10">
						<h2 class="text-2xl sm:text-4xl font-extrabold text-white">{{ __('messages.landing_cta_box_title') }}</h2>
						<p class="mt-3 text-sm sm:text-lg text-purple-200/80 max-w-xl mx-auto">{{ __('messages.landing_cta_box_subtitle') }}</p>
						
						<div class="mt-8 flex justify-center">
							@auth
								<a href="{{ route('dashboard') }}"
								   class="inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-white bg-linear-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-xl shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95">
									{{ __('messages.landing_cta_dashboard') }}
								</a>
							@else
								<a href="{{ route('register') }}"
								   class="inline-flex items-center justify-center px-8 py-3.5 text-base font-bold text-white bg-linear-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 rounded-xl shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95">
									{{ __('messages.landing_cta_box_button') }}
								</a>
							@endauth
						</div>
					</div>
				</div>
			</div>
		</section>

	</div>
@endsection
