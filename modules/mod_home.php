<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Get the IDs of active programs
$sql = "SELECT id FROM {$db->prefix}programs " .
       "WHERE is_active = 1";
$results = $db->query($sql);

// Take to the program home directly if there's only one program
if (count($results) == 1)
{
    $id  = $results[0]['id'];
    $url = "?q=program_home&prg={$id}";

    $core->redirect($url);
}

// Set the module data
$module_title = $lang->get('home');
$module_data = $skin->output('tpl_home');;

?>