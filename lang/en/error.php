<?php

return [
    'page' => [
        401 => ['title' => 'Please sign in', 'desc' => 'You need to sign in to see this page.'],
        402 => ['title' => 'Payment required', 'desc' => 'This page needs a payment before it can be shown.'],
        403 => ['title' => 'No access', 'desc' => 'Your account can’t open this page. Ask a manager if you need it.'],
        404 => ['title' => 'Page not found', 'desc' => 'This page has moved or no longer exists. Check the address, or start again from the front page.'],
        405 => ['title' => 'Not allowed', 'desc' => 'This page can’t be opened this way. Go back and try again.'],
        419 => ['title' => 'Page expired', 'desc' => 'The page was open too long without activity. Go back and reload it, then try again.'],
        429 => ['title' => 'Too many tries', 'desc' => 'Too many requests in a short time. Wait a minute, then try again.'],
        500 => ['title' => 'Something broke on our side', 'desc' => 'We couldn’t load this page. Try again in a moment; if it keeps happening, contact us.'],
        503 => ['title' => 'Back soon', 'desc' => 'We’re updating the site. Please come back in a few minutes.'],
        'generic' => ['title' => 'Something went wrong', 'desc' => 'An unexpected error stopped this page. Please try again later.'],
    ],
    'back' => 'Go back',
    'home' => 'Front page',
];
