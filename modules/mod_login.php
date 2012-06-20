<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Collect some data
$username = $core->variable('username', '');
$password = $core->variable('password', '');
$redir_url = $core->variable('r', '');

$login_submit = isset($_POST['login']);

// Log the user out if already logged in
if ($auth->is_logged_in)
{
    $auth->logout();
    $core->redirect('?q=login');
}

// Login data was submitted
if ($login_submit)
{
    if (!empty($username) && !empty($password))
    {
        // Log in user
        $login_success = $auth->login($username, $password);

        // Check if login succeeded
        if ($login_success)
        {
            $url = !empty($redir_url) ? urldecode($redir_url) : $core->path();
            $core->redirect($url);
        }
        else
        {
            $error_message = $lang->get('login_error');
        }
    }
    else
    {
        $error_message = $lang->get('enter_user_pw');
    }
}

// Assign skin data
$skin->assign(array(
    'error_message'         => isset($error_message) ? $error_message : '',
    'error_visibility'      => $skin->visibility(isset($error_message)),
));

// Assign the module data and title
$module_title = $lang->get('log_in');
$module_data = $skin->output('tpl_login');

?>