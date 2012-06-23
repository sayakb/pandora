<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Collect some data
$ban_user = $core->variable('ban_user', '', false, true);
$unban_user_url = $core->variable('u', '');
$success = $core->variable('s', 0);

$ban_submit = isset($_POST['ban_submit']);
$unban_user = urldecode($unban_user_url);
$is_successful = $success == 1;

// Was the ban form submitted?
if ($ban_submit)
{
    $db->escape($ban_user);

    // Check if user is already banned
    $sql = "SELECT COUNT(*) AS count " .
           "FROM {$db->prefix}bans " .
           "WHERE username = '{$ban_user}'";
    $row = $db->query($sql, true);

    // Count should be 0 to ban again
    if ($row['count'] == 0)
    {
        // Insert entry into user ban table
        $sql = "INSERT INTO {$db->prefix}bans (username) " .
               "VALUES ('{$ban_user}')";
        $db->query($sql);

        // Kill the user's session
        $sql = "DELETE FROM {$db->prefix}session " .
               "WHERE username = '{$ban_user}'";
        $db->query($sql);
    }

    // Redirect to refresh
    $core->redirect("?q=user_ban");
}

// Was the user unbanned
if (!empty($unban_user))
{
    $db->escape($unban_user);

    // Delete the user from the ban table
    $sql = "DELETE FROM {$db->prefix}bans " .
           "WHERE username = '{$unban_user}'";
    $db->query($sql);

    // Redirect to refresh
    $core->redirect("?q=user_ban");
}

// Get currently banned users
$sql = "SELECT * FROM {$db->prefix}bans";
$result = $db->query($sql);

// Populate the ban list
$ban_list = '';

foreach ($result as $row)
{
    $username_url = urlencode($row['username']);
    
    // Assign data for each row
    $skin->assign(array(
        'ban_username'   => $user->profile(htmlspecialchars($row['username'], true)),
        'unban_url'      => "?q=user_ban&amp;u={$username_url}",
    ));

    // Output the row
    $ban_list .= $skin->output('tpl_user_ban_item');
}

// Assign final skin variables
$skin->assign(array(
    'ban_list'          => $ban_list,
    'list_visibility'   => $skin->visibility(count($result) > 0),
    'notice_visibility' => $skin->visibility(count($result) == 0),
));

// Output the page
$module_title = $lang->get('manage_bans');
$module_data = $skin->output('tpl_user_ban');

?>