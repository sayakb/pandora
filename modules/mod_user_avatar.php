<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Get the username
$username_encoded = $core->variable('u', '');
$username = urldecode($username_encoded);

// We need username for this module
$user->restrict(!empty($username));

// Get the avatar for the user
$user_data = $user->get_details($username, $config->ldap_avatar);

// Set the page header
header('Content-type: octet-stream');
header('Content-Transfer-Encoding: binary');

// Does the user have an avatar?
if (isset($user_data[$config->ldap_avatar]))
{
    // Avatar was found, output it
    echo $user_data[$config->ldap_avatar];
}
else
{
    // User doesn't have an avatar, output the default avatar
    echo file_get_contents(realpath("skins/{$skin->skin_name}/images/default-avatar.png"));
}

// Stop any more output
exit;
    
?>