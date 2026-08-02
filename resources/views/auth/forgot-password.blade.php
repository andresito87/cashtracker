@extends('layouts.auth')

@section('title')
	Olvide mi contraseña
@endsection

@section('auth-contents')

	<form method="POST" action="{{ route('password.email') }}" class="mt-10 space-y-5" novalidate>
		@csrf
		<div class="flex flex-col gap-2">
			<label class="font-bold text-2xl" for="email">Email</label>

			<input id="email" type="email" placeholder="Email de Registro"
			       class="w-full border border-gray-300 p-3 rounded-lg" name="email"/>
		</div>

		<x-input-error :messages="$errors->get('email')"/>

		<input type="submit" value='Enviar Instrucciones'
		       class="bg-purple-950 hover:bg-purple-800 w-full p-3 rounded-lg text-white font-bold  text-xl cursor-pointer"/>
	</form>
@endsection
