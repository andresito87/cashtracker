@extends('layouts.auth')

@use('App\Enums\Currency')

@section('title', __('messages.register'))

@section('auth-contents')

	<form method="POST" action="{{ route('register.store') }}" class="mt-14 space-y-5" novalidate>
		@csrf
		<div class="space-y-2">
			<label class="font-bold text-2xl block" for="name">{{ __('messages.name') }}</label>

			<input
				id="name"
				type="text"
				placeholder="{{ __('messages.name_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="name"
				value="{{ old('name') }}"
			/>
		</div>

		<x-input-error :messages="$errors->get('name')" />

		<div class="space-y-2">
			<label class="font-bold text-2xl block" for="email">{{ __('messages.email') }}</label>

			<input
				id="email"
				type="email"
				placeholder="{{ __('messages.email_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="email"
				value="{{ old('email') }}"
			/>
		</div>

		<x-input-error :messages="$errors->get('email')" />

		<div class="space-y-2">
			<label class="font-bold text-2xl block" for="currency">{{ __('messages.currency') }}</label>

			<div class="relative">
				<select
					id="currency"
					name="currency"
					class="w-full border border-gray-300 p-3 pr-10 rounded-lg bg-white appearance-none cursor-pointer"
				>
					@foreach (Currency::cases() as $currency)
						<option
							value="{{ $currency->value }}"
							{{ old('currency', Currency::EUR->value) === $currency->value ? 'selected' : '' }}
						>
							{{ $currency->label() }}
						</option>
					@endforeach
				</select>
				<div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-500">
					<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
					     stroke-width="2" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
					</svg>
				</div>
			</div>
		</div>

		<x-input-error :messages="$errors->get('currency')" />

		<div class="space-y-2">
			<label class="font-bold text-2xl block" for="password">{{ __('messages.password') }}</label>

			<input
				id="password"
				type="password"
				placeholder="{{ __('messages.password_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="password"
			/>
		</div>

		<x-input-error :messages="$errors->get('password')" />

		<div class="space-y-2">
			<label class="font-bold text-2xl block" for="password_confirmation">{{ __('messages.confirm_password') }}</label>

			<input
				id="password_confirmation"
				type="password"
				placeholder="{{ __('messages.confirm_password_placeholder') }}"
				class="w-full border border-gray-300 p-3 rounded-lg"
				name="password_confirmation"
			/>
		</div>

		<x-input-error :messages="$errors->get('password_confirmation')" />

		<button
			type="submit"
			data-loading-text="{{ __('messages.registering') }}"
			class="bg-purple-950 hover:bg-purple-800 w-full p-3 rounded-lg text-white font-bold text-xl cursor-pointer flex items-center justify-center gap-2 select-none"
		>
			{{ __('messages.sign_up') }}
		</button>
	</form>
@endsection
