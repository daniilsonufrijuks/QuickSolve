<?php

test('returns a successful response', function () {
    $this->get('/')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Home')
            ->has('demoMode')
            ->has('chatUsage')
            ->has('plans')
            ->has('seo.title')
            ->where('seo.robots', 'index,follow'));
});
