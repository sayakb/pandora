<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Read the cron table
$sql = "SELECT timestamp, locked FROM {$db->prefix}cron LIMIT 1";
$row = $db->query($sql, true);
$timestamp = $row['timestamp'];
$locked = $row['locked'];

// Check the time difference
if (((time() - $timestamp) > 60) && !$locked)
{
    // Make sure the cron is run only once
    $db->query("UPDATE {$db->prefix}cron SET locked = 1 WHERE locked = 0");

    if ($db->affected_rows() > 0)
    {
        // Perform cron tasks
        $db->query("DELETE FROM {$db->prefix}session WHERE timestamp < {$user->max_age}");
        $db->query("UPDATE {$db->prefix}cron SET timestamp = " . time() . ", locked = 0");
    }
}

?>