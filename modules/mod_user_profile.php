<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Get the username and return URL
$return_encoded = $core->variable('r', '');
$username_encoded = $core->variable('u', '');

$return_url = urldecode($return_encoded);
$username = urldecode($username_encoded);

// We need username for this module
$user->restrict(!empty($username));

// Build an array of the data that we need
$required_data = array($config->ldap_fullname, $config->ldap_mail);

// Get the user data
$username_data = $user->get_details($username, $required_data);

// Output the avatar
if (isset($username_data[$config->ldap_mail]))
{
    $full_name  = @$username_data[$config->ldap_fullname];
    $email      = @$username_data[$config->ldap_mail];
    $avatar_url = "?q=user_avatar&u={$username_encoded}";
    
    $skin->assign(array(
        'user_username'         => htmlspecialchars($username),
        'user_fullname'         => htmlspecialchars($full_name),
        'user_email'            => htmlspecialchars($email),
        'avatar_url'            => $avatar_url,
        'return_url'            => $return_url,
        'profile_visibility'    => $skin->visibility(true),
        'notice_visibility'     => $skin->visibility(false),
        'return_visibility'     => $skin->visibility(!empty($return_url)),
    ));    
}
else
{
    $skin->assign(array(
        'profile_visibility'    => $skin->visibility(false),
        'notice_visibility'     => $skin->visibility(true),
    ));
}

// Output the page
$module_title = $lang->get('user_profile');
$module_data = $skin->output('tpl_user_profile');
    
?>