@props(['messages'])

@if ($messages)
    <div {{ $attributes->merge(['class' => 'text-red-500 text-sm mt-1']) }}>
        @foreach ((array) $messages as $message)
            <p>{{ $message }}</p>
        @endforeach
    </div>
@endif