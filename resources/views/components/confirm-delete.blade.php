<dialog
	id="{{ $id }}"
	closedby="any"
	{{ $attributes->merge(['class' => 'backdrop:bg-slate-900/60 backdrop:backdrop-blur-xs rounded-2xl border border-purple-900/10 p-0 shadow-2xl bg-white text-left max-w-xl w-[calc(100%-2rem)] mx-auto my-auto backdrop:transition-all overflow-hidden focus:outline-none select-none']) }}
>
	<div class="p-6 sm:p-7">
		<div class="flex items-start gap-4">
			<!-- Alert Icon Badge -->
			<div class="shrink-0 w-11 h-11 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
				<svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
					<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
				</svg>
			</div>

			<!-- Title & Message Content -->
			<div class="flex-1">
				<h3 class="text-base sm:text-lg font-bold text-gray-900 leading-snug">
					{{ $title }}
				</h3>
				<div class="text-sm text-gray-500 mt-1.5 leading-relaxed">
					{{ (isset($slot) && $slot->isNotEmpty()) ? $slot : $message }}
				</div>
			</div>
		</div>

		<!-- Footer Action Buttons (Aligned to right) -->
		<div class="mt-6 pt-4 border-t border-gray-100 flex flex-row items-center justify-end gap-3">
			<form method="POST" action="{{ $action }}" class="m-0">
				@csrf
				@if ($methodOverride)
					<input type="hidden" name="_method" value="{{ $methodOverride }}">
				@endif
				<button
					type="submit"
					class="inline-flex items-center justify-center font-semibold px-4 py-2.5 rounded-xl text-sm transition-all duration-200 cursor-pointer active:scale-95 shrink-0 bg-rose-600 hover:bg-rose-700 text-white shadow-xs shadow-rose-600/20"
				>
					<svg class="w-4 h-4 mr-1.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
					</svg>
					<span>{{ $confirmText }}</span>
				</button>
			</form>

			<button
				type="button"
				command="close"
				commandfor="{{ $id }}"
				class="inline-flex items-center justify-center font-semibold px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 hover:bg-gray-100 text-sm transition-all duration-200 cursor-pointer active:scale-95 shrink-0"
			>
				{{ $cancelText }}
			</button>
		</div>
	</div>
</dialog>
