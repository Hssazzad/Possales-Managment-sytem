<?php

test('the application redirects to dashboard', function () {
    $response = $this->get('/');

    $response->assertRedirect('/dashboard');
});
