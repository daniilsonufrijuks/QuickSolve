@if (! empty($cv['skills']))
    <h2>Skills</h2>
    <p class="skills">
        @foreach ($cv['skills'] as $skill)
            <span>{{ $skill }}</span>
        @endforeach
    </p>
@endif
