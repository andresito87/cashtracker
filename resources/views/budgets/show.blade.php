@extends('layouts.app')

@section('title', $budget->name)

@section('contents')
    <div class="min-h-[calc(100vh-64px)] bg-gray-50 p-6">
        <div class="max-w-2xl mx-auto">
            @if (session('status'))
                <x-alert :type="session('status_type', 'success')" :message="session('status')" />
            @endif

            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $budget->name }}</h1>
            <p class="text-2xl font-semibold text-indigo-600 mb-4">${{ $budget->amount }}</p>

            @if ($budget->description)
                <p class="text-gray-600 mb-6">{{ $budget->description }}</p>
            @endif

            <div class="flex gap-4">
                <a href="{{ route('budgets.edit', $budget) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('messages.edit') }}</a>
                <a href="{{ route('budgets.index') }}" class="text-gray-600 hover:text-gray-800">{{ __('messages.back_to_list') }}</a>
            </div>
        </div>
    </div>
@endsection
