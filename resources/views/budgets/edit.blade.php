@extends('layouts.app')

@section('title', __('messages.edit_budget'))

@section('contents')
    <div class="min-h-[calc(100vh-64px)] bg-gray-50 p-6">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">{{ __('messages.edit_budget') }}</h1>

            <form method="POST" action="{{ route('budgets.update', $budget) }}" class="space-y-4 bg-white rounded-xl p-6 shadow-sm">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">{{ __('messages.name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $budget->name) }}" class="mt-1 w-full rounded-lg border-gray-300">
                    <x-input-error :messages="$errors->get('name')" />
                </div>

                <div>
                    <label for="amount" class="block text-sm font-medium text-gray-700">{{ __('messages.amount') }}</label>
                    <input id="amount" name="amount" type="number" step="0.01" value="{{ old('amount', $budget->amount) }}" class="mt-1 w-full rounded-lg border-gray-300">
                    <x-input-error :messages="$errors->get('amount')" />
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">{{ __('messages.description') }}</label>
                    <textarea id="description" name="description" class="mt-1 w-full rounded-lg border-gray-300">{{ old('description', $budget->description) }}</textarea>
                    <x-input-error :messages="$errors->get('description')" />
                </div>

                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-white">{{ __('messages.update_budget') }}</button>
            </form>
        </div>
    </div>
@endsection
