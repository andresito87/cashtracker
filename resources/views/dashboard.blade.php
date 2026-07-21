@extends('layouts.app')

@section('title', __('messages.dashboard'))

@section('contents')

    <div class="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-80px)] bg-gray-50 p-6 sm:p-10">
        <div class="max-w-7xl mx-auto">

            {{-- Success / Flash Status Alert --}}
            @if (session('status'))
                <x-alert :type="session('status_type', 'success')" :message="session('status')" />
            @endif

            {{-- Section Header: Manage Budgets + Create Budget CTA --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-10">
                <div>
                    <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 tracking-tight">
                        {{ __('messages.manage_budgets_title') }}
                    </h1>
                    <p class="text-gray-500 text-base mt-1">
                        {{ __('messages.manage_budgets_subtitle') }}
                    </p>
                </div>

                <div>
                    <a href="{{ route('budgets.create') }}"
                       class="inline-flex items-center justify-center bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl shadow-md shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95 text-base">
                        {{ __('messages.create_budget') }}
                    </a>
                </div>
            </div>

            {{-- Overview Financial Summary Cards --}}
            <div class="hidden">
                <h2>{{ __('messages.welcome_title', ['name' => auth()->user()->name]) }}</h2>
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
