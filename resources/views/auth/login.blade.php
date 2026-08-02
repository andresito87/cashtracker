@extends('layouts.auth')

@section('title', __('messages.sign_in'))

@section('auth-contents')

	<form method="POST" action="{{ route('login.store') }}" class="mt-14 space-y-5" novalidate>
		@csrf

		<div class="flex flex-col gap-2">
			<label class="font-bold text-2xl" for="email">{{ __('messages.email') }}</label>

			<input
				id="email"
				type="email"
				placeholder="{{ __('messages.email_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="email"
				value="{{ old('email') }}"
				tabindex="1"
			/>
		</div>

		<x-input-error :messages="$errors->get('email')"/>

		<div class="flex flex-col gap-2">
			<div class="flex items-center justify-between">
				<label class="font-bold text-2xl" for="password">{{ __('messages.password') }}</label>
				<a href="{{ route('password.request') }}" class="text-indigo-950"
				   tabindex="3">{{ __('messages.forgot_password') }}</a>
			</div>
			<input
				id="password"
				type="password"
				placeholder="{{ __('messages.password_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="password"
				tabindex="2"
			/>
		</div>

		<x-input-error :messages="$errors->get('password')"/>

		@error('login')
		<x-alert type="error" :message="$message"/>
		@enderror

		<button
			type="submit"
			data-loading-text="{{ __('messages.logging_in') }}"
			class="bg-purple-950 hover:bg-purple-800 w-full p-3 rounded-lg text-white font-bold text-xl cursor-pointer flex items-center justify-center gap-2 select-none"
		>
			{{ __('messages.sign_in') }}
		</button>
	</form>
@endsection
