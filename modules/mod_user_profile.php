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
$required_data = array(
    $config->ldap_fullname,
    $config->ldap_mail,
    $config->ldap_group,
);

// Get the user data
$username_data = $user->get_details($username, $required_data);

// Set the template data
if (isset($username_data[$config->ldap_mail]))
{
    $is_admin   = false;
    $avatar_url = "?q=user_avatar&amp;u={$username_encoded}";
    $full_name  = $username_data[$config->ldap_fullname][0];
    $email      = $username_data[$config->ldap_mail][0];

    // Determine if the user is a site admin
    foreach ($username_data[$config->ldap_group] as $group)
    {
        if ($group == $config->ldap_admin_group)
        {
            $is_admin = true;
        }
    }

    // Assign profile variables
    $skin->assign(array(
        'user_username'         => htmlspecialchars($username),
        'user_fullname'         => htmlspecialchars($full_name),
        'user_email'            => htmlspecialchars($email),
        'avatar_url'            => $avatar_url,
        'return_url'            => $return_url,
        'profile_visibility'    => $skin->visibility(true),
        'notice_visibility'     => $skin->visibility(false),
        'badge_visibility'      => $skin->visibility($is_admin),
        'return_visibility'     => $skin->visibility(!empty($return_url)),
    ));    
}
else
{
    // No profile found, show notice
    $skin->assign(array(
        'profile_visibility'    => $skin->visibility(false),
        'notice_visibility'     => $skin->visibility(true),
    ));
}

// Output the page
$module_title = $lang->get('user_profile');
$module_data = $skin->output('tpl_user_profile');
    
?>