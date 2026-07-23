@extends(auth()->check() ? 'layouts.app' : 'layouts.base')

@section('title', __('messages.error_403_title'))

@section('contents')
<div class="min-h-[calc(100vh-180px)] flex items-center justify-center p-4">
	<div class="max-w-md w-full bg-white border border-purple-900/10 rounded-2xl p-8 text-center shadow-lg space-y-6">
		<div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto shadow-inner">
			<svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
			</svg>
		</div>

		<div>
			<span class="text-xs font-bold uppercase tracking-widest text-purple-900/60 block mb-1">Error 403</span>
			<h1 class="text-2xl font-extrabold text-gray-900 mb-2">{{ __('messages.error_403_title') }}</h1>
			<p class="text-sm text-gray-600 leading-relaxed">
				{{ $exception->getMessage() ?: __('messages.error_403_subtitle') }}
			</p>
		</div>

		<div class="pt-2">
			<a
				href="{{ auth()->check() ? route('dashboard') : route('login') }}"
				class="inline-flex items-center justify-center gap-2 w-full bg-[#1b0e35] hover:bg-[#28154e] text-white font-semibold px-6 py-3 rounded-xl shadow-md transition-all duration-200 active:scale-95"
			>
				<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
				</svg>
				<span>{{ __('messages.error_back_dashboard') }}</span>
			</a>
		</div>
	</div>
</div>
@endsection
