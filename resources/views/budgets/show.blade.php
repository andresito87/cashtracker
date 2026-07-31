@php use App\Enums\BudgetType; @endphp
@extends('layouts.app')

@section('title', $budget->name)

@section('contents')
	<div class="py-10 bg-slate-50/70 min-h-[calc(100vh-80px)]">
		<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

			{{-- Flash Status Alert --}}
			@if (session('status'))
				<x-alert :type="session('status_type', 'success')" :message="session('status')" class="mb-6"/>
			@endif

			<!-- Back Button -->
			<div class="mb-4">
				<a href="{{ route('dashboard') }}"
				   onclick="if (this.hasAttribute('data-clicked')) { return false; } this.setAttribute('data-clicked', 'true'); this.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');"
				   class="inline-flex items-center gap-1.5 text-xs sm:text-sm font-semibold text-purple-900/80 hover:text-purple-900 transition-colors group">
					<svg class="w-3.5 h-3.5 transition-transform group-hover:-translate-x-0.5"
					     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
					     stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/>
					</svg>
					<span>{{ __('messages.back_to_list') }}</span>
				</a>
			</div>

			<!-- Detail Card -->
			<div class="bg-white border border-purple-900/10 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">

				<!-- Header: Budget Title & Type Badge -->
				<div
					class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-gray-100">
					<div>
						<h1 class="text-2xl sm:text-3xl font-extrabold text-gray-900 tracking-tight">{{ $budget->name }}</h1>
					</div>

					<div>
                        <span
							class="inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold {{ $budget->type === BudgetType::Goal ? 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-600/20' : 'bg-purple-50 text-purple-700 ring-1 ring-inset ring-purple-700/20' }}">
                            {{ __('messages.type_' . ($budget->type->value ?? $budget->type)) }}
                        </span>
					</div>
				</div>

				<!-- Stats Grid: Amount & Type Details -->
				<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
					<!-- Amount Box -->
					<div class="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
						<span
							class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">{{ __('messages.amount') }}</span>
						<p class="text-3xl font-extrabold text-gray-900">{{ $budget->formattedAmount() }}</p>
					</div>

					<!-- Type Info Box -->
					<div class="bg-gray-50/50 border border-gray-100 rounded-xl p-5">
						<span
							class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1">{{ __('messages.type') }}</span>
						<p class="text-lg font-bold text-gray-900">{{ __('messages.type_' . ($budget->type->value ?? $budget->type)) }}</p>
						<p class="text-xs text-gray-500 mt-1">{{ __('messages.type_help_' . ($budget->type->value ?? $budget->type)) }}</p>
					</div>
				</div>

				<!-- Description -->
				<div>
					<span
						class="text-xs font-semibold text-gray-400 uppercase tracking-wider block mb-1.5">{{ __('messages.description') }}</span>
					<div
						class="bg-slate-50/80 rounded-xl p-4 border border-gray-100 text-gray-700 text-sm leading-relaxed">
						@if ($budget->description)
							{{ $budget->description }}
						@else
							<span class="text-gray-400 italic">Sin descripción registrada</span>
						@endif
					</div>
				</div>

				<!-- Action Buttons (Edit & Delete only) -->
				<div class="pt-6 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-100">
					<a
						href="{{ route('budgets.edit', $budget) }}"
						class="w-full sm:w-auto bg-[#1b0e35] hover:bg-[#28154e] text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 active:scale-95"
					>
						<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
						     stroke-width="2" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round"
							      d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10"/>
						</svg>
						<span>{{ __('messages.edit') }}</span>
					</a>

					<button
						type="button"
						command="show-modal"
						commandfor="delete-budget-dialog-{{ $budget->id }}"
						class="w-full sm:w-auto bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium px-5 py-2.5 rounded-xl border border-rose-200 transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 active:scale-95"
					>
						<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
						     stroke-width="2" stroke="currentColor">
							<path stroke-linecap="round" stroke-linejoin="round"
							      d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
						</svg>
						<span>{{ __('messages.delete') }}</span>
					</button>

					<x-confirm-delete
						:id="'delete-budget-dialog-' . $budget->id"
						:title="__('messages.confirm_delete_budget_title', ['name' => $budget->name])"
						:message="__('messages.confirm_delete_message')"
						:action="route('budgets.destroy', $budget)"
					/>
				</div>

			</div>

		</div>
	</div>
@endsection
