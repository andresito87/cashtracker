@php use App\Enums\BudgetType; @endphp
@extends('layouts.app')

@section('title', __('messages.dashboard'))

@section('contents')

	<div class="min-h-[calc(100vh-64px)] sm:min-h-[calc(100vh-80px)] bg-slate-50/70 p-6 sm:p-10">
		<div class="max-w-7xl mx-auto">

			{{-- Success / Flash Status Alert --}}
			@if (session('status'))
				<x-alert :type="session('status_type', 'success')" :message="session('status')"/>
			@endif

			{{-- Section Header: Manage Budgets + Create Budget CTA --}}
			<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
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
					   class="inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-bold px-6 py-3 rounded-xl shadow-md shadow-orange-500/20 hover:shadow-orange-500/35 transition-all duration-200 active:scale-95 text-base">
						<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
						     stroke-width="2.5" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
						</svg>
						<span>{{ __('messages.create_budget') }}</span>
					</a>
				</div>
			</div>

			{{-- Budget List Grid --}}
			<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
				@forelse ($budgets as $budget)
					<div
						class="bg-white rounded-2xl p-6 border border-purple-900/10 hover:border-purple-900/25 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between group">
						<div>
							<!-- Header: Name & Type Badge -->
							<div class="flex items-start justify-between gap-3 mb-3">
								<h2 class="text-xl font-bold text-gray-900 line-clamp-1 group-hover:text-purple-900 transition-colors">
									{{ $budget->name }}
								</h2>
								<span
									class="inline-flex items-center shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold {{ $budget->type === BudgetType::Goal ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/20' }}">
                                    {{ __('messages.type_' . ($budget->type->value ?? $budget->type)) }}
                                </span>
							</div>

							<!-- Amount -->
							<div class="mb-4">
								<span
									class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-0.5">{{ __('messages.amount') }}</span>
								<p class="text-3xl font-extrabold text-gray-900">{{ $budget->formattedAmount() }}</p>
							</div>

							<!-- Description (if present) -->
							@if ($budget->description)
								<p class="text-sm text-gray-600 line-clamp-2 mb-4 bg-slate-50/80 rounded-xl p-3 border border-gray-100">
									{{ $budget->description }}
								</p>
							@endif
						</div>

						<!-- Card Footer Actions -->
						<div class="pt-4 border-t border-gray-100 flex items-center justify-between mt-4">
							<a href="{{ route('budgets.show', $budget) }}"
							   class="inline-flex items-center gap-1.5 text-sm font-bold text-purple-900 hover:text-purple-700 transition-colors group/link">
								<span>{{ __('messages.show') }}</span>
								<svg class="w-4 h-4 transition-transform group-hover/link:translate-x-1"
								     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
								     stroke="currentColor">
									<path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
								</svg>
							</a>
						</div>
					</div>
				@empty
					<div
						class="col-span-full bg-white rounded-2xl p-8 sm:p-10 text-center border border-purple-900/10 shadow-sm">
						<h3 class="text-lg font-bold text-gray-900 mb-1">{{ __('messages.no_budgets') }}</h3>
						<p class="text-gray-500 text-sm max-w-md mx-auto">{{ __('messages.no_budgets_subtitle') }}</p>
					</div>
				@endforelse
			</div>

		</div>
	</div>

@endsection
