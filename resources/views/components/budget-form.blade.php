@use('App\Enums\BudgetType')

<form method="POST" action="{{ $action }}"
      class="bg-white border border-purple-900/10 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6 select-none">
	@csrf
	@if ($methodOverride)
		@method($methodOverride)
	@endif

	<!-- Budget Name -->
	<div>
		<label for="name" class="flex items-center gap-2 text-sm font-semibold text-gray-800 mb-1.5">
			<svg class="w-4 h-4 text-purple-900/70 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
			     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round"
				      d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/>
				<path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/>
			</svg>
			<span>{{ __('messages.name') }} <span class="text-red-500">*</span></span>
		</label>
		<input
			id="name"
			name="name"
			type="text"
			required
			value="{{ $name }}"
			placeholder="{{ __('messages.budget_name_placeholder') }}"
			class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-gray-900 bg-gray-50/40 focus:outline-none focus:ring-2 focus:ring-purple-900/20 transition-all duration-200"
		>
		<x-input-error :messages="$errors->get('name')" class="mt-1.5"/>
	</div>

	<!-- Amount & Type Grid -->
	<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
		<!-- Amount -->
		<div class="sm:col-span-1">
			<label for="amount" class="flex items-center gap-2 text-sm font-semibold text-gray-800 mb-1.5">
				<svg class="w-4 h-4 text-purple-900/70 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
				     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round"
					      d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5h16.5a1.5 1.5 0 011.5 1.5v9.75a1.5 1.5 0 01-1.5 1.5H3.75a1.5 1.5 0 01-1.5-1.5V6a1.5 1.5 0 011.5-1.5Zm13.5 3h.008v.008H17.25V7.5Zm0 6h.008v.008H17.25v-.008ZM6 10.5a3 3 0 116 0 3 3 0 01-6 0z"/>
				</svg>
				<span>{{ __('messages.amount') }} <span class="text-red-500">*</span></span>
			</label>
			<div
				class="flex rounded-xl border border-gray-200 bg-gray-50/40 focus-within:ring-2 focus-within:ring-purple-900/20 transition-all duration-200 overflow-hidden">
				<input
					id="amount"
					name="amount"
					type="number"
					step="0.01"
					min="0"
					required
					value="{{ $amount }}"
					placeholder="{{ __('messages.budget_amount_placeholder') }}"
					class="w-full px-4 py-2.5 bg-transparent border-0 text-gray-900 focus:outline-none focus:ring-0"
				>
				<span class="bg-gray-100/90 text-gray-800 text-sm font-bold border-l border-gray-200 px-4 py-2.5 flex items-center shrink-0 select-none">
					{{ $userCurrencySymbol }}
				</span>
			</div>
			<x-input-error :messages="$errors->get('amount')" class="mt-1.5"/>
		</div>

		<!-- Type -->
		<div class="sm:col-span-1">
			<label for="type" class="flex items-center gap-2 text-sm font-semibold text-gray-800 mb-1.5">
				<svg class="w-4 h-4 text-purple-900/70 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
				     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round"
					      d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
				</svg>
				<span>{{ __('messages.type') }} <span class="text-red-500">*</span></span>

				<!-- Interactive Info Tooltip -->
				<span class="group relative inline-flex items-center ml-0.5">
					<svg class="w-4 h-4 text-purple-900/50 hover:text-purple-900 cursor-help transition-colors"
					     xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
					     stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round"
						      d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/>
					</svg>

					<!-- Popover Card -->
					<span
						class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 opacity-0 group-hover:opacity-100 transition-all duration-200 z-50 bg-[#1b0e35] text-white text-xs rounded-xl p-3 shadow-xl border border-purple-800/40">
						<span
							class="font-bold block mb-1.5 text-purple-300 border-b border-purple-800/50 pb-1">{{ __('messages.type') }}</span>
						<span class="block mb-1"><strong
								class="text-purple-200">{{ __('messages.type_general') }}:</strong> {{ __('messages.type_help_general') }}</span>
						<span class="block"><strong class="text-purple-200">{{ __('messages.type_help_goal') }}:</strong> {{ __('messages.type_help_goal') }}</span>
					</span>
				</span>
			</label>
			<div class="relative">
				<select
					id="type"
					name="type"
					required
					class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-gray-900 bg-gray-50/40 focus:outline-none focus:ring-2 focus:ring-purple-900/20 transition-all duration-200 appearance-none pr-10"
				>
					@foreach (BudgetType::cases() as $type)
						<option value="{{ $type->value }}" {{ $currentType === $type->value ? 'selected' : '' }}>
							{{ __('messages.type_' . $type->value) }}
						</option>
					@endforeach
				</select>
				<div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-gray-400">
					<svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
					     stroke-width="2" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
					</svg>
				</div>
			</div>
			<x-input-error :messages="$errors->get('type')" class="mt-1.5"/>
		</div>
	</div>

	<!-- Description -->
	<div>
		<label for="description" class="flex items-center gap-2 text-sm font-semibold text-gray-800 mb-1.5">
			<svg class="w-4 h-4 text-purple-900/70 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
			     viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h12"/>
			</svg>
			<span>{{ __('messages.description') }}</span>
		</label>
		<textarea
			id="description"
			name="description"
			rows="3"
			placeholder="{{ __('messages.budget_description_placeholder') }}"
			class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-gray-900 bg-gray-50/40 focus:outline-none focus:ring-2 focus:ring-purple-900/20 transition-all duration-200 resize-y"
		>{{ $description }}</textarea>
		<x-input-error :messages="$errors->get('description')" class="mt-1.5"/>
	</div>

	<!-- Action Buttons -->
	<div class="pt-4 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-100">
		<button
			type="submit"
			data-loading-text="{{ $loadingText }}"
			class="w-full sm:w-auto bg-[#1b0e35] hover:bg-[#28154e] text-white font-semibold px-6 py-2.5 rounded-xl shadow-md hover:shadow-lg transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 active:scale-95"
		>
			<svg class="w-4 h-4 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
			     stroke-width="2.5" stroke="currentColor">
				<path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
			</svg>
			<span>{{ $submitText }}</span>
		</button>

		<a
			href="{{ route('dashboard') }}"
			class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-100 font-medium transition-all duration-200 text-center active:scale-95"
		>
			{{ __('messages.cancel') }}
		</a>
	</div>
</form>
