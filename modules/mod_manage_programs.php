<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

// Collect some data
$action = $core->variable('a', 'list');
$id = $core->variable('p', 0);
$title = $core->variable('title', '', false, true);
$description = $core->variable('description', '', false, true);
$start_date = $core->variable('start_date', '', false, true);
$end_date = $core->variable('end_date', '', false, true);
$dl_student_date = $core->variable('dl_student', '', false, true);
$dl_mentor_date = $core->variable('dl_mentor', '', false, true);
$active = $core->variable('active', '') == "on" ? 1 : 0;
$page = $core->variable('pg', 1);
$limit_start = ($page - 1) * $config->per_page;

$program_save = isset($_POST['program_save']);
$confirm = isset($_POST['yes']);

// Serve the page based on the action
if ($action == 'list')
{
    $programs_list = '';
    
    // Get all programs
    $sql = "SELECT * FROM {$db->prefix}programs " .
           "LIMIT {$limit_start}, {$config->per_page}";
    $program_data = $db->query($sql);

    // Get program count
    $sql = "SELECT COUNT(*) AS count FROM {$db->prefix}programs";
    $program_count = $db->query($sql, true);

    // Build the list
    foreach ($program_data as $row)
    {
        // Assign data for this program
        $skin->assign(array(
            'program_id'          => $row['id'],
            'program_title'       => htmlspecialchars($row['title']),
            'program_description' => htmlspecialchars($row['description']),
            'program_active'      => $skin->visibility($row['is_active'] == 1),
            'program_inactive'    => $skin->visibility($row['is_active'] == 0),
        ));

        $programs_list .= $skin->output('tpl_manage_programs_item');
    }

    // Get the pagination
    $pagination = $skin->pagination($program_count['count'], $page);

    // Assign final skin data
    $skin->assign(array(
        'programs_list'     => $programs_list,
        'list_pages'        => $pagination,
        'notice_visibility' => $skin->visibility(count($program_data) == 0),
        'list_visibility'   => $skin->visibility(count($program_data) > 0),
        'pages_visibility'  => $skin->visibility($program_count['count'] > $config->per_page),
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
    $dl_student = strtotime($dl_student_date);
    $dl_mentor = strtotime($dl_mentor_date);

    if ($program_save)
    {
        if (empty($title) || empty($start_date) || empty($end_date) ||
            empty($dl_student_date) || empty($dl_mentor_date))
        {
            $error_message = $lang->get('err_mandatory_fields');
        }
        else if ($start_time === false || $end_time === false)
        {
            $error_message = $lang->get('invalid_date');
        }
        else if ($dl_student === false || $dl_mentor === false)
        {
            $error_message = $lang->get('invalid_deadlines');
        }
        else
        {
            $db->escape($id);
            $db->escape($title);
            $db->escape($description);
            
            // Determine deadline and completion flags
            $deadline = $dl_student < $core->timestamp ? 1 : 0;
            $complete = $end_time < $core->timestamp ? 1 : 0;

            if ($id > 0)
            {
                // Update program data
                $sql = "UPDATE {$db->prefix}programs " .
                       "SET title = '{$title}', " .
                       "    description = '{$description}', " .
                       "    start_time = {$start_time}, " .
                       "    end_time = {$end_time}, " .
                       "    dl_student = {$dl_student}, " .
                       "    dl_mentor = {$dl_mentor}, " .
                       "    is_active = {$active} " .
                       "WHERE id = $id";
                $db->query($sql);

                // Update program flags in the queue
                // Usually, one entry for the program is exptected to
                // be there, unless the program is updated post completion,
                // which is highly unlikely.
                $sql = "UPDATE {$db->prefix}queue " .
                       "SET deadline = {$deadline}, " .
                       "    complete = {$complete} " .
                       "WHERE program_id = {$id}";
                $db->query($sql);
            }
            else
            {
                // Insert program data
                $sql = "INSERT INTO {$db->prefix}programs " .
                       "(title, description, start_time, end_time, " .
                       " dl_student, dl_mentor, is_active) " .
                       "VALUES ('{$title}', '{$description}', {$start_time}, " .
                       "         {$end_time}, {$dl_student}, {$dl_mentor}, {$active})";
                $db->query($sql);

                // Get the new program ID
                $program_id = $db->get_id();

                // Insert new entry to the queue
                $sql = "INSERT INTO {$db->prefix}queue " .
                       "(program_id, deadline, complete) " .
                       "VALUES ({$program_id}, {$deadline}, {$complete})";
                $db->query($sql);
            }

            // Redirect to list page
            $core->redirect("?q=manage_programs");
        }
    }

    // Load data when in edit mode
    if ($id > 0)
    {
        $db->escape($id);

        $sql = "SELECT * FROM {$db->prefix}programs " .
               "WHERE id = {$id}";
        $row = $db->query($sql, true);

        // Set loaded data
        $title = $row['title'];
        $description = $row['description'];
        $start_date = date('M d Y, h:i a', $row['start_time']);
        $end_date = date('M d Y, h:i a', $row['end_time']);
        $dl_student = date('M d Y, h:i a', $row['dl_student']);
        $dl_mentor = date('M d Y, h:i a', $row['dl_mentor']);
        $active = $row['is_active'];
    }
    
    // Assign skin data
    $skin->assign(array(
        'editor_title'      => $page_title,
        'title'             => htmlspecialchars($title),
        'description'       => htmlspecialchars($description),
        'start_date'        => $start_date,
        'end_date'          => $end_date,
        'dl_student'        => $dl_student,
        'dl_mentor'         => $dl_mentor,
        'active_checked'    => $skin->checked($active == 1),
        'error_message'     => isset($error_message) ? $error_message : '',
        'error_visibility'  => $skin->visibility(isset($error_message)),
        'delete_visibility' => $skin->visibility($id > 0),
        'delete_url'        => "?q=manage_programs&amp;a=delete&amp;p={$id}",
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
        'message_body'      => $lang->get('confirm_program_del'),
        'cancel_url'        => "?q=manage_programs&amp;a=editor&amp;p={$id}",
    ));

    // Output the module
    $module_title = $lang->get('confirm_deletion');
    $module_data = $skin->output('tpl_confirm_box');
}

?>