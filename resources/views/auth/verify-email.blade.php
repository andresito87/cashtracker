@extends('layouts.auth')

@section('title', __('messages.verify_email'))

@section('auth-contents')

	@if (session('status') !== 'verification-link-sent')
		<x-alert type="info" :message="__('messages.verification_sent')" />
	@endif

	<div class="mb-8 text-center">
		<p class="text-gray-500">{{ __('messages.verification_help') }}</p>
	</div>

	@if (session('status') === 'verification-link-sent')
		<x-alert type="success" :message="__('messages.verification_resent')" />
	@endif

	<div class="mt-8 text-center">
		<form method="POST" action="{{ route('verification.send') }}">
			@csrf
			<button type="submit" data-loading-text="{{ __('messages.loading') }}" class="bg-blue-500 text-white px-4 py-2 rounded-lg hover:bg-blue-600 select-none inline-flex items-center justify-center gap-2">
				{{ __('messages.resend_verification') }}
			</button>
		</form>
	</div>

@endsection
