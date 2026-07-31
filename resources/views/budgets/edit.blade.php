@extends('layouts.app')

@section('title', __('messages.edit_budget'))

@section('contents')
	<div class="py-10 bg-slate-50/70 min-h-[calc(100vh-80px)]">
		<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

			<!-- Page Header & Back Button -->
			<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
				<div>
					<a href="{{ route('budgets.show', $budget) }}"
					   onclick="if (this.hasAttribute('data-clicked')) { return false; } this.setAttribute('data-clicked', 'true'); this.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');"
					   class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-purple-900/80 hover:text-purple-900 transition-colors mb-2 group">
						<svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5"
						     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
						     stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
						</svg>
						<span>{{ __('messages.back_to_list') }}</span>
					</a>
					<h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">{{ __('messages.edit_budget') }}</h1>
				</div>
			</div>

			<!-- Reusable Budget Form Component -->
			<x-budget-form :budget="$budget" :action="route('budgets.update', $budget)" method="PUT"/>

		</div>
	</div>
@endsection
