@foreach ($cv['education'] ?? [] as $item)
    <div class="block">
        <div class="role">{{ $item['school'] }}</div>
        <div class="muted">{{ $item['credential'] ?? '' }} {{ $item['dates'] ?? '' }}</div>
    </div>
@endforeach
