<?php

if (!User::loggedIn())
    user_error('Forbidden access', ERROR);

if (API::action('save'))
{
    if (!API::has('link_id') || !API::has('content'))
        user_error('No link ID or content set', ERROR);

    Db::exec("
    INSERT INTO content (link_id, name, content, modify_time) VALUES (
        '" . Db::escape(API::get('link_id')) . "',
        'content',
        '" . Db::escape(API::get('content')) . "',
        '" . Db::escape(time()) . "'
    );");

    API::finish();
}

?>