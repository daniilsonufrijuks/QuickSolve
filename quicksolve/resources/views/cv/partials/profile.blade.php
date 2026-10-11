@if (! empty($cv['summary']))
    <h2>Profile</h2>
    <p>{!! nl2br(e($cv['summary'])) !!}</p>
@endif
