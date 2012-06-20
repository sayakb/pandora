<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// Collect some data
$action = $core->variable('a', 'view');
$program_id = $core->variable('prg', 0);
$project_id = $core->variable('p', 0);
$title = $core->variable('title', '');
$description = $core->variable('description', '');

$project_save = isset($_POST['project_save']);
$confirm = isset($_POST['yes']);

// Escape captured data
$db->escape($program_id);
$db->escape($project_id);

// Validate project and program ID
$db->validate('programs', 'id', $program_id);
$db->validate('projects', 'id', $project_id);

// We are viewing/editing a project
if ($project_id > 0)
{  
    $sql = "SELECT prj.is_approved, prt.role " .
           "FROM {$db->prefix}projects prj " .
           "LEFT JOIN {$db->prefix}participants prt " .
           "ON (prj.id = prt.project_id) " .
           "WHERE prj.id = {$project_id} " .
           "AND prt.username = '{$auth->username}'";
    $owner_data = $db->query($sql, true);

    if ($owner_data != null)
    {
        // Set the role for the user
        $role = $owner_data['role'];
        
        // Role student means the user submitted the proposal
        // Hence set as owner
        if ($role == 's')
        {
            $is_owner = true;
        }

        // Role mentor should be approved for that project
        // to be considered as a owner
        else if ($role == 'm' && $owner_data['is_approved'] == 1)
        {
            $is_owner = true;
        }
    }
    else
    {
        $is_owner = false;
        $role = 'g';
    }
}

// We are viewing a list
else
{
    // Get the role of the user
    $sql = "SELECT * FROM {$db->prefix}participants " .
           "WHERE program_id = {$program_id} " .
           "AND username = '{$auth->username}'";
    $role_data = $db->query($sql, true);

    // Role is guest if no entry was found
    $role = $role_data != null ? $role_data['role'] : 'g';
}


// Serve the page based on the action
if ($action == 'editor')
{
    $page_title = $project_id == 0 ? $lang->get('submit_proposal') : $lang->get('edit_project');

    // Program ID is mandatory for editor
    $auth->restrict($program_id > 0);
    $skin->assign('program_id', $program_id);
    
    // Only students/non-participants can create new proposals
    if ($project_id == 0)
    {
        $auth->restrict($role == 'g' || $role == 's', true);
    }

    // Only owners can edit a project
    else
    {
        $auth->restrict($is_owner, true);
    }

    // Project was saved
    if ($project_save)
    {
        // All fields are mandatory
        if (empty($title) || empty($description))
        {
            $error_message = $lang->get('mandatory_all');
        }
        else
        {
            $db->escape($title);
            $db->escape($description);

            // Are we updating?
            if ($project_id > 0)
            {
                $sql = "UPDATE {$db->prefix}projects " .
                       "SET title = '{$title}', " .
                       "    description = '{$description}' " .
                       "WHERE id = {$project_id}";
                $db->query($sql);

                if ($db->affected_rows() >= 0)
                {
                    $success_message = $lang->get('project_updated');
                }
                else
                {
                    $error_message = $lang->get('error_occurred');
                }
            }

            else
            {
                // Insert new project
                $sql = "INSERT INTO {$db->prefix}projects " .
                       "(title, description, program_id, is_approved, is_complete) " .
                       "VALUES ('{$title}', '{$description}', {$program_id}, 0, 0)";
                $db->query($sql);

                // Get the new project ID
                $new_id = $db->get_id();

                // Insert student data
                $sql = "INSERT INTO {$db->prefix}participants " .
                       "(username, project_id, program_id, role, passed) " .
                       "VALUES ('{$auth->username}', {$new_id}, {$program_id}, 's', -1)";
                $db->query($sql);

                if ($new_id > 0)
                {
                    $success_message = $lang->get('proposal_submitted');
                    $title = '';
                    $description = '';
                }
                else
                {
                    $error_message = $lang->get('error_occurred');
                }
            }
        }
    }

    // Populate project data 
    else if ($project_id > 0)
    {
        $sql = "SELECT * FROM {$db->prefix}projects " .
               "WHERE id = {$project_id}";
        $project_data = $db->query($sql, true);

        $title = $project_data['title'];
        $description = $project_data['description'];
    }

    // Assign skin data
    $skin->assign(array(
        'editor_title'          => $page_title,
        'title'                 => $title,
        'description'           => $description,
        'success_message'       => isset($success_message) ? $success_message : '',
        'error_message'         => isset($error_message) ? $error_message : '',
        'success_visibility'    => $skin->visibility(!empty($success_message)),
        'error_visibility'      => $skin->visibility(!empty($error_message)),
        'delete_visibility'     => $skin->visibility($project_id > 0),
        'delete_url'            => "?q=view_projects&a=delete&prg={$program_id}&p={$project_id}",
    ));

    // Output the module
    $module_title = $lang->get('submit_proposal');
    $module_data = $skin->output('tpl_view_projects_editor');
}
else if ($action == 'delete')
{
    // Program ID should be supplied, and user must be project owner
    $auth->restrict($program_id > 0);
    $auth->restrict($is_owner, true);
    
    // Deletion was confirmed
    if ($confirm)
    {
        $db->escape($id);

        $sql = "DELETE FROM {$db->prefix}participants " .
               "WHERE project_id = {$project_id}";
        $db->query($sql);

        $sql = "DELETE FROM {$db->prefix}projects " .
               "WHERE id = {$project_id}";
        $db->query($sql);

        // Redirect to list page
        $core->redirect("?q=program_home&prg={$program_id}");
    }

    // Assign confirm box data
    $skin->assign(array(
        'message_title'     => $lang->get('confirm_deletion'),
        'message_body'      => $lang->get('confirm_del_msg'),
        'cancel_url'        => "?q=view_projects&a=editor&prg={$program_id}&p={$project_id}",
    ));

    // Output the module
    $module_title = $lang->get('confirm_deletion');
    $module_data = $skin->output('tpl_confirm_box');
}

?>