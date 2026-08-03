@extends('layouts.auth')

@section('title', __('messages.passwords.forgot.title'))

@section('auth-contents')

	@if (session('status'))
		<x-alert type="success" :message="session('status')"/>
	@endif

	<div class="mb-8 text-center">
		<p class="text-gray-500">{{ __('messages.passwords.forgot.subtitle') }}</p>
	</div>

	<form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-5" novalidate>
		@csrf
		<div class="flex flex-col gap-2">
			<label class="font-bold text-2xl" for="email">{{ __('messages.passwords.forgot.email_label') }}</label>

			<input
				id="email"
				type="email"
				placeholder="{{ __('messages.passwords.forgot.email_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="email"
				value="{{ old('email') }}"
				autofocus
				tabindex="1"
			/>
		</div>

		<x-input-error :messages="$errors->get('email')"/>

		<button
			type="submit"
			data-loading-text="{{ __('messages.passwords.forgot.submit') }}"
			class="bg-purple-950 hover:bg-purple-800 w-full p-3 rounded-lg text-white font-bold text-xl cursor-pointer flex items-center justify-center gap-2 select-none"
		>
			{{ __('messages.passwords.forgot.submit') }}
		</button>
	</form>

	<div class="mt-6 text-center">
		<a href="{{ route('login') }}" class="text-indigo-950" tabindex="2">
			{{ __('messages.passwords.forgot.back_to_login') }}
		</a>
	</div>
@endsection
