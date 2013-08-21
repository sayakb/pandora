<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

$action = $core->variable('a', '');
$username = $core->variable('u', '');

// Something was modified
if (!empty($action) && !empty($username))
{
    // Process the username
    $username = urldecode($username);
    $db->escape($username);

    if ($action == 'approve')
    {
        $new_role = 'm';
    }
    else if ($action == 'reject')
    {
        $new_role = 'x';
    }

    if (isset($new_role))
    {
        $sql = "UPDATE {$db->prefix}roles " .
               "SET role = '{$new_role}' " .
               "WHERE username = '{$username}'";
        $db->query($sql);
    }

    // Purge the roles cache
    $cache->purge('roles');

    // Redirect to refresh
    $core->redirect("?q=approve_mentors");
}

// Get the proposed mentors list
$sql = "SELECT * FROM {$db->prefix}roles " .
       "WHERE role = 'i'";
$list_data = $db->query($sql);

// Populate the mentor list
$mentors_list = '';

foreach ($list_data as $row)
{
    $mentor_url = urlencode($row['username']);

    // Assign data for each mentor
    $skin->assign(array(
        'mentor_name'           => $user->profile(htmlspecialchars($row['username']), true),
        'approve_url'           => "?q=approve_mentors&amp;a=approve&amp;u={$mentor_url}",
        'reject_url'            => "?q=approve_mentors&amp;a=reject&amp;u={$mentor_url}",
    ));

    $mentors_list .= $skin->output('tpl_approve_mentors_item');
}

// Assign final skin data
$skin->assign(array(
    'mentors_list'          => $mentors_list,
    'notice_visibility'     => $skin->visibility(count($list_data) == 0),
    'list_visibility'       => $skin->visibility(count($list_data) > 0),
));

// Output the module
$module_title = $lang->get('approve_mentors');
$module_data = $skin->output('tpl_approve_mentors');

?>
