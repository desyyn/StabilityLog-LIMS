@props(['title' => null])

<div {{ $attributes->merge(['class' => 'card shadow-sm']) }}>
    @if($title)
        <div class="card-header">
            <h5 class="mb-0">{{ $title }}</h5>
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
</div>
