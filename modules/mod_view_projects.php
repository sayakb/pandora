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
$title = $core->variable('title', '', false, true);
$description = $core->variable('description', '', false, true);
$return_url = $core->variable('r', '');
$is_passed = $core->variable('passed', 0);
$is_complete = $core->variable('complete', '') == 'on' ? 1 : 0;
$page = $core->variable('pg', 1);
$limit_start = ($page - 1) * $config->per_page;

$mentor_apply = isset($_POST['mentor_apply']);
$project_save = isset($_POST['project_save']);
$confirm = isset($_POST['yes']);

// Escape captured data
$db->escape($program_id);
$db->escape($project_id);

// Validate project and program ID
if ($project_id > 0)
{
    $sql = "SELECT COUNT(*) AS count " .
           "FROM {$db->prefix}projects prj " .
           "LEFT JOIN {$db->prefix}programs prg " .
           "ON (prg.id = prj.program_id) " .           
           "WHERE prj.id = {$project_id} " .
           "AND prg.id = {$program_id} " .
           (!$user->is_admin ? "AND prg.is_active = 1" : "");
}
else
{
    $sql = "SELECT COUNT(*) AS count " .
           "FROM {$db->prefix}programs " .
           "WHERE id = {$program_id} " .
           (!$user->is_admin ? "AND is_active = 1" : "");
}

$row = $db->query($sql, true);
$user->restrict($row['count'] > 0);

// Treat all users as guest by default
$is_owner = false;
$role = 'g';

// We are viewing/editing a project
if ($project_id > 0)
{  
    $sql = "SELECT prj.is_accepted, prt.role " .
           "FROM {$db->prefix}projects prj " .
           "LEFT JOIN {$db->prefix}participants prt " .
           "ON (prj.id = prt.project_id) " .
           "WHERE prj.id = {$project_id} " .
           "AND prt.username = '{$user->username}'";
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
        else if ($role == 'm' && $owner_data['is_accepted'] == 1)
        {
            $is_owner = true;
        }
    }
}

// We are viewing a list
else
{
    // Get the role of the user
    $sql = "SELECT * FROM {$db->prefix}participants " .
           "WHERE program_id = {$program_id} " .
           "AND username = '{$user->username}'";
    $role_data = $db->query($sql, true);

    // Role is guest if no entry was found
    $role = $role_data != null ? $role_data['role'] : 'g';
}


// Serve the page based on the action
if ($action == 'editor')
{
    $page_title = $project_id == 0 ? $lang->get('submit_proposal') : $lang->get('edit_project');

    // Program ID is mandatory for editor
    $user->restrict($program_id > 0);

    // Validate pass status of student (should be 1, 0 or -1)
    $user->restrict(in_array($is_passed, array(1, 0, -1)));
    
    // Only students/non-participants can create new proposals
    if ($project_id == 0)
    {
        $user->restrict($role == 'g' || $role == 's', true);
    }

    // Only owners can edit a project
    else
    {
        $user->restrict($is_owner, true);
    }

    // Only mentor/admins can mark project as complete and pass a student
    $can_decide = ($role == 'm' && $is_owner) || $user->is_admin;
    
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
            $db->escape($is_complete);
            $db->escape($is_passed);

            // Are we updating?
            if ($project_id > 0)
            {                
                // Update project data
                $sql = "UPDATE {$db->prefix}projects " .
                       "SET title = '{$title}', " .
                       "    description = '{$description}', " .
                       "    is_complete = " . ($can_decide ? "{$is_complete} " : "is_complete ") .
                       "WHERE id = {$project_id}";
                $db->query($sql);

                // Update student pass status
                if ($can_decide)
                {
                    $sql = "UPDATE {$db->prefix}participants " .
                           "SET passed = $is_passed " .
                           "WHERE project_id = {$project_id} " .
                           "AND role = 's'";
                    $db->query($sql);
                }

                $success_message = $lang->get('project_updated');
            }

            else
            {
                // Insert new project
                $sql = "INSERT INTO {$db->prefix}projects " .
                       "(title, description, program_id, is_accepted, is_complete) " .
                       "VALUES ('{$title}', '{$description}', {$program_id}, 0, 0)";
                $db->query($sql);

                // Get the new project ID
                $new_id = $db->get_id();

                // Insert student data
                $sql = "INSERT INTO {$db->prefix}participants " .
                       "(username, project_id, program_id, role, passed) " .
                       "VALUES ('{$user->username}', {$new_id}, {$program_id}, 's', -1)";
                $db->query($sql);

                $success_message = $lang->get('proposal_submitted');
                $title = '';
                $description = '';
            }
        }
    }

    // Populate project data 
    else if ($project_id > 0)
    {
        $sql = "SELECT * FROM {$db->prefix}projects prj " .
               "LEFT JOIN {$db->prefix}participants prt " .
               "ON (prj.id = prt.project_id) " .
               "WHERE prj.id = {$project_id} " .
               "AND prt.role = 's'";
        $project_data = $db->query($sql, true);

        $title = $project_data['title'];
        $description = $project_data['description'];
        $is_passed = $project_data['passed'];
        $is_complete = $project_data['is_complete'];
    }

    // Assign skin data
    $skin->assign(array(
        'editor_title'          => $page_title,
        'program_id'            => $program_id,
        'project_title'         => htmlspecialchars($title),
        'project_description'   => htmlspecialchars($description),
        'success_message'       => isset($success_message) ? $success_message : '',
        'error_message'         => isset($error_message) ? $error_message : '',
        'success_visibility'    => $skin->visibility(!empty($success_message)),
        'error_visibility'      => $skin->visibility(!empty($error_message)),
        'delete_visibility'     => $skin->visibility($project_id > 0),
        'decision_visibility'   => $skin->visibility($project_id > 0 && $can_decide),
        'complete_checked'      => $skin->checked($is_complete == 1),
        'pass_checked'          => $skin->checked($is_passed == 1),
        'fail_checked'          => $skin->checked($is_passed == 0),
        'undecided_checked'     => $skin->checked($is_passed == -1),
        'delete_url'            => "?q=view_projects&a=delete&prg={$program_id}&p={$project_id}",
    ));

    // Output the module
    $module_title = $lang->get('submit_proposal');
    $module_data = $skin->output('tpl_view_projects_editor');
}
else if ($action == 'delete')
{
    // Program ID should be supplied, and user must be project owner
    $user->restrict($program_id > 0);
    $user->restrict($is_owner, true);
    
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
        'message_body'      => $lang->get('confirm_project_del'),
        'cancel_url'        => "?q=view_projects&a=editor&prg={$program_id}&p={$project_id}",
    ));

    // Output the module
    $module_title = $lang->get('confirm_deletion');
    $module_data = $skin->output('tpl_confirm_box');
}
else if ($action == 'view')
{
    // Program and Project IDs are mandatory here
    $user->restrict($program_id > 0 && $project_id > 0);

    // Get project data
    $sql = "SELECT * FROM {$db->prefix}projects " .
           "WHERE id = {$project_id}";
    $project_data = $db->query($sql, true);

    // Get participants for the project
    $sql = "SELECT * FROM {$db->prefix}participants " .
           "WHERE project_id = {$project_id}";
    $participant_data = $db->query($sql);

    // Assign participant data
    $mentor = '-';
    $has_mentor = false;
    $passed = -1;

    foreach($participant_data as $participant)
    {
        if ($participant['role'] == 's')
        {
            $passed = $participant['passed'];
            $student = $participant['username'];
        }
        else if ($participant['role'] == 'm')
        {
            $has_mentor = true;
            $mentor = $participant['username'];
        }
    }

    // Convert indicators to string for displaying
    $accepted = $project_data['is_accepted'] == 1 ? $lang->get('yes') : $lang->get('no');
    $complete = $project_data['is_complete'] == 1 ? $lang->get('yes') : $lang->get('no');

    if ($passed == 1)
    {
        $result = $lang->get('passed');
    }
    else if ($passed == 0)
    {
        $result = $lang->get('failed');
    }
    else if ($passed == -1)
    {
        $result = $lang->get('undecided');
    }
    
    // A user can choose to mentor if:
    //  1. He isn't a student in this program
    //  2. Project doesn't already have a mentor
    $can_mentor = $role != 's' && !$has_mentor;

    // User applied as mentor
    if ($mentor_apply && $can_mentor)
    {
        $sql = "INSERT INTO {$db->prefix}participants " .
               "(username, project_id, program_id, role) " .
               "VALUES ('{$user->username}', {$project_id}, {$program_id}, 'm')";
        $db->query($sql);

        $success_message = $lang->get('mentor_submitted');
        $can_mentor = false;
        $mentor = $user->username;
    }

    // Set the return URL (needed when approving the project)
    $return_url = urlencode($core->request_uri());

    // Assign final skin data
    $skin->assign(array(
        'program_id'                => $program_id,
        'project_id'                => $project_id,
        'project_title'             => htmlspecialchars($project_data['title']),
        'project_description'       => htmlspecialchars($project_data['description']),
        'project_student'           => $user->profile(htmlspecialchars($student), true),
        'project_mentor'            => $user->profile(htmlspecialchars($mentor), true),
        'project_accepted'          => $accepted,
        'project_complete'          => $complete,
        'project_result'            => $result,
        'return_url'                => $return_url,
        'success_message'           => isset($success_message) ? $success_message : '',
        'success_visibility'        => $skin->visibility(!empty($success_message)),
        'edit_visibility'           => $skin->visibility($is_owner || $user->is_admin),
        'mentorship_visibility'     => $skin->visibility($can_mentor),
        'actions_visibility'        => $skin->visibility($is_owner || $can_mentor),
        'approve_visibility'        => $skin->visibility($project_data['is_accepted'] == 0 && $user->is_admin),
        'disapprove_visibility'     => $skin->visibility($project_data['is_accepted'] == 1 && $user->is_admin),
    ));

    // Output the module
    $module_title = $lang->get('view_project');
    $module_data = $skin->output('tpl_view_project');
}
else if ($action == 'user' || $action == 'proposed' || $action == 'accepted')
{
    $data_sql = "SELECT * FROM {$db->prefix}projects ";
    $count_sql = "SELECT COUNT(*) AS count FROM {$db->prefix}projects ";
    $limit = "LIMIT {$limit_start}, {$config->per_page}";
    
    // Set action specific page title and query
    if ($action == 'user')
    {
        $title = $lang->get('your_projects');
        $filter = "WHERE id IN (SELECT project_id " .
                  "FROM {$db->prefix}participants " .
                  "WHERE username = '{$user->username}' " .
                  "AND program_id = {$program_id}) ";
    }
    else if ($action == 'proposed')
    {
        $title = $lang->get('proposed_projects');
        $filter = "WHERE is_accepted = 0 " .
                  "AND program_id = {$program_id} ";
    }
    else if ($action == 'accepted')
    {
        $title = $lang->get('accepted_projects');
        $filter = "WHERE is_accepted = 1 " .
                  "AND program_id = {$program_id} ";
    }

    // Get list data and count
    $list_data = $db->query($data_sql . $filter . $limit);
    $list_count = $db->query($count_sql . $filter, true);

    // Assign approve flag, we need it everywhere!
    $skin->assign('approve_visibility', $skin->visibility($action == 'proposed' && $user->is_admin));

    // Set the return URL (needed when approving projects)
    $return_url = urlencode($core->request_uri());

    // Populate the project list
    $projects_list = '';
    
    foreach($list_data as $row)
    {
        // Assign data for each project
        $skin->assign(array(
            'project_title'         => htmlspecialchars($row['title']),
            'project_description'   => htmlspecialchars($row['description']),
            'project_url'           => "?q=view_projects&prg={$program_id}&p={$row['id']}",
            'approve_url'           => "?q=view_projects&a=approve&prg={$program_id}&p={$row['id']}&r={$return_url}",
        ));

        $projects_list .= $skin->output('tpl_view_projects_item');
    }

    // Get the pagination
    $pagination = $skin->pagination($list_count['count'], $page);

    // Assign final skin data
    $skin->assign(array(
        'program_id'            => $program_id,
        'view_title'            => $title,
        'projects_list'         => $projects_list,
        'list_pages'            => $pagination,
        'notice_visibility'     => $skin->visibility(count($list_data) == 0),
        'list_visibility'       => $skin->visibility(count($list_data) > 0),
        'pages_visibility'      => $skin->visibility($list_count['count'] > $config->per_page),
    ));

    // Output the module
    $module_title = $title;
    $module_data = $skin->output('tpl_view_projects');
}
else if ($action == 'approve' || $action == 'disapprove')
{
    // This is an admin only action
    $user->restrict(false, true);

    // Program ID, Project ID and return URL are mandatory
    $user->restrict($program_id > 0 && $project_id > 0 && !empty($return_url));

    // Set the accepted flag when approving
    $flag = $action == 'approve' ? 1 : 0;        

    // Set the project as approved
    $sql = "UPDATE {$db->prefix}projects " .
           "SET is_accepted = {$flag} " .
           "WHERE id = {$project_id}";
    $db->query($sql);

    if ($action == 'approve')
    {
        $name = $config->ldap_fullname;
        $mail = $config->ldap_mail;
        $base = $core->base_uri();
        $mentor_name = $lang->get('no_mentor');

        // Get program and project data
        $sql = "SELECT prg.title as program, " .
               "       prj.title as project " .
               "FROM {$db->prefix}projects prj " .
               "LEFT JOIN {$db->prefix}programs prg " .
               "ON (prg.id = prj.program_id) " .
               "WHERE prg.id = {$program_id} " .
               "AND prj.id = {$project_id}";
        $env_data = $db->query($sql, true);

        // Get participant data
        $sql = "SELECT * FROM {$db->prefix}participants " .
               "WHERE project_id = {$project_id}";
        $participant_data = $db->query($sql);

        // Set the mentor and student names
        foreach ($participant_data as $participant)
        {
            $data = $user->get_details($participant['username'], array($name, $mail));
            $fullname = $data[$name];
            $mail = $data[$mail];

            if ($participant['role'] == 's')
            {
                $student      = $participant['username'];
                $student_to   = $fullname;
                $student_name = "{$fullname} &lt;{$mail}&gt;";
                $student_mail = $mail;
            }
            else if ($participant['role'] == 'm')
            {
                $mentor      = $participant['username'];
                $mentor_to   = $fullname;
                $mentor_name = "{$fullname} &lt;{$mail}&gt;";
                $mentor_mail = $mail;
            }
        }

        // Assign data needed for the email
        $email->assign(array(
            'program_name'      => $env_data['program'],
            'project_name'      => $env_data['project'],
            'student_name'      => $student_name,
            'mentor_name'       => $mentor_name,
            'project_url'       => "{$base}?q=view_projects&prg={$program_id}&p={$project_id}",
        ));

        // Send a mail to the student
        $email->assign('recipient', $student_to);
        $status = $email->send($student_mail, $lang->get('mail_subject'));

        // Send a mail to the mentor, if any
        if (isset($mentor_mail))
        {
            $email->assign('recipient', $mentor_to);
            $email->send($mentor_mail, $lang->get('mail_subject'));
        }
    }
    
    // Redirect to return URL
    $core->redirect(urldecode($return_url));
}

?>