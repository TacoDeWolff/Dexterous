<?php

Hooks::attach('site-header', -1, function () {
    Theme::set('simple');

    Theme::addExternalStyle('http://yui.yahooapis.com/pure/0.3.0/pure-min.css');
    Theme::addStyle('top-menu.css');
    Theme::addExternalScript('//ajax.googleapis.com/ajax/libs/jquery/2.0.3/jquery.min.js');
    Theme::addDeferredScript('script.js');
});

Hooks::attach('navigation', -1, function () {
    global $base_url, $settings;
    echo '<a href="/' . $base_url . '" class="logo">' . $settings['title'] . '</a>';
});

Hooks::attach('header', 0, function() {
    global $settings;

    if (isset($settings['title']))
        echo '<h1>' . $settings['title'] . '</h1>';
    if (isset($settings['subtitle']))
        echo '<h2>' . nl2br($settings['subtitle']) . '</h2>';
});