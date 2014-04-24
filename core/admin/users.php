<?php

if (!User::isAdmin())
{
	Common::responseCode(403);
	user_error('Forbidden access (' . $_SERVER['REQUEST_URI'] . ')', ERROR);
}

if (!isset($url[2]))
{
	Core::addStyle('vendor/dropdown.css');

	Hooks::emit('admin-header');
	Core::render('admin/users.tpl');
	Hooks::emit('admin-footer');
	exit;
}
else
{
	if ($url[2] != 'new')
	{
		$user = Db::singleQuery("SELECT * FROM user WHERE user_id = '" . Db::escape($url[2]) . "' LIMIT 1;");
		if (!$user)
		{
			Common::responseCode(404);
			user_error('User ID "' . $url[2] . '" doesn\'t exist', ERROR);
		}
		$current_user = $user;
	}

	if (User::getUserId() != $url[2])
	{
		$current_user = Db::singleQuery("SELECT * FROM user WHERE user_id = '" . Db::escape(User::getUserId()) . "' LIMIT 1;");
		if (!$current_user)
			user_error('Current user ID "' . User::getUserId() . '" doesn\'t exist', ERROR);
	}

	$form = new Form('user');

	$form->addSection(_('User'), _('These users can access the admin area. Admins have access to everything while editors have access only to a few admin pages related to editing content.'));
	$form->addText('username', _('Username'), '', '', array('[a-zA-Z0-9-_]*', 3, 16, _('Only alphanumeric and (-_) characters allowed')));
	$form->addEmail('email', _('Email address'), _('Used for notifications and password recovery'));
	$form->addPassword('password', _('Password'), ($url[2] != 'new' ? _('Leave empty to keep current') : ''));
	$form->addPasswordConfirm('password2', 'password', _('Confirm password'), '');

	if (User::getUserId() == $url[2])
		$form->addRadios('role', _('Role'), '', array($current_user['role'] => ucfirst($current_user['role'])));
	else
	{
		$form->addRadios('role', _('Role'), '', array('admin' => _('Admin'), 'editor' => _('Editor')));
		$form->set('role', 'editor');
	}
	$form->addSeparator();

	if ($url[2] != 'new')
	{
		$form->addPassword('current_password', _('Admin password'), _('Confirm with your password'));
		$form->optional(array('password', 'password2'));
	}

	$form->setSubmit('<i class="fa fa-save"></i>&ensp;' . _('Save'));
	$form->setResponse('', _('Not saved'));

	if ($form->submitted())
	{
		if ($form->validate())
		{
			if (Db::singleQuery("SELECT * FROM user WHERE username = '" . Db::escape($form->get('username')) . "' AND user_id != '" . Db::escape($url[2]) . "' LIMIT 1;"))
				$form->setError('username', _('Already used'));
			else if (Db::singleQuery("SELECT * FROM user WHERE email = '" . Db::escape($form->get('email')) . "' AND user_id != '" . Db::escape($url[2]) . "' LIMIT 1;"))
				$form->setError('email', _('Already used'));
			else if ($url[2] != 'new' && !Bcrypt::verify($form->get('current_password'), $current_user['password']))
				$form->setError('current_password', _('Wrong password'));
			else if ($url[2] != 'new' && User::getUserId() == $url[2] && $current_user['role'] != $form->get('role'))
				$form->setError('role', _('Can\'t change your own role'));
			else
			{
				if ($url[2] != 'new')
				{
					if ($form->get('password') != '')
						Db::exec("
						UPDATE user SET
							username = '" . Db::escape($form->get('username')) . "',
							email = '" . Db::escape($form->get('email')) . "',
							password = '" . Db::escape(Bcrypt::hash($form->get('password'))) . "',
							role = '" . Db::escape($form->get('role')) . "'
						WHERE user_id = '" . Db::escape($url[2]) . "';");
					else
						Db::exec("
						UPDATE user SET
							username = '" . Db::escape($form->get('username')) . "',
							email = '" . Db::escape($form->get('email')) . "',
							role = '" . Db::escape($form->get('role')) . "'
						WHERE user_id = '" . Db::escape($url[2]) . "';");
				}
				else
					Db::exec("
					INSERT INTO user (username, email, password, role) VALUES (
						'" . Db::escape($form->get('username')) . "',
						'" . Db::escape($form->get('email')) . "',
						'" . Db::escape(Bcrypt::hash($form->get('password'))) . "',
						'" . Db::escape($form->get('role')) . "'
					);");
				$form->setRedirect('/' . Common::$base_url . 'admin/users/');
			}
		}
		$form->finish();
	}

	if ($url[2] != 'new')
	{
		$form->set('username', $user['username']);
		$form->set('email', $user['email']);
		$form->set('role', $user['role']);
	}

	Hooks::emit('admin-header');

	Core::set('user', $form);
	Core::render('admin/user.tpl');

	Hooks::emit('admin-footer');
	exit;
}
