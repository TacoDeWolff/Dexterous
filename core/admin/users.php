<?php

if (!Session::isAdmin())
	user_error('Forbidden access (' . $_SERVER['REQUEST_URI'] . ')', ERROR);

if (!isset($url[2]))
{
	Core::addStyle('vendor/dropdown.min.css');

	Hooks::emit('admin-header');
	Core::render('admin/users.tpl');
	Hooks::emit('admin-footer');
	exit;
}
else
{
    if ($url[2] != 'new')
    {
		$user = $db->querySingle("SELECT * FROM user WHERE user_id = '" . $db->escape($url[2]) . "' LIMIT 1;");
		if (!$user)
			user_error('User ID "' . $url[2] . '" doesn\'t exist', ERROR);
	}

	$form = new Form('user');

	$form->addSection('User', 'These users can access the admin area. Admins have access to everything.');
	$form->addText('username', 'Username', '', '', array('[a-zA-Z0-9-_]*', 3, 16, 'Only alphanumeric and (-_) characters allowed'));
	$form->addEmail('email', 'Email address', 'Used for notifications and password recovery');
	$form->addPassword('password', 'Password', ($url[2] != 'new' ? 'Leave empty to keep current' : ''));
	$form->addPasswordConfirm('password2', 'password', 'Confirm password', '');
	$form->addDropdown('permission', 'Permission level', 'Restricts access', array('admin' => 'Admin', 'user' => 'User'));

	$form->addSeparator();

    if ($url[2] != 'new')
    {
		$form->addPassword('current_password', 'Current password', 'Confirm with your current password');
		$form->optional(array('password', 'password2'));
    }

	$form->setSubmit('<i class="fa fa-save"></i>&ensp;Save');
	$form->setResponse('Saved<span data-time=""></span>', 'Not saved');

	if ($form->submitted())
	{
		if ($form->validate())
		{
			$current_user = $db->querySingle("SELECT * FROM user WHERE user_id = '" . $db->escape(Session::getUserId()) . "' LIMIT 1;");
            if ($db->querySingle("SELECT * FROM user WHERE username = '" . $db->escape($form->get('username')) . "' AND user_id != '" . $db->escape($url[2]) . "' LIMIT 1;"))
                $form->setError('username', 'Already used');
            else if (!$current_user)
                $form->appendError('Unknown error');
            else if ($url[2] != 'new' && !Bcrypt::verify($form->get('current_password'), $current_user['password']))
                $form->setError('current_password', 'Wrong password');
            else if ($url[2] != 'new' && Session::getUserId() == $url[2] && $current_user['permission'] != $form->get('permission'))
                $form->setError('permission', 'Can\'t change your own permission level');
            else
				if ($url[2] != 'new')
				{
					if ($form->get('password') != '')
						$db->exec("
						UPDATE user SET
							username = '" . $db->escape($form->get('username')) . "',
							email = '" . $db->escape($form->get('email')) . "',
							password = '" . $db->escape(Bcrypt::hash($form->get('password'))) . "',
							permission = '" . $db->escape($form->get('permission')) . "'
						WHERE user_id = '" . $db->escape($url[2]) . "';");
					else
						$db->exec("
						UPDATE user SET
							username = '" . $db->escape($form->get('username')) . "',
							email = '" . $db->escape($form->get('email')) . "',
							permission = '" . $db->escape($form->get('permission')) . "'
						WHERE user_id = '" . $db->escape($url[2]) . "';");
				}
				else
				{
					$db->exec("
					INSERT INTO user (username, email, password, permission) VALUES (
						'" . $db->escape($form->get('username')) . "',
						'" . $db->escape($form->get('email')) . "',
						'" . $db->escape(Bcrypt::hash($form->get('password'))) . "',
						'" . $db->escape($form->get('permission')) . "'
					);");
					$form->setRedirect('/' . $base_url . 'admin/users/');
				}
		}
		$form->finish();
	}

	if ($url[2] != 'new')
	{
		$form->set('username', $user['username']);
		$form->set('email', $user['email']);
		$form->set('permission', $user['permission']);
	}

	Hooks::emit('admin-header');

	Core::assign('user', $form);
	Core::render('admin/user.tpl');

	Hooks::emit('admin-footer');
	exit;
}

?>
