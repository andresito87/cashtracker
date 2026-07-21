@extends('layouts.app')

@section('title', __('messages.budgets'))

@section('contents')
    <div class="min-h-[calc(100vh-64px)] bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto">
            @if (session('status'))
                <x-alert :type="session('status_type', 'success')" :message="session('status')" />
            @endif

            <div class="flex items-center justify-between mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.budgets') }}</h1>
                <a href="{{ route('budgets.create') }}" class="text-indigo-600 hover:text-indigo-800">{{ __('messages.create_budget') }}</a>
            </div>

            <div class="space-y-4">
                @forelse ($budgets as $budget)
                    <a href="{{ route('budgets.show', $budget) }}" class="block bg-white rounded-xl p-4 shadow-sm">
                        {{ $budget->name }}
                    </a>
                @empty
                    <p class="text-gray-500">{{ __('messages.no_budgets') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
