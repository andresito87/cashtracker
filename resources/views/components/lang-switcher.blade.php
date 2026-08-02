@php
	$availableLocales = config('app.available_locales', ['en', 'es']);
	$currentLocale = app()->getLocale();
	$otherLocale = $availableLocales[0] === $currentLocale ? $availableLocales[1] : $availableLocales[0];
	$switchTitles = [
		'en' => __('messages.switch_to_english'),
		'es' => __('messages.switch_to_spanish'),
	];
@endphp

<a href="{{ request()->fullUrlWithQuery(['lang' => $otherLocale]) }}"
   class="flex items-center bg-[#13082a] p-0.5 sm:p-1 rounded-full border border-purple-500/40 shrink-0 shadow-inner hover:border-purple-400/70 transition-all duration-200 cursor-pointer"
   title="{{ $switchTitles[$otherLocale] ?? strtoupper($otherLocale) }}">
	@foreach ($availableLocales as $locale)
		<span
			class="px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-xs sm:text-sm font-bold transition-all duration-200 {{ $currentLocale === $locale ? 'bg-purple-600/60 text-white shadow-sm' : 'text-purple-300/50' }}">
            {{ strtoupper($locale) }}
        </span>
	@endforeach
</a>
