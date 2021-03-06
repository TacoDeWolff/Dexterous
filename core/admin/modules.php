<?php

if (!User::isAdmin())
{
	Common::responseCode(403);
	user_error('Forbidden access (' . $_SERVER['REQUEST_URI'] . ')', ERROR);
}

Core::addStyle('vendor/dropdown.css');

Hooks::emit('admin-header');
Core::render('admin/modules.tpl');
Hooks::emit('admin-footer');
exit;
