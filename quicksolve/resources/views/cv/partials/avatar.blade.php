@php
    $avatarSize = $size ?? 72;
    $photo = $cv['photo'] ?? null;
    $hasPhoto = is_string($photo) && preg_match('/^data:image\/jpeg;base64,[A-Za-z0-9\/+=]+$/', $photo) === 1;
    $initials = collect(preg_split('/\s+/u', trim((string) ($cv['full_name'] ?? ''))))
        ->filter()
        ->take(2)
        ->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))
        ->implode('');
@endphp
@if ($hasPhoto)
    <img src="{{ $photo }}" width="{{ $avatarSize }}" height="{{ $avatarSize }}" class="avatar-img" alt="">
@else
    <div class="avatar-mark" style="width: {{ $avatarSize }}px; height: {{ $avatarSize }}px; line-height: {{ $avatarSize }}px;">{{ $initials }}</div>
@endif
