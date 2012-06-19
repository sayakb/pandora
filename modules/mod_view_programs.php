<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Get a list of active programs
$sql = "SELECT * FROM {$db->prefix}programs " .
       "WHERE start_time <= {$core->timestamp} " .
       "AND end_time >= {$core->timestamp} " .
       "AND is_active = 1";
$result = $db->query($sql);

// Generate a list
$programs_list = '';

foreach ($result as $row)
{
    // Assign data for program
    $skin->assign(array(
        'program_id'          => $row['id'],
        'program_title'       => $row['title'],
        'program_description' => $row['description'],
    ));

    $programs_list .= $skin->output('tpl_view_programs_item');
}

// Assign final skin data
$skin->assign(array(
    'programs_list'     => $programs_list,
    'notice_visibility' => count($result) > 0 ? 'hidden' : 'visible',
    'list_visibility'   => count($result) > 0 ? 'visible' : 'hidden',
));

// Output the module
$module_title = $lang->get('view_programs');
$module_data = $skin->output('tpl_view_programs');

?>