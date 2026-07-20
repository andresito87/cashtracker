
@php
    $classes = match($type) {
        'success' => 'bg-green-50 border border-green-200 text-green-800',
        'error' => 'bg-red-50 border border-red-200 text-red-800',
        default => 'bg-blue-50 border border-blue-200 text-blue-800',
    };
@endphp

<div {{ $attributes->merge(['class' => "mb-6 p-4 rounded-xl font-semibold flex items-center justify-center gap-2 shadow-sm text-sm sm:text-base $classes"]) }}>
    @if($type === 'success')
        <svg class="h-5 w-5 text-green-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    @elseif($type === 'error')
        <svg class="h-5 w-5 text-red-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    @else
        <svg class="h-5 w-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    @endif
    <span>{{ $message ?? $slot }}</span>
</div>