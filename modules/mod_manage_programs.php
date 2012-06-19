<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Collect some data
$action = $core->variable('a', 'list');
$id = $core->variable('p', 0);
$title = $core->variable('title', '');
$description = $core->variable('description', '');
$start_date = $core->variable('start_date', '');
$end_date = $core->variable('end_date', '');
$active = $core->variable('active', '');

$program_save = isset($_POST['program_save']);
$confirm = isset($_POST['yes']);
$cancel = isset($_POST['no']);

// Serve the page based on the action
if ($action == 'list')
{
    $programs_list = '';
    
    // Get all programs
    $sql = "SELECT * FROM {$db->prefix}programs";
    $result = $db->query($sql);

    // Build the list
    foreach ($result as $row)
    {
        // Assign data for this program
        $skin->assign(array(
            'program_id'          => $row['id'],
            'program_title'       => $row['title'],
            'program_active'      => $row['is_active'] == 1 ? 'visible' : 'hidden',
            'program_inactive'    => $row['is_active'] == 0 ? 'visible' : 'hidden',
            'program_description' => $row['description'],
        ));

        $programs_list .= $skin->output('tpl_manage_programs_item');
    }

    // Assign final skin data
    $skin->assign(array(
        'programs_list'     => $programs_list,
        'notice_visibility' => count($result) > 0 ? 'hidden' : 'visible',
        'list_visibility'   => count($result) > 0 ? 'visible' : 'hidden',
    ));

    // Output the module
    $module_title = $lang->get('manage_programs');
    $module_data = $skin->output('tpl_manage_programs');
}
else if ($action == 'editor')
{
    $page_title = $id == 0 ? $lang->get('add_program') : $lang->get('edit_program');
    $start_time = strtotime($start_date);
    $end_time = strtotime($end_date);

    if ($program_save)
    {
        if (empty($title) || empty($start_date) || empty($end_date))
        {
            $error_message = $lang->get('err_mandatory_fields');
        }
        else if ($start_time === false || $end_time === false)
        {
            $error_message = $lang->get('invalid_date');
        }
        else
        {
            $db->escape($id);
            $db->escape($title);
            $db->escape($description);

            $is_active = $active == 'on' ? 1 : 0;

            // Are we updating?
            if ($id > 0)
            {
                $sql = "UPDATE {$db->prefix}programs " .
                       "SET title = '{$title}', " .
                       "    description = '{$description}', " .
                       "    start_time = {$start_time}, " .
                       "    end_time = {$end_time}, " .
                       "    is_active = {$is_active} " .
                       "WHERE id = $id";
                $db->query($sql);
            }
            else
            {
                $sql = "INSERT INTO {$db->prefix}programs " .
                       "(title, description, start_time, end_time, is_active) " .
                       "VALUES ('{$title}', '{$description}', {$start_time}, " .
                       "        {$end_time}, {$is_active})";
                $db->query($sql);
            }

            // Redirect to list page
            $core->redirect("?q=manage_programs");
        }
    }

    // Load data when in edit mode
    else if ($id > 0)
    {
        $db->escape($id);

        $sql = "SELECT * FROM {$db->prefix}programs " .
               "WHERE id = {$id}";
        $row = $db->query($sql, true);

        // Set loaded data
        $title = $row['title'];
        $description = $row['description'];
        $start_date = date('M d, Y', $row['start_time']);
        $end_date = date('M d, Y', $row['end_time']);
        $active = $row['is_active'] == 1 ? 'on' : '';
    }
    
    // Assign skin data
    $skin->assign(array(
        'editor_title'      => $page_title,
        'title'             => $title,
        'description'       => $description,
        'start_date'        => $start_date,
        'end_date'          => $end_date,
        'active_checked'    => $active == 'on' ? 'checked' : '',
        'error_message'     => isset($error_message) ? $error_message : '',
        'error_visibility'  => isset($error_message) ? 'visible' : 'hidden',
        'delete_url'        => "?q=manage_programs&a=delete&p={$id}",
        'delete_visibility' => $id > 0 ? 'visible' : 'hidden',
    ));

    // Output the module
    $module_title = $page_title;
    $module_data = $skin->output('tpl_manage_programs_editor');
}
else if ($action == 'delete')
{
    // Deletion was confirmed
    if ($confirm)
    {
        $db->escape($id);

        $sql = "DELETE FROM {$db->prefix}participants " .
               "WHERE program_id = {$id}";
        $db->query($sql);

        $sql = "DELETE FROM {$db->prefix}projects " .
               "WHERE program_id = {$id}";
        $db->query($sql);

        $sql = "DELETE FROM {$db->prefix}programs " .
               "WHERE id = {$id}";
        $db->query($sql);
        
        // Redirect to list page
        $core->redirect("?q=manage_programs");
    }
    
    // Assign confirm box data
    $skin->assign(array(
        'message_title'     => $lang->get('confirm_deletion'),
        'message_body'      => $lang->get('confirm_del_msg'),
        'cancel_url'        => "?q=manage_programs&a=editor&p={$id}",
    ));

    // Output the module
    $module_title = $lang->get('confirm_deletion');
    $module_data = $skin->output('tpl_confirm_box');

}

?>