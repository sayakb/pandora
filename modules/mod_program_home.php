<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Collect some data
$id = $core->variable('prg', 0);

// Get program data
$db->escape($id);

$sql = "SELECT * FROM {$db->prefix}programs " .
       "WHERE id = {$id}";
$program_data = $db->query($sql, true);

// Was the program found?
if ($program_data != null)
{
    // Check the role of the current user
    $sql = "SELECT role FROM {$db->prefix}participants " .
           "WHERE username = '{$user->username}' " .
           "AND program_id = {$id}";
    $role_data = $db->query($sql, true);

    // Check if we have a role
    if ($role_data != null)
    {
        $role = $role_data['role'];
    }
    else
    {
        $role = 'g';
    }

    // Assign screen data for the program
    $skin->assign(array(
        'program_id'               => $program_data['id'],
        'program_title'            => $program_data['title'],
        'program_description'      => $program_data['description'],
        'program_start_date'       => date('M d, Y', $program_data['start_time']),
        'program_end_date'         => date('M d, Y', $program_data['end_time']),
        'prg_guest_visibility'     => $skin->visibility($role == 'g'),
        'prg_student_visibility'   => $skin->visibility($role == 's'),
        'prg_mentor_visibility'    => $skin->visibility($role == 'm'),
    ));

    // Output the module
    $module_title = $program_data['title'];
    $module_data = $skin->output('tpl_program_home');
}
else
{
    $core->redirect("?q=view_programs");
}

?>