@extends('layouts.base')

@section('title', 'Dashboard')

@section('contents')

    <div class="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-96px)] bg-gray-50 p-6">
        <div class="max-w-7xl mx-auto">

            {{-- Success flash --}}
            @if (session('status'))
                <x-alert :type="session('status_type', 'success')" :message="session('status')" />
            @endif

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.welcome_title', ['name' => auth()->user()->name]) }}</h1>
                <p class="text-gray-500 mt-1">{{ __('messages.welcome_subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">{{ __('messages.incomes') }}</p>
                    <p class="text-3xl font-bold text-green-600 mt-2">$0.00</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">{{ __('messages.expenses') }}</p>
                    <p class="text-3xl font-bold text-red-500 mt-2">$0.00</p>
                </div>
                <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100">
                    <p class="text-sm text-gray-500 font-medium uppercase tracking-wide">{{ __('messages.balance') }}</p>
                    <p class="text-3xl font-bold text-purple-700 mt-2">$0.00</p>
                </div>
            </div>

        </div>
    </div>

@endsection
