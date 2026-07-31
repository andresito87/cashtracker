@if (auth()->user()->isYearlySubscribed())
	<span
		class="inline-flex items-center border border-amber-500/80 md:border-2 text-xs md:text-sm rounded-lg md:rounded-xl text-amber-400 py-1 px-2 md:py-1.5 md:px-3.5 font-bold md:font-black tracking-wide shrink-0 whitespace-nowrap">
		<span class="md:hidden">PRO Anual</span>
		<span class="hidden md:inline">{{ __('messages.pro_yearly_badge') }}</span>
	</span>
@elseif (auth()->user()->isMonthlySubscribed())
	<span
		class="inline-flex items-center border border-amber-500/80 md:border-2 text-xs md:text-sm rounded-lg md:rounded-xl text-amber-400 py-1 px-2 md:py-1.5 md:px-3.5 font-bold md:font-black tracking-wide shrink-0 whitespace-nowrap">
		<span class="md:hidden">PRO Mensual</span>
		<span class="hidden md:inline">{{ __('messages.pro_monthly_badge') }}</span>
	</span>
@else
	<a class="inline-flex items-center gap-1 border border-amber-500/80 md:border-2 text-xs md:text-sm hover:bg-amber-500/10 rounded-lg md:rounded-xl text-amber-400 py-1 px-2.5 md:py-1.5 md:px-3.5 font-bold md:font-black tracking-wide transition-all shrink-0 whitespace-nowrap active:scale-95"
	   href="{{ route('plans') }}">
		<svg class="w-3.5 h-3.5 md:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
			<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
		</svg>
		<span class="md:hidden">PRO</span>
		<span class="hidden md:inline">{{ __('messages.subscribe_to_pro') }}</span>
	</a>
@endif
