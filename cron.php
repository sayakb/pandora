<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

// Use cache for cron
if ($cache->is_available)
{
    // Get last run time
    $last_run = $cache->get('last_run', 'cron');

    if (!$last_run)
    {
        $last_run = 0;
    }
}

// Use DB for cron
else
{
    // Get last run time
    $sql = "SELECT timestamp " .
           "FROM {$db->prefix}cron";
    $row = $db->query($sql, true);

    if ($row != null)
    {
        $last_run = $row['timestamp'];
    }
    else
    {
        $last_run = 0;
    }
}

// Check the time difference
if (($core->timestamp - $last_run) > 60)
{
    // Update new run time
    if ($cache->is_available)
    {
        $cache->put('last_run', $core->timestamp, 'cron');
    }
    else
    {
        $sql = "UPDATE {$db->prefix}cron " .
               "SET timestamp = {$core->timestamp}";
        $db->query($sql);
    }

    // Cron tasks
    $cache->purge('users');
    $db->query("DELETE FROM {$db->prefix}session WHERE timestamp < {$user->max_age}");
}

?>
