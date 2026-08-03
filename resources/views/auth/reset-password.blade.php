@extends('layouts.auth')

@section('title', __('messages.passwords.reset.title'))

@section('auth-contents')

	<div class="mb-8 text-center">
		<p class="text-gray-500">{{ __('messages.passwords.reset.subtitle') }}</p>
	</div>

	<form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-5" novalidate>
		@csrf
		<input type="hidden" name="token" value="{{ $token }}"/>

		<div class="flex flex-col gap-2">
			<label class="font-bold text-2xl" for="email">{{ __('messages.passwords.reset.email_label') }}</label>

			<input
				id="email"
				type="email"
				class="w-full border border-gray-300 p-3 rounded-lg bg-gray-100"
				name="email"
				value="{{ old('email', $email) }}"
				readonly
				tabindex="1"
			/>
		</div>

		<x-input-error :messages="$errors->get('email')"/>

		<div class="flex flex-col gap-2">
			<label class="font-bold text-2xl" for="password">{{ __('messages.passwords.reset.password_label') }}</label>

			<input
				id="password"
				type="password"
				placeholder="{{ __('messages.passwords.reset.password_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="password"
				autofocus
				tabindex="2"
			/>
		</div>

		<x-input-error :messages="$errors->get('password')"/>

		<div class="flex flex-col gap-2">
			<label class="font-bold text-2xl"
			       for="password_confirmation">{{ __('messages.passwords.reset.password_confirmation_label') }}</label>

			<input
				id="password_confirmation"
				type="password"
				placeholder="{{ __('messages.passwords.reset.password_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="password_confirmation"
				tabindex="3"
			/>
		</div>

		<x-input-error :messages="$errors->get('password_confirmation')"/>

		<button
			type="submit"
			data-loading-text="{{ __('messages.passwords.reset.submit') }}"
			class="bg-purple-950 hover:bg-purple-800 w-full p-3 rounded-lg text-white font-bold text-xl cursor-pointer flex items-center justify-center gap-2 select-none"
		>
			{{ __('messages.passwords.reset.submit') }}
		</button>
	</form>

	<div class="mt-6 text-center">
		<a href="{{ route('login') }}" class="text-indigo-950" tabindex="4">
			{{ __('messages.passwords.reset.back_to_login') }}
		</a>
	</div>
@endsection
