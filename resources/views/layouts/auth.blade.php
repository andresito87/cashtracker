@extends('layouts.base')

@section('contents')

	<div class="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-96px)] flex flex-col items-center justify-center bg-gray-50 p-4">
		<main class="w-full max-w-2xl p-10 shadow-2xl bg-white rounded-2xl select-none">

			<h1 class="font-bold text-4xl text-center mb-8">@yield('title')</h1>

			@yield('auth-contents')

		</main>
	</div>

@endsection
