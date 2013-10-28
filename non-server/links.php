<?php

if (!Session::isAdmin())
	user_error('Forbidden access (' . $_SERVER['REQUEST_URI'] . ')', ERROR);

if (!isset($url[2]) || $url[2] == 'remove')
{
	if (isset($url[2]) && $url[2] == 'remove' && isset($url[3]))
		$db->exec("DELETE FROM link WHERE link_id = '" . $db->escape($url[3]) . "';");

	$links = array();
	$table = $db->query("SELECT * FROM link;");
	while ($row = $table->fetch())
	{
		$module_names = array();
		$table_link_module = $db->query("SELECT * FROM link_module WHERE link_id = '" . $db->escape($row['id']) . "';");
		while ($row_link_module = $table_link_module->fetch())
		{
			$ini_filename = "modules/" . $row_link_module['module_name'] . "/" . $row_link_module['module_name'] . ".ini";
			if ($row_link_module['module_name'] == '0')
				$module_names[] = '- None -';
			else if (file_exists($ini_filename) && ($ini = parse_ini_file($ini_filename)) !== false)
				$module_names[] = $ini['title'];
			else
				$module_names[] = $row_link_module['module_name'];
		}

		$row['module_names'] = implode(', ', $module_names);
		$links[] = $row;
	}

	Core::addStyle('resources/styles/popbox.css');
	Core::addStyle('resources/styles/dropdown.css');
	Core::addDeferredScript('resources/scripts/popbox.js');
	Core::addDeferredScript('resources/scripts/dropdown.js');

	Hooks::emit('admin_header');

	Core::assign('links', $links);
	Core::render('admin/links.tpl');

	Hooks::emit('admin_footer');
	exit;
}
else
{
	$dropbox_modules = array('0' => '- None -');
	$modules = $db->query("SELECT * FROM module;");
	while ($module = $modules->fetch()) {
		$ini_filename = "modules/" . $module['module_name'] . "/" . $module['module_name'] . ".ini";
		if (file_exists($ini_filename) && ($ini = parse_ini_file($ini_filename)) !== false)
			$dropbox_modules[$module['module_name']] = $ini['title'];
		else
			$dropbox_modules[$module['module_name']] = '(' . $module['module_name'] . ')';
	}

	$form = new Form('link');

	$form->addSection('Link', 'Every URL typed in the address bar is processed and the correct module is loaded. Below you can define what module is loaded when the specified link is requested. Make sure the link is meaningful since its valuable for users and search engines.');
	$form->addText('url', 'URL', $domain_url . $base_url, '', array('([a-zA-Z0-9\s_\\\\\/\[\]\(\)\|\?\+\-\*\{\},:\^=!\<\>#\$]*\/)?', 0, 50, 'Must be valid link and end with /'));
	$form->addText('title', 'Title', 'As displayed in links and titlebar', '', array('[a-zA-Z0-9\s_\/\-]*', 0, 20, 'May contain alphanumeric characters, spaces and (_/-)'));
	//$form->addDropdown('module_name', 'Module', 'Module to load', $dropbox_modules);
	//$form->addParameters('module_params', 'Parameters', '');

	$form->addSeparator();
	$form->addSubmit('link', '<i class="icon-save"></i>&ensp;Save');

	if ($form->submittedBy('link'))
	{
		if ($form->verifyPost())
		{
            if ($db->querySingle("SELECT * FROM link WHERE url = '" . $db->escape($form->get('url')) . "' AND id != '" . $db->escape($url[2]) . "' LIMIT 1;"))
                $form->setError('page_link', 'Already used');
            else if (substr($form->get('link'), 0, 6) == 'admin/')
                $form->setError('link', 'Cannot start with "admin/"');
            else
            {
				if ($url[2] != 'new')
				{
					$db->exec("
					UPDATE link SET
						url = '" . $db->escape($form->get('url')) . "',
						title = '" . $db->escape($form->get('title')) . "'
					WHERE id = '" . $db->escape($url[2]) . "';");
				}
				else
				{
					$db->exec("
					INSERT INTO link (url, title, module_name, module_params) VALUES (
						'" . $db->escape($form->get('url')) . "',
						'" . $db->escape($form->get('title')) . "'
					);");
					$form->setAction('/' . $base_url . 'admin/links/' . $db->last_id() . '/');
				}

				$form->setResponse('<span class="passed_time" data-time="' . time() . '">(<span></span>)</span>');
			}
		}
		$form->postToSession();

		if ($url[2] != 'new')
			Core::assign('view', $form->get('url'));
	}
	else
	{
		if ($url[2] != 'new')
		{
			$link = $db->querySingle("SELECT * FROM links WHERE id = '" . $db->escape($url[2]) . "' LIMIT 1;");
			if ($link === false)
				Hooks::emit('error', 404);

			$form->set('page_link', $link['link']);
			$form->set('title', $link['title']);

			Core::assign('view', $link['link']);
		}
	}

	Hooks::emit('admin_header');

	$form->sessionToForm();

	Core::assign('link', $form);
	Core::render('admin/link.tpl');

	Hooks::emit('admin_footer');
	exit;
}

?>
