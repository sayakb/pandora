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
           "WHERE username = '{$auth->username}' " .
           "AND program_id = {$id}";
    $role_data = $db->query($sql, true);

    // Check if we have a role
    if ($role_data != null)
    {
        $role = $role_data['role'] == 's' ? $lang->get('role_student') : $lang->get('role_mentor');
        $program_role = preg_replace('/\_\_role\_\_/', $role, $lang->get('program_role'));
    }

    // Assign screen data for the program
    $skin->assign(array(
        'program_id'                    => $program_data['id'],
        'program_title'                 => $program_data['title'],
        'program_description'           => $program_data['description'],
        'program_role'                  => isset($program_role) ? $program_role : '',
        'prog_guest_visibility'         => $skin->visibility($role_data == null),
        'prog_participant_visibility'   => $skin->visibility($role_data != null),
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