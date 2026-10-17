@php
    $layout = $theme['layout'];
    $showPhoto = ($theme['photo'] ?? 'circle') !== 'hidden';
    $photoShape = $theme['photo'] ?? 'circle';
    $nameSize = match ($theme['name_size'] ?? 'lg') {
        'md' => '22px',
        'xl' => '32px',
        default => '26px',
    };
    $font = ($theme['type'] ?? 'sans') === 'serif' ? 'DejaVu Serif, serif' : 'DejaVu Sans, sans-serif';
    $align = ($theme['header_align'] ?? 'left') === 'center' ? 'center' : 'left';
    $contacts = collect([$cv['email'] ?? null, $cv['phone'] ?? null, $cv['location'] ?? null, $cv['website'] ?? null])->filter();
@endphp
<style>
    .themed-body { margin: 0; padding: 0; }
    .t-page { font-family: {{ $font }}; color: {{ $theme['ink'] }}; background: {{ $theme['paper'] }}; font-size: 11px; }
    .t-page h1 { margin: 0 0 4px; font-size: {{ $nameSize }}; color: {{ $theme['ink'] }}; }
    .t-page h2 { font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; margin: 14px 0 6px; padding-bottom: 3px; border-bottom: 1px solid {{ $theme['accent'] }}; color: {{ $theme['heading'] }}; }
    .t-page p { margin: 0 0 6px; line-height: 1.4; }
    .t-muted { color: {{ $theme['muted'] }}; }
    .t-side { width: 32%; padding: 18px 14px; min-height: 760px; background: {{ $theme['sidebar'] }}; color: {{ $theme['sidebar_ink'] }}; }
    .t-side h1, .t-side h2, .t-side p { color: {{ $theme['sidebar_ink'] }}; }
    .t-side h2 { border-color: {{ $theme['accent'] }}; color: {{ $theme['sidebar_ink'] }}; }
    .t-side .skills span { border-color: {{ $theme['sidebar_ink'] }}; color: {{ $theme['sidebar_ink'] }}; }
    .t-side-left { float: left; }
    .t-side-right { float: right; }
    .t-main-left { margin-left: 36%; padding: 22px 22px 22px 0; }
    .t-main-right { margin-right: 36%; padding: 22px 0 22px 22px; }
    .t-band { background: {{ $theme['sidebar'] }}; color: {{ $theme['sidebar_ink'] }}; padding: 18px 22px; text-align: {{ $align }}; }
    .t-band h1, .t-band p { color: {{ $theme['sidebar_ink'] }}; }
    .t-pad { padding: 18px 22px 22px; }
    .t-stack { padding: 28px 32px; text-align: {{ $align }}; }
    .t-stack .t-left { text-align: left; }
    .t-rule { border-top: 2px solid {{ $theme['accent'] }}; width: 36%; margin: 8px {{ $align === 'center' ? 'auto' : '0' }} 14px; }
    .t-photo { margin-bottom: 12px; }
    .t-photo .avatar-img, .t-photo .avatar-mark { {{ $align === 'center' ? 'margin-left:auto;margin-right:auto;' : '' }} {{ $photoShape === 'rounded' ? 'border-radius:12px;' : 'border-radius:50%;' }} }
    .t-row .left { width: 34%; float: left; }
    .t-row .right { margin-left: 38%; }
    .t-skills span { display: inline-block; border: 1px solid {{ $theme['accent'] }}; color: {{ $theme['heading'] }}; padding: 2px 6px; margin: 0 4px 4px 0; font-size: 10px; }
    .t-page-rel { position: relative; overflow: hidden; min-height: 800px; }
    .t-corner-tl { position: absolute; top: 0; left: 0; width: 0; height: 0; border-top: 70px solid {{ $theme['accent'] }}; border-right: 90px solid transparent; }
    .t-corner-tr { position: absolute; top: 0; right: 0; width: 0; height: 0; border-top: 70px solid {{ $theme['accent'] }}; border-left: 90px solid transparent; }
    .t-corner-bl { position: absolute; bottom: 0; left: 0; width: 0; height: 0; border-bottom: 90px solid {{ $theme['sidebar'] }}; border-right: 110px solid transparent; }
    .t-frame { padding: 36px 32px 48px; }
    .t-frame-head .name { width: 70%; float: left; }
    .t-frame-head .pic { width: 26%; float: right; text-align: right; }
    .t-when { width: 28%; float: left; color: {{ $theme['muted'] }}; font-size: 10px; }
    .t-what { margin-left: 32%; }
    .t-split-left { width: 40%; float: left; background: {{ $theme['paper'] }}; min-height: 800px; padding: 28px 18px; }
    .t-split-right { width: 60%; float: right; background: {{ $theme['sidebar'] }}; color: {{ $theme['sidebar_ink'] }}; min-height: 800px; padding: 28px 18px; }
    .t-split-right h1, .t-split-right h2, .t-split-right p { color: {{ $theme['sidebar_ink'] }}; }
    .t-split-right h2 { border-color: {{ $theme['sidebar_ink'] }}; color: {{ $theme['sidebar_ink'] }}; }
    .t-display { font-size: 26px; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 6px; }
    .t-track { display: block; height: 5px; background: {{ $theme['heading'] }}; margin: 3px 0 10px; }
    .t-harbor { padding: 28px 32px; text-align: center; }
    .t-harbor .t-left { text-align: left; }
    .t-harbor h1 { color: {{ $theme['heading'] }}; }
    .t-pills span { background: {{ $theme['accent'] }}; color: #ffffff; border: 0; border-radius: 12px; padding: 3px 8px; }
    .t-italic { font-style: italic; color: {{ $theme['muted'] }}; }
</style>
<div class="t-page">
@if (in_array($layout, ['sidebar_left', 'sidebar_right'], true))
    <div class="t-side {{ $layout === 'sidebar_right' ? 't-side-right' : 't-side-left' }}">
        @if ($showPhoto)
            <div class="t-photo">@include('cv.partials.avatar', ['size' => 82])</div>
        @endif
        @if ($layout === 'sidebar_right')
            <h1>{{ $cv['full_name'] }}</h1>
            <p>{{ $cv['headline'] ?? '' }}</p>
        @endif
        <h2>Contact</h2>
        @include('cv.partials.contact-stack')
        @include('cv.partials.themed-skills', ['onSidebar' => true])
        <h2>Education</h2>
        @include('cv.partials.education')
    </div>
    <div class="{{ $layout === 'sidebar_right' ? 't-main-right' : 't-main-left' }}">
        @if ($layout !== 'sidebar_right')
            <h1>{{ $cv['full_name'] }}</h1>
            <p class="t-muted">{{ $cv['headline'] ?? '' }}</p>
        @endif
        @include('cv.partials.profile')
        <h2>Experience</h2>
        @include('cv.partials.experience')
    </div>
    <div class="clear"></div>
@elseif ($layout === 'header_band')
    <div class="t-band">
        @if ($showPhoto)
            <div class="t-photo">@include('cv.partials.avatar', ['size' => 76])</div>
        @endif
        <h1>{{ $cv['full_name'] }}</h1>
        <p>{{ $cv['headline'] ?? '' }}</p>
        <p>{{ $contacts->implode(' · ') }}</p>
    </div>
    <div class="t-pad">
        @include('cv.partials.profile')
        <div class="t-row">
            <div class="left">
                <h2>Education</h2>
                @include('cv.partials.education')
                @include('cv.partials.themed-skills', ['onSidebar' => false])
            </div>
            <div class="right">
                <h2>Experience</h2>
                @include('cv.partials.experience')
            </div>
            <div class="clear"></div>
        </div>
    </div>
@elseif ($layout === 'corner_frame')
    <div class="t-page-rel">
        <div class="t-corner-tl"></div>
        <div class="t-corner-tr"></div>
        <div class="t-corner-bl"></div>
        <div class="t-frame">
            <div class="t-frame-head">
                <div class="name">
                    <h1>{{ $cv['full_name'] }}</h1>
                    <p class="t-italic">{{ $cv['headline'] ?? '' }}</p>
                    <p class="t-muted">{{ $contacts->implode(' · ') }}</p>
                </div>
                @if ($showPhoto)
                    <div class="pic t-photo">@include('cv.partials.avatar', ['size' => 96])</div>
                @endif
                <div class="clear"></div>
            </div>
            @include('cv.partials.profile')
            <h2>Professional experience</h2>
            @foreach ($cv['experience'] ?? [] as $job)
                <div class="t-time">
                    <div class="t-when">{{ $job['dates'] ?? '' }}</div>
                    <div class="t-what">
                        <div class="role">{{ $job['company'] ?? '' }}@if (! empty($job['company']) && ! empty($job['role'])), @endif{{ $job['role'] }}</div>
                        <p>{!! nl2br(e($job['details'] ?? '')) !!}</p>
                    </div>
                    <div class="clear"></div>
                </div>
            @endforeach
            <h2>Education</h2>
            @foreach ($cv['education'] ?? [] as $item)
                <div class="t-time">
                    <div class="t-when">{{ $item['dates'] ?? '' }}</div>
                    <div class="t-what">
                        <div class="role">{{ $item['credential'] ?? '' }}</div>
                        <p class="t-muted">{{ $item['school'] }}</p>
                    </div>
                    <div class="clear"></div>
                </div>
            @endforeach
            @include('cv.partials.themed-skills', ['onSidebar' => false])
        </div>
    </div>
@elseif ($layout === 'split_panel')
    <div class="t-split-left">
        <div class="t-display">{{ $cv['full_name'] }}</div>
        <p class="t-italic">{{ $cv['headline'] ?? '' }}</p>
        @if ($showPhoto)
            <div class="t-photo">@include('cv.partials.avatar', ['size' => 110])</div>
        @endif
        @include('cv.partials.contact-stack')
        <h2>Skills</h2>
        @foreach ($cv['skills'] ?? [] as $skill)
            <p>{{ $skill }}<span class="t-track"></span></p>
        @endforeach
    </div>
    <div class="t-split-right">
        @include('cv.partials.profile')
        <h2>Professional experience</h2>
        @include('cv.partials.experience')
        <h2>Education</h2>
        @include('cv.partials.education')
    </div>
    <div class="clear"></div>
@elseif ($layout === 'centered_portrait')
    <div class="t-harbor">
        @if ($showPhoto)
            <div class="t-photo">@include('cv.partials.avatar', ['size' => 110])</div>
        @endif
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="t-italic">{{ $cv['headline'] ?? '' }}</p>
        <p class="t-muted">{{ $contacts->implode(' · ') }}</p>
        <div class="t-left">
            @include('cv.partials.profile')
            <h2>Professional experience</h2>
            @foreach ($cv['experience'] ?? [] as $job)
                <div class="t-time">
                    <div class="t-when">{{ $job['dates'] ?? '' }}</div>
                    <div class="t-what">
                        <div class="role">{{ $job['role'] }}@if (! empty($job['company'])), {{ $job['company'] }}@endif</div>
                        <p>{!! nl2br(e($job['details'] ?? '')) !!}</p>
                    </div>
                    <div class="clear"></div>
                </div>
            @endforeach
            <h2>Education</h2>
            @foreach ($cv['education'] ?? [] as $item)
                <div class="t-time">
                    <div class="t-when">{{ $item['dates'] ?? '' }}</div>
                    <div class="t-what">
                        <div class="role">{{ $item['credential'] ?? '' }}</div>
                        <p class="t-muted">{{ $item['school'] }}</p>
                    </div>
                    <div class="clear"></div>
                </div>
            @endforeach
            @if (! empty($cv['skills']))
                <h2>Skills</h2>
                <p class="t-skills t-pills">
                    @foreach ($cv['skills'] as $skill)
                        <span>{{ $skill }}</span>
                    @endforeach
                </p>
            @endif
        </div>
    </div>
@elseif ($layout === 'photo_top')
    <div class="t-pad">
        <table width="100%">
            <tr>
                @if ($showPhoto)
                    <td width="110" valign="top">@include('cv.partials.avatar', ['size' => 88])</td>
                @endif
                <td valign="middle">
                    <h1>{{ $cv['full_name'] }}</h1>
                    <p class="t-muted">{{ $cv['headline'] ?? '' }}</p>
                    <p class="t-muted">{{ $contacts->implode(' · ') }}</p>
                </td>
            </tr>
        </table>
        <div class="t-rule"></div>
        @include('cv.partials.profile')
        <h2>Experience</h2>
        @include('cv.partials.experience')
        <div class="t-row">
            <div class="left">
                <h2>Education</h2>
                @include('cv.partials.education')
            </div>
            <div class="right">
                @include('cv.partials.themed-skills', ['onSidebar' => false])
            </div>
            <div class="clear"></div>
        </div>
    </div>
@else
    <div class="t-stack">
        @if ($showPhoto)
            <div class="t-photo">@include('cv.partials.avatar', ['size' => 86])</div>
        @endif
        <h1>{{ $cv['full_name'] }}</h1>
        <p class="t-muted">{{ $cv['headline'] ?? '' }}</p>
        <p class="t-muted">{{ $contacts->implode(' · ') }}</p>
        <div class="t-rule"></div>
        <div class="t-left">
            @include('cv.partials.profile')
            <h2>Experience</h2>
            @include('cv.partials.experience')
            <h2>Education</h2>
            @include('cv.partials.education')
            @include('cv.partials.themed-skills', ['onSidebar' => false])
        </div>
    </div>
@endif
</div>
