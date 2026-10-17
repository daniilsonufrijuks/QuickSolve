@if (! empty($cv['skills']))
    <h2>Skills</h2>
    @if (($theme['skills'] ?? 'chips') === 'list')
        <p>{{ implode(', ', $cv['skills']) }}</p>
    @else
        <p class="t-skills">
            @foreach ($cv['skills'] as $skill)
                <span>{{ $skill }}</span>
            @endforeach
        </p>
    @endif
@endif
