<?php

if ($dex_conf->get('ssl') &&  $_SERVER['HTTPS'] != 'on')
{
	header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	exit();
}

$form = new Form('login');

$form->addSection(__('Log in'), __('You must log in before you can continue to the admin panel.'));
$form->addText('username', __('Username'), '', '', array('[a-zA-Z0-9-_]*', 3, 16, __('Must be a valid username')));
$form->addPassword('password', __('Password'), '');

$form->addSeparator();

$form->setSubmit('<i class="fa fa-sign-in"></i>&ensp;' . __('Log in'));
$form->setResponse('', __('Not logged in'));

if ($form->submitted())
{
	if ($form->validate())
	{
		if (($error = User::logIn($form->get('username'), $form->get('password'))) !== false)
			$form->appendError($error);
		else
		{
			if (isset($url[1]) && strpos($url[1], 'r=') === 0)
				$form->setRedirect('/' . Common::$base_url . rawurldecode(substr($url[1], 2)));
			else if (Common::$request_url == 'admin/logout/')
				$form->setRedirect('/' . Common::$base_url . 'admin/');
			else
				$form->setRedirect('/' . Common::$base_url . Common::$request_url);
		}
	}
	$form->finish();
}

Core::addTitle('Admin panel');

Hooks::emit('admin-header');

Core::set('login', $form);
Core::render('admin/login.tpl');

Hooks::emit('admin-footer');
exit;
