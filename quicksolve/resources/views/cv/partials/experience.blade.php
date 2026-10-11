@foreach ($cv['experience'] ?? [] as $job)
    <div class="block">
        <div class="role">{{ $job['role'] }}@if (! empty($job['company'])) · {{ $job['company'] }}@endif</div>
        <div class="muted">{{ $job['dates'] ?? '' }}</div>
        <p>{!! nl2br(e($job['details'] ?? '')) !!}</p>
    </div>
@endforeach
