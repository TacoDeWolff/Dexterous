<?php

if (Common::isMethod('POST'))
{
	$data = Common::getMethodData();
	if (!isset($data['subject']) || !Session::isAdmin())
		user_error('Subject not set or current user is no admin', ERROR);

	if ($data['subject'] == 'logs')
	{
		$handle = opendir('logs/');
		while (($log_name = readdir($handle)) !== false)
			if ($log_name != '.gitignore' && is_file('logs/' . $log_name))
				unlink('logs/' . $log_name);
	}
	else if ($data['subject'] == 'cache')
	{
		$handle = opendir('cache/');
		while (($cache_name = readdir($handle)) !== false)
			if ($cache_name != '.gitignore' && is_file('cache/' . $cache_name))
				unlink('cache/' . $cache_name);
	}
	exit;
}

if (isset($url[2]) && $url[2] == 'logs' && isset($url[3]) && $url[3] == 'view')
{
	$log = file_get_contents('logs/' . Log::getCurrentFilename());

	Hooks::emit('admin_header');

	Core::assign('log', $log);
	Core::render('admin/log.tpl');

	Hooks::emit('admin_footer');
	exit;
}

$logs_size = 0;
$handle = opendir('logs/');
while (($log_name = readdir($handle)) !== false)
	if ($log_name != '.gitignore' && is_file('logs/' . $log_name))
		$logs_size += filesize('logs/' . $log_name);

$cache_size = 0;
$handle = opendir('cache/');
while (($cache_name = readdir($handle)) !== false)
	if ($cache_name != '.gitignore' && is_file('cache/' . $cache_name))
		$cache_size += filesize('cache/' . $cache_name);

Core::addStyle('popbox.css');

Hooks::emit('admin_header');

Core::assign('log_name_current', Log::getCurrentFilename());
Core::assign('logs_size', Common::formatBytes($logs_size));
Core::assign('logs_size_percentage', number_format(100 * $logs_size / 50 / 1000 / 1000, 1));
Core::assign('cache_size', Common::formatBytes($cache_size));
Core::assign('cache_size_percentage', number_format(100 * $cache_size / 250 / 1000 / 1000, 1));
Core::render('admin/index.tpl');

Hooks::emit('admin_footer');
exit;

?>
