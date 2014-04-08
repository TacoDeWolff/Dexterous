<?php

$form = new Form('settings');

$form->addSection('Settings', 'General site settings');
$form->addText('title', 'Title', 'Displayed in the titlebar and site header', '', array('.*', 1, 25, 'Unknown error'));
$form->addMultilineText('subtitle', 'Slogan', 'Displayed below the title in the site header', '', array('(.|\n)*', 0, 200, 'Unknown error'));
$form->addMultilineText('description', 'Description', 'Only visible for search engines<br>Describe your site concisely', '', array('.*', 0, 80, 'Unknown error'));
$form->addArray('keywords', 'Keywords', 'Only visible for search engines<br>Enter keywords defining your site', array(), array('.*', 0, 80, 'Unknown error'));

$form->addSeparator();

$form->setResponse('Saved', 'Not saved');

if ($form->submitted())
{
	if ($form->validate())
	{
		Db::exec("BEGIN;
			UPDATE setting SET value = '" . Db::escape($form->get('title')) . "' WHERE key = 'title';
			UPDATE setting SET value = '" . Db::escape($form->get('subtitle')) . "' WHERE key = 'subtitle';
			UPDATE setting SET value = '" . Db::escape($form->get('description')) . "' WHERE key = 'description';
			UPDATE setting SET value = '" . Db::escape($form->get('keywords')) . "' WHERE key = 'keywords';
		COMMIT;");
	}
	$form->finish();
}

$settings = Db::query("SELECT * FROM setting;");
while ($setting = $settings->fetch())
	$form->set($setting['key'], $setting['value']);

Hooks::emit('admin-header');

Core::set('settings', $form);
Core::render('admin/settings.tpl');

Hooks::emit('admin-footer');
exit;
