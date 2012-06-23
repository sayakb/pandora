<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Collect some data
$username = $core->variable('username', '', false, true);
$password = $core->variable('password', '');
$redir_url = $core->variable('r', '');

$login_submit = isset($_POST['login']);

// Log the user out if already logged in
if ($user->is_logged_in)
{
    $user->logout();
    $core->redirect('?q=login');
}

// Login data was submitted
if ($login_submit)
{
    if (!empty($username) && !empty($password))
    {
        // Check if user is banned
        $is_banned = $user->is_banned($username);

        // User isn't banned
        if (!$is_banned)
        {
            // Log in user
            $login_success = $user->login($username, $password);

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
            $error_message = $lang->get('account_banned');
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