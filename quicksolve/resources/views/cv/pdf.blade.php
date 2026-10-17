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
        .avatar-img, .avatar-mark { display: block; margin: 0 auto 12px; border-radius: 50%; }
        .avatar-mark { background: #94a3b8; color: #fff; text-align: center; font-size: 18px; font-weight: bold; }
        .col-side { width: 32%; float: left; color: #f8fafc; padding: 18px 14px; min-height: 760px; margin: -28px 0 -28px -28px; }
        .col-side h1, .col-side h2, .col-side p { color: #f8fafc; }
        .col-side h2 { border-color: rgba(255,255,255,0.28); color: #f8fafc; }
        .col-side .skills span { border-color: rgba(255,255,255,0.4); color: #f8fafc; }
        .col-main { margin-left: 36%; padding-top: 6px; }
        .col-main h1 { font-size: 26px; letter-spacing: 0.04em; }
        .atlantic .col-side { background: #1e3a5f; }
        .rose .col-side { background: #9f1239; }
        .forest .col-side { background: #3f6212; }
        .portrait-head { margin-bottom: 14px; }
        .portrait-head .avatar-img, .portrait-head .avatar-mark { float: left; margin: 0 16px 0 0; }
        .portrait h1 { font-size: 28px; letter-spacing: 0.08em; text-transform: uppercase; }
        .two-col .left { width: 34%; float: left; }
        .two-col .right { margin-left: 38%; }
        .mono-photo { width: 34%; float: left; background: #111827; min-height: 210px; margin: -28px 0 16px -28px; padding: 24px 12px; text-align: center; }
        .mono-name { margin-left: 38%; padding-top: 18px; }
        .mono h1 { font-size: 34px; line-height: 1.05; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 8px; }
        .academic { text-align: center; }
        .academic h1 { font-size: 26px; letter-spacing: 0.16em; text-transform: uppercase; }
        .academic .rule { border-top: 1px solid #334155; width: 70%; margin: 8px auto 12px; }
        .academic .tri { margin: 0 auto 14px; }
        .academic .tri td { width: 33%; text-align: center; font-size: 10px; color: #475569; }
        .academic .body, .academic .block, .academic h2, .academic p.skills { text-align: left; }
        .sand { background: #f5efe6; }
        .sand h2 { color: #b45309; border-color: #e8d5b5; }
        .sand-side { width: 32%; float: left; padding-right: 14px; }
        .sand-main { margin-left: 36%; }
        .slate-bar { background: #1e293b; color: #f8fafc; padding: 16px 14px; margin: -28px -28px 18px; }
        .slate-bar h1, .slate-bar p { color: #f8fafc; }
        .slate-bar .avatar-img, .slate-bar .avatar-mark { float: left; margin: 0 14px 0 0; }
        .slate-grid .left { width: 34%; float: left; }
        .slate-grid .right { margin-left: 38%; }
    </style>
</head>
<body class="{{ $cv['template'] }}{{ ! empty($theme) ? ' themed-body' : '' }}">
@php
    $contacts = collect([$cv['email'] ?? null, $cv['phone'] ?? null, $cv['location'] ?? null, $cv['website'] ?? null])->filter();
    $contact = $contacts->implode(' · ');
@endphp

@if (! empty($theme))
    @include('cv.partials.themed')
@elseif ($cv['template'] === 'modern')
    <div class="sidebar">
        @include('cv.partials.avatar', ['size' => 78])
        <h1>{{ $cv['full_name'] }}</h1>
        <p>{{ $cv['headline'] ?? '' }}</p>
        <h2>Contact</h2>
        @include('cv.partials.contact-stack')
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
        @include('cv.partials.avatar', ['size' => 72])
        <h1>{{ $cv['full_name'] }}</h1>
        <p>{{ $cv['headline'] ?? '' }}</p>
        <h2>Contact</h2>
        @include('cv.partials.contact-stack')
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
@elseif (in_array($cv['template'], ['atlantic', 'rose', 'forest'], true))
    <div class="col-side">
        @include('cv.partials.avatar', ['size' => 82])
        <h2>Contact</h2>
        @include('cv.partials.contact-stack')
        @include('cv.partials.skills')
        <h2>Education</h2>
        @include('cv.partials.education')
    </div>
    <div class="col-main">
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="muted">{{ $cv['headline'] ?? '' }}</p>
        @include('cv.partials.profile')
        <h2>Experience</h2>
        @include('cv.partials.experience')
    </div>
@elseif ($cv['template'] === 'portrait')
    <div class="portrait-head">
        @include('cv.partials.avatar', ['size' => 88])
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="muted">{{ $cv['headline'] ?? '' }}</p>
        <p class="muted">{{ $contact }}</p>
        <div class="clear"></div>
    </div>
    @include('cv.partials.profile')
    <div class="two-col">
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
@elseif ($cv['template'] === 'mono')
    <div class="mono-photo">
        @include('cv.partials.avatar', ['size' => 120])
    </div>
    <div class="mono-name">
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="muted">{{ $cv['headline'] ?? '' }}</p>
        <p class="muted">{{ $contact }}</p>
    </div>
    <div class="clear"></div>
    @include('cv.partials.profile')
    <h2>Experience</h2>
    @include('cv.partials.experience')
    <h2>Education</h2>
    @include('cv.partials.education')
    @include('cv.partials.skills')
@elseif ($cv['template'] === 'academic')
    <div class="academic">
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="muted">{{ $cv['headline'] ?? '' }}</p>
        <div class="rule"></div>
        <table class="tri" width="100%">
            <tr>
                <td>{{ $cv['phone'] ?? '' }}</td>
                <td>{{ $cv['email'] ?? '' }}</td>
                <td>{{ $cv['location'] ?? '' }}{{ ! empty($cv['website']) ? ' · '.$cv['website'] : '' }}</td>
            </tr>
        </table>
    </div>
    @include('cv.partials.profile')
    <h2>Experience</h2>
    @include('cv.partials.experience')
    <h2>Education</h2>
    @include('cv.partials.education')
    @include('cv.partials.skills')
@elseif ($cv['template'] === 'sand')
    <div class="sand-side">
        @include('cv.partials.avatar', ['size' => 86])
        <h2>Contact</h2>
        @include('cv.partials.contact-stack')
        <h2>Education</h2>
        @include('cv.partials.education')
        @include('cv.partials.skills')
    </div>
    <div class="sand-main">
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="muted">{{ $cv['headline'] ?? '' }}</p>
        @include('cv.partials.profile')
        <h2>Experience</h2>
        @include('cv.partials.experience')
    </div>
@elseif ($cv['template'] === 'slate')
    <div class="slate-bar">
        @include('cv.partials.avatar', ['size' => 76])
        <h1>{{ $cv['full_name'] }}</h1>
        <p>{{ $cv['headline'] ?? '' }}</p>
        <p>{{ $contact }}</p>
        <div class="clear"></div>
    </div>
    @include('cv.partials.profile')
    <div class="slate-grid">
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
