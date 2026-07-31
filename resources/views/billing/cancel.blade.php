@extends('layouts.app')

@section('title')
	{{ __('messages.billing_cancel_title') }}
@endsection

@section('contents')
	<div class="max-w-2xl mx-auto my-12 bg-white rounded-2xl border border-purple-900/15 p-8 text-center shadow-md">
		<div class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
			<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
				<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
			</svg>
		</div>

		<h1 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-3">
			{{ __('messages.billing_cancel_title') }}
		</h1>

		<p class="text-gray-600 text-base leading-relaxed mb-8 max-w-lg mx-auto">
			{{ __('messages.billing_cancel_subtitle') }}
		</p>

		<div class="flex items-center justify-center gap-4">
			<a href="{{ route('plans') }}"
			   class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl shadow-md shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95 text-base">
				<span>{{ __('messages.get_pro_monthly') }}</span>
			</a>
			<a href="{{ route('dashboard') }}"
			   class="inline-flex items-center justify-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-6 py-3 rounded-xl transition-all text-base">
				<span>{{ __('messages.landing_cta_dashboard') }}</span>
			</a>
		</div>
	</div>
@endsection

