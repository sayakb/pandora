<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Collect some data
$username = $core->variable('username', '');
$password = $core->variable('password', '');

$login_submit = isset($_POST['login']);

// Log the user out if already logged in
if ($auth->is_logged_in)
{
    $auth->logout();

    $login_page = $nav->get('nav_login');
    $core->redirect($login_page);
}

// Login data was submitted
if ($login_submit)
{
    // Log in user
    $login_success = $auth->login($username, $password);

    // Check if login succeeded
    if ($login_success)
    {
        $homepage = $nav->get('nav_home');
        $core->redirect($homepage);
    }
    else
    {
        $show_error = true;
    }
}

// Assign skin data
$skin->assign(array(
    'error_visibility'      => isset($show_error) ? 'visible' : 'hidden',
));

// Assign the module data and title
$module_title = $lang->get('log_in');
$module_data = $skin->output('tpl_login');

?>