@extends('layouts.app')

@section('title', __('messages.edit_budget'))

@section('contents')
    <div class="py-10 bg-slate-50/70 min-h-[calc(100vh-80px)]">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header & Back Button -->
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-gray-200 text-gray-700 hover:bg-gray-100 font-semibold text-xs uppercase tracking-wider shadow-xs transition-all duration-200 active:scale-95 mb-3 group">
                        <svg class="w-4 h-4 text-purple-900 transition-transform group-hover:-translate-x-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                        </svg>
                        <span>{{ __('messages.back_to_list') }}</span>
                    </a>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('messages.edit_budget') }}</h1>
                </div>
            </div>

            <!-- Reusable Budget Form Component -->
            <x-budget-form :budget="$budget" :action="route('budgets.update', $budget)" method="PUT" />

        </div>
    </div>
@endsection
