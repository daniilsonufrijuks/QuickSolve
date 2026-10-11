<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CV {{ $cv['full_name'] }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #0f172a; font-size: 11px; margin: 28px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 12px; letter-spacing: 0.08em; text-transform: uppercase; margin: 16px 0 6px; border-bottom: 1px solid #cbd5e1; padding-bottom: 3px; }
        .muted { color: #475569; }
        .block { margin-bottom: 10px; }
        .role { font-weight: bold; }
        p { margin: 0 0 6px; line-height: 1.4; }
        .skills span { display: inline-block; border: 1px solid #cbd5e1; padding: 2px 6px; margin: 0 4px 4px 0; font-size: 10px; }
        .modern h1 { color: #1d4ed8; }
        .modern h2 { color: #1d4ed8; border-color: #93c5fd; }
        .compact h1 { font-size: 18px; }
        .compact h2 { margin-top: 10px; }
        .compact p, .compact .block { margin-bottom: 4px; }
        .sidebar { width: 32%; float: left; background: #0f172a; color: #f8fafc; padding: 12px; min-height: 700px; }
        .sidebar h1, .sidebar h2, .sidebar p { color: #f8fafc; }
        .sidebar h2 { border-color: #334155; }
        .main { margin-left: 36%; }
        .classic .contact { margin-bottom: 12px; }
        .executive { text-align: center; }
        .executive h1 { letter-spacing: 0.12em; text-transform: uppercase; font-size: 20px; }
        .executive .rule { border-top: 2px solid #b45309; width: 40%; margin: 8px auto 14px; }
        .executive .body, .executive .block, .executive h2 { text-align: left; }
        .timeline .row { margin-bottom: 10px; }
        .timeline .when { width: 22%; float: left; color: #475569; }
        .timeline .what { margin-left: 26%; }
        .creative { border-left: 10px solid #6d28d9; padding-left: 16px; }
        .creative h1 { font-size: 26px; color: #6d28d9; }
        .clear { clear: both; }
        .split-rail { width: 30%; float: left; background: #ecfeff; color: #134e4a; padding: 12px; min-height: 700px; }
        .split-rail h1, .split-rail h2 { color: #0f766e; }
        .split-rail h2 { border-color: #99f6e4; }
        .split-main { margin-left: 34%; }
        .banner-bar { background: #0f766e; color: #f8fafc; padding: 16px 14px; margin: -28px -28px 18px; }
        .banner-bar h1, .banner-bar p { color: #f8fafc; margin: 0 0 4px; }
        .mosaic h1 { color: #0369a1; }
        .mosaic-grid .left { width: 38%; float: left; }
        .mosaic-grid .right { margin-left: 42%; }
    </style>
</head>
<body class="{{ $cv['template'] }}">
@php
    $contact = collect([$cv['email'] ?? null, $cv['phone'] ?? null, $cv['location'] ?? null, $cv['website'] ?? null])->filter()->implode(' · ');
@endphp

@if ($cv['template'] === 'modern')
    <div class="sidebar">
        <h1>{{ $cv['full_name'] }}</h1>
        <p>{{ $cv['headline'] ?? '' }}</p>
        <h2>Contact</h2>
        <p>{{ $contact }}</p>
        @if (! empty($cv['skills']))
            <h2>Skills</h2>
            <p>{{ implode(', ', $cv['skills']) }}</p>
        @endif
    </div>
    <div class="main">
        @include('cv.partials.profile')
        <h2>Experience</h2>
        @include('cv.partials.experience')
        <h2>Education</h2>
        @include('cv.partials.education')
    </div>
@elseif ($cv['template'] === 'executive')
    <div class="executive">
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="muted">{{ $cv['headline'] ?? '' }}</p>
        <p class="muted">{{ $contact }}</p>
        <div class="rule"></div>
    </div>
    @include('cv.partials.profile')
    <h2>Experience</h2>
    @include('cv.partials.experience')
    <h2>Education</h2>
    @include('cv.partials.education')
    @include('cv.partials.skills')
@elseif ($cv['template'] === 'timeline')
    <h1>{{ $cv['full_name'] }}</h1>
    <p class="muted">{{ $cv['headline'] ?? '' }}</p>
    <p class="muted">{{ $contact }}</p>
    @include('cv.partials.profile')
    <h2>Experience</h2>
    @foreach ($cv['experience'] ?? [] as $job)
        <div class="row">
            <div class="when">{{ $job['dates'] ?? '' }}</div>
            <div class="what">
                <div class="role">{{ $job['role'] }}@if (! empty($job['company'])) · {{ $job['company'] }}@endif</div>
                <p>{!! nl2br(e($job['details'] ?? '')) !!}</p>
            </div>
            <div class="clear"></div>
        </div>
    @endforeach
    <h2>Education</h2>
    @foreach ($cv['education'] ?? [] as $item)
        <div class="row">
            <div class="when">{{ $item['dates'] ?? '' }}</div>
            <div class="what">
                <div class="role">{{ $item['school'] }}</div>
                <div class="muted">{{ $item['credential'] ?? '' }}</div>
            </div>
            <div class="clear"></div>
        </div>
    @endforeach
    @include('cv.partials.skills')
@elseif ($cv['template'] === 'creative')
    <h1>{{ $cv['full_name'] }}</h1>
    <p class="muted">{{ $cv['headline'] ?? '' }}</p>
    <p class="muted">{{ $contact }}</p>
    @include('cv.partials.profile')
    <h2>Experience</h2>
    @include('cv.partials.experience')
    <h2>Education</h2>
    @include('cv.partials.education')
    @include('cv.partials.skills')
@elseif ($cv['template'] === 'split')
    <div class="split-rail">
        <h1>{{ $cv['full_name'] }}</h1>
        <p>{{ $cv['headline'] ?? '' }}</p>
        <h2>Contact</h2>
        <p>{{ $contact }}</p>
        @include('cv.partials.skills')
        <h2>Education</h2>
        @include('cv.partials.education')
    </div>
    <div class="split-main">
        @include('cv.partials.profile')
        <h2>Experience</h2>
        @include('cv.partials.experience')
    </div>
@elseif ($cv['template'] === 'banner')
    <div class="banner-bar">
        <h1>{{ $cv['full_name'] }}</h1>
        <p>{{ $cv['headline'] ?? '' }}</p>
        <p>{{ $contact }}</p>
    </div>
    @include('cv.partials.profile')
    <h2>Experience</h2>
    @include('cv.partials.experience')
    <h2>Education</h2>
    @include('cv.partials.education')
    @include('cv.partials.skills')
@elseif ($cv['template'] === 'mosaic')
    <h1>{{ $cv['full_name'] }}</h1>
    <p class="muted">{{ $cv['headline'] ?? '' }}</p>
    <p class="muted">{{ $contact }}</p>
    @include('cv.partials.profile')
    <div class="mosaic-grid">
        <div class="left">
            <h2>Education</h2>
            @include('cv.partials.education')
            @include('cv.partials.skills')
        </div>
        <div class="right">
            <h2>Experience</h2>
            @include('cv.partials.experience')
        </div>
        <div class="clear"></div>
    </div>
@else
    <h1>{{ $cv['full_name'] }}</h1>
    <p class="muted">{{ $cv['headline'] ?? '' }}</p>
    <p class="contact muted">{{ $contact }}</p>
    @include('cv.partials.profile')
    <h2>Experience</h2>
    @include('cv.partials.experience')
    <h2>Education</h2>
    @include('cv.partials.education')
    @include('cv.partials.skills')
@endif
</body>
</html>
