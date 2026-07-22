@php use App\Enums\BudgetType; @endphp
@extends('layouts.app')

@section('title', __('messages.budgets'))

@section('contents')
	<div class="min-h-[calc(100vh-64px)] bg-gray-50 p-6">
		<div class="max-w-7xl mx-auto">
			@if (session('status'))
				<x-alert :type="session('status_type', 'success')" :message="session('status')"/>
			@endif

			<div class="flex items-center justify-between mb-8">
				<h1 class="text-3xl font-bold text-gray-900">{{ __('messages.budgets') }}</h1>
				<a href="{{ route('budgets.create') }}"
				   class="text-indigo-600 hover:text-indigo-800">{{ __('messages.create_budget') }}</a>
			</div>

			<div class="space-y-4">
				@forelse ($budgets as $budget)
					<a href="{{ route('budgets.show', $budget) }}"
					   class="flex items-center justify-between bg-white rounded-xl p-4 shadow-sm hover:shadow-md transition">
						<div>
							<span class="font-semibold text-gray-900">{{ $budget->name }}</span>
							<span
								class="ml-2 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium {{ $budget->type === BudgetType::Goal ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-blue-50 text-blue-700 ring-1 ring-inset ring-blue-700/10' }}">
                                {{ __('messages.type_' . ($budget->type->value ?? $budget->type)) }}
                            </span>
						</div>
						<span class="font-bold text-gray-700">{{ $budget->formattedAmount() }}</span>
					</a>
				@empty
					<div class="bg-white rounded-2xl p-8 sm:p-10 text-center border border-gray-200 shadow-sm">
						<h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('messages.no_budgets') }}</h3>
						<p class="text-gray-500 text-sm max-w-md mx-auto">{{ __('messages.no_budgets_subtitle') }}</p>
					</div>
				@endforelse
			</div>
		</div>
	</div>
@endsection
