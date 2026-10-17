<?php

use App\Services\Documents\CvThemeCatalog;

test('builtin color layouts ignore client theme colors', function () {
    $theme = CvThemeCatalog::resolve('coral', [
        'layout' => 'stacked',
        'paper' => '#000000',
        'ink' => '#000000',
        'muted' => '#000000',
        'accent' => '#000000',
        'sidebar' => '#000000',
        'sidebar_ink' => '#000000',
        'heading' => '#000000',
        'type' => 'serif',
        'name_size' => 'xl',
        'photo' => 'hidden',
        'header_align' => 'center',
        'skills' => 'list',
    ]);

    expect($theme['id'])->toBe('coral')
        ->and($theme['accent'])->toBe('#e07a5f')
        ->and($theme['layout'])->toBe('sidebar_left');
});

test('custom cv themes drop css that is not a hex color', function () {
    $theme = CvThemeCatalog::sanitize([
        'name' => '<b>Mine</b>',
        'layout' => 'not-a-layout',
        'paper' => 'white',
        'ink' => '#3f2e2a',
        'muted' => '#8d6e68',
        'accent' => 'red;background:url(javascript:alert(1))',
        'sidebar' => '#e07a5f',
        'sidebar_ink' => '#fff7f3',
        'heading' => '#c45c40',
        'type' => 'comic',
        'name_size' => 'huge',
        'photo' => 'svg',
        'header_align' => 'justify',
        'skills' => 'script',
    ]);

    expect($theme['id'])->toBe('custom')
        ->and($theme['name'])->toBe('Mine')
        ->and($theme['layout'])->toBe('sidebar_left')
        ->and($theme['paper'])->toBe('#fff8f4')
        ->and($theme['accent'])->toBe('#e07a5f')
        ->and($theme['type'])->toBe('sans');
});

test('classic layouts do not resolve a color theme', function () {
    expect(CvThemeCatalog::resolve('atlantic', null))->toBeNull();
});

test('screenshot color layouts keep their structure', function () {
    expect(CvThemeCatalog::resolve('citrus', null)['layout'])->toBe('corner_frame')
        ->and(CvThemeCatalog::resolve('violet', null)['layout'])->toBe('split_panel')
        ->and(CvThemeCatalog::resolve('harbor', null)['layout'])->toBe('centered_portrait');
});
