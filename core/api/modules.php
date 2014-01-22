<?php

if (!User::isAdmin())
    user_error('Forbidden access', ERROR);

if (API::action('enable_module') || API::action('disable_module'))
{
    if (!API::has('module_name'))
        user_error('No module name set', ERROR);

    Db::exec("
        UPDATE module SET enabled = '" . Db::escape(API::action('enable_module') ? '1' : '0') . "'
        WHERE module_name = '" . Db::escape(API::get('module_name')) . "';");
    API::finish();
}
else if (API::action('reinstall_module'))
{
    if (!API::has('module_name') || !is_file('modules/' . API::get('module_name') . '/admin/setup.php'))
        user_error('No module name set or module doesn\'t exist', ERROR);

    include_once('modules/' . API::get('module_name') . '/admin/setup.php');
    API::finish();
}
else if (API::action('get_modules'))
{
    $modules = array();
    $table = Db::query("SELECT * FROM module ORDER BY module_name ASC;");
    while ($row = $table->fetch())
    {
        $ini_filename = 'modules/' . $row['module_name'] . '/config.ini';
        $row['module_id'] = count($modules);
        if (is_file($ini_filename) && ($ini = parse_ini_file($ini_filename)) !== false)
        {
            $row['title'] = Common::tryOrEmpty($ini, 'title');
            $row['author'] = Common::tryOrEmpty($ini, 'author');
            $row['description'] = Common::tryOrEmpty($ini, 'description');
        }
        $modules[] = $row;
    }
    API::set('modules', $modules);
    API::finish();
}

?>