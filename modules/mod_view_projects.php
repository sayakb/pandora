<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

// Collect some data
$action = $core->variable('a', 'view');
$category = $core->variable('c', '');
$program_id = $core->variable('prg', 0);
$project_id = $core->variable('p', 0);
$title = $core->variable('title', '', false, true);
$description = $core->variable('description', '', false, true);
$new_student = $core->variable('new_student', '', false, true);
$new_mentor = $core->variable('new_mentor', '', false, true);
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
           "ON prg.id = prj.program_id " .
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

// Get the role of the user
$sql = "SELECT * FROM {$db->prefix}roles " .
       "WHERE program_id = {$program_id} " .
       "AND username = '{$user->username}'";
$crc = crc32($sql);
$role_data = $cache->get($crc, 'roles');

if (!$role_data)
{
    $role_data = $db->query($sql, true);
    $cache->put($crc, $role_data, 'roles');
}

// Role is guest if no entry was found
$role = $role_data != null ? $role_data['role'] : 'g';

// Check if the user is the owner of the project
if ($project_id > 0)
{
    $sql = "SELECT COUNT(*) AS count " .
           "FROM {$db->prefix}projects prj " .
           "LEFT JOIN {$db->prefix}participants prt " .
           "ON prj.id = prt.project_id " .
           "WHERE prj.id = {$project_id} " .
           "AND prt.username = '{$user->username}' " .
           "AND (prt.role = 's' " .
           "OR (prt.role = 'm' " .
           "AND prj.is_accepted = 1))";
    $owner_count = $db->query($sql, true);

    $is_owner = $owner_count['count'] > 0;
}
else
{
    $is_owner = false;
}

// Serve the page based on the action
if ($action == 'editor')
{
    $page_title = $project_id == 0 ? $lang->get('submit_proposal') : $lang->get('edit_project');

    // Program ID is mandatory for editor
    $user->restrict($program_id > 0);

    // Validate pass status of student (should be 1, 0 or -1)
    $user->restrict(in_array($is_passed, array(1, 0, -1)));

    // Only students can create new proposals
    if ($project_id == 0)
    {
        $user->restrict($role == 's', true);
    }

    // Only owners can edit a project
    else
    {
        $user->restrict($is_owner, true);
    }

    // Past student deadline, don't let them submit or edit
    if ($role == 's')
    {
        $sql = "SELECT dl_student FROM {$db->prefix}programs " .
               "WHERE id = {$program_id}";
        $program_data = $db->query($sql, true);

        $user->restrict($core->timestamp < $program_data['dl_student'], true);
    }

    // Fetch project data
    if ($project_id > 0)
    {
        $sql = "SELECT * FROM {$db->prefix}projects prj " .
               "LEFT JOIN {$db->prefix}participants prt " .
               "ON prj.id = prt.project_id " .
               "WHERE prj.id = {$project_id} " .
               "AND prt.role = 's'";
        $project_data = $db->query($sql, true);

        // Do not let anyone but admins edit rejected projects`
        $user->restrict($project_data['is_accepted'] != 0, true);

        // Load data from DB only if new data wasn't POSTed
        if (!$project_save)
        {
            $title = $project_data['title'];
            $description = $project_data['description'];
            $is_passed = $project_data['passed'];
            $is_complete = $project_data['is_complete'];
        }
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

                // Update student name
                if ($user->is_admin && !empty($new_student))
                {
                    $db->escape($new_student);

                    // Get existing role of the new student
                    $sql = "SELECT role FROM {$db->prefix}roles " .
                           "WHERE username = '{$new_student}' " .
                           "AND program_id = {$program_id}";
                    $role_data = $db->query($sql, true);

                    // New student has an already defined role
                    if ($role_data != null)
                    {
                        if ($role_data['role'] != 'm')
                        {
                            // Update role to mentor
                            $sql = "UPDATE {$db->prefix}roles " .
                                   "SET role = 's' " .
                                   "WHERE username = '{$new_student}' " .
                                   "AND program_id = {$program_id} ";
                            $db->query($sql);
                        }
                        else
                        {
                            $error_message = $lang->get('new_student_mentor');
                        }
                    }
                    else
                    {
                        $sql = "INSERT INTO {$db->prefix}roles " .
                               "(username, program_id, role) " .
                               "VALUES ('{$new_student}', {$program_id}, 's')";
                        $db->query($sql);
                    }

                    if (empty($error_message))
                    {
                       // Update the project student
                        $sql = "UPDATE {$db->prefix}participants " .
                               "SET username = '{$new_student}', " .
                               "    role = 's' " .
                               "WHERE program_id = {$program_id} " .
                               "AND project_id = {$project_id}";
                        $db->query($sql);
                    }
                }

                // Update mentor name
                if ($user->is_admin && !empty($new_mentor))
                {
                    $db->escape($new_mentor);

                    // Get existing role of the new mentor
                    $sql = "SELECT role FROM {$db->prefix}roles " .
                           "WHERE username = '{$new_mentor}' " .
                           "AND program_id = {$program_id}";
                    $role_data = $db->query($sql, true);

                    // New mentor has an already defined role
                    if ($role_data != null)
                    {
                        if ($role_data['role'] != 's')
                        {
                            // Update role to mentor
                            $sql = "UPDATE {$db->prefix}roles " .
                                   "SET role = 'm' " .
                                   "WHERE username = '{$new_mentor}' " .
                                   "AND program_id = {$program_id} ";
                            $db->query($sql);
                        }
                        else
                        {
                            $error_message = $lang->get('new_mentor_student');
                        }
                    }
                    else
                    {
                        $sql = "INSERT INTO {$db->prefix}roles " .
                               "(username, program_id, role) " .
                               "VALUES ('{$new_mentor}', {$program_id}, 'm')";
                        $db->query($sql);
                    }

                    if (empty($error_message))
                    {
                        // Delete existing mentors of this project
                        $sql = "DELETE FROM {$db->prefix}participants " .
                               "WHERE project_id = {$project_id} " .
                               "AND role = 'm'";
                        $db->query($sql);

                        // Insert the new mentor
                        $sql = "INSERT INTO {$db->prefix}participants " .
                               "(username, project_id, program_id, role) " .
                               "VALUES ('{$new_mentor}', {$project_id}, {$program_id}, 'm')";
                        $db->query($sql);
                    }
                }

                if (empty($error_message))
                {
                    // Purge the project and roles cache
                    $cache->purge(array('projects', 'roles'));

                    // We take the user back to the view project page
                    $core->redirect("?q=view_projects&prg={$program_id}&p={$project_id}");
                }
            }

            else
            {
                // Auto accept project when being created by an admin
                $is_accepted = $user->is_admin ? 1 : -1;

                // Insert new project
                $sql = "INSERT INTO {$db->prefix}projects " .
                       "(title, description, program_id, is_accepted, is_complete) " .
                       "VALUES ('{$title}', '{$description}', {$program_id}, {$is_accepted}, 0)";
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
                $show_subscribe = true;
            }

            // Purge the project cache
            $cache->purge('projects');
        }
    }

    // Determine the cancel URL
    $cancel_url = !empty($return_url) ? "?q=view_projects&amp;prg={$program_id}&amp;p={$project_id}"
                                      : "?q=program_home&amp;prg={$program_id}";

    // Assign skin data
    $skin->assign(array(
        'editor_title'          => $page_title,
        'program_id'            => $program_id,
        'project_title'         => htmlspecialchars($title),
        'project_description'   => htmlspecialchars($description),
        'new_mentor'            => htmlspecialchars($new_mentor),
        'success_message'       => isset($success_message) ? $success_message : '',
        'error_message'         => isset($error_message) ? $error_message : '',
        'success_visibility'    => $skin->visibility(empty($success_message), true),
        'error_visibility'      => $skin->visibility(empty($error_message), true),
        'decision_visibility'   => $skin->visibility($project_id > 0 && $can_decide),
        'subscribe_visibility'  => $skin->visibility(isset($show_subscribe)),
        'newuser_visibility'    => $skin->visibility($project_id > 0 && $user->is_admin),
        'complete_checked'      => $skin->checked($is_complete == 1),
        'pass_checked'          => $skin->checked($is_passed == 1),
        'fail_checked'          => $skin->checked($is_passed == 0),
        'undecided_checked'     => $skin->checked($is_passed == -1),
        'cancel_url'            => $cancel_url,
    ));

    // Output the module
    $module_title = $lang->get('submit_proposal');
    $module_data = $skin->output('tpl_view_projects_editor');
}
else if ($action == 'delete')
{
    // Program ID should be supplied, and user must be an admin
    $user->restrict($program_id > 0);
    $user->restrict(false, true);

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

        // Purge the project cache
        $cache->purge('projects');

        // Redirect to list page
        $core->redirect("?q=program_home&amp;prg={$program_id}");
    }

    // Assign confirm box data
    $skin->assign(array(
        'message_title'     => $lang->get('confirm_deletion'),
        'message_body'      => $lang->get('confirm_project_del'),
        'cancel_url'        => "?q=view_projects&amp;prg={$program_id}&amp;p={$project_id}",
    ));

    // Output the module
    $module_title = $lang->get('confirm_deletion');
    $module_data = $skin->output('tpl_confirm_box');
}
else if ($action == 'view')
{
    // Program and Project IDs are mandatory here
    $user->restrict($program_id > 0 && $project_id > 0);

    // Get program, project and participant data
    $program_data     = $cache->get("program_{$program_id}", 'programs');
    $project_data     = $cache->get("project_{$project_id}", 'projects');
    $participant_data = $cache->get("participant_{$project_id}", 'projects');

    if (!$program_data)
    {
        $sql = "SELECT * FROM {$db->prefix}programs " .
               "WHERE id = {$program_id}";
        $program_data = $db->query($sql, true);

        $cache->put("program_{$program_id}", $program_data, 'programs');
    }

    if (!$project_data)
    {
        $sql = "SELECT * FROM {$db->prefix}projects " .
               "WHERE id = {$project_id}";
        $project_data = $db->query($sql, true);

        $cache->put("project_{$project_id}", $project_data, 'projects');
    }

    if (!$participant_data)
    {
        $sql = "SELECT * FROM {$db->prefix}participants " .
               "WHERE project_id = {$project_id}";
        $participant_data = $db->query($sql);

        $cache->put("participant_{$project_id}", $participant_data, 'projects');
    }

    // Now that we have project data, allow only owner or admin to view
    // a rejected project
    $user->restrict($project_data['is_accepted'] != 0 ||
                   ($project_data['is_accepted'] == 0 && $is_owner), true);

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

    // Convert complete indicator to yes/no
    $complete = $project_data['is_complete'] == 1 ? $lang->get('yes') : $lang->get('no');

    // Convert accepted indicator to yes/no/undecided
    if ($project_data['is_accepted'] == 1)
    {
        $accepted = $lang->get('yes');
    }
    else if ($project_data['is_accepted'] == 0)
    {
        $accepted = $lang->get('no');
    }
    else if ($project_data['is_accepted'] == -1)
    {
        $accepted = $lang->get('undecided');
    }

    // Convert passed indicator to yes/no/undecided
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

    // Don't let students edit post student deadline or if project is rejected
    if ($role == 's' && $is_owner)
    {
        $is_owner = ($core->timestamp < $program_data['dl_student'] && $project_data['is_accepted'] != 0);
    }

    // Only mentors and admins can view proposal status before mentor deadline
    if ($role != 'm' && !$user->is_admin && $core->timestamp < $program_data['dl_mentor'])
    {
        $accepted = $lang->get('undecided');
    }

    // A user can choose to mentor if:
    //  1. He signed up as a mentor for the program, and
    //  2. Project doesn't already have a mentor
    //  3. Project hasn't passed mentor deadline
    //  4. Projest has passed student deadline
    $can_mentor = ($role == 'm' && !$has_mentor &&
                   $core->timestamp > $program_data['dl_student'] &&
                   $core->timestamp < $program_data['dl_mentor']);

    // User applied as mentor
    if ($mentor_apply && $can_mentor)
    {
        $sql = "INSERT INTO {$db->prefix}participants " .
               "(username, project_id, program_id, role) " .
               "VALUES ('{$user->username}', {$project_id}, {$program_id}, 'm')";
        $db->query($sql);

        $success_message = $lang->get('mentor_submitted');
        $can_mentor = false;
        $show_subscribe = true;
        $mentor = $user->username;
        $is_owner = $project_data['is_accepted'] == 1;

        // Purge project data
        $cache->purge('projects');
    }

    // Set the return URL (needed when approving the project)
    $return_url = urlencode($core->request_uri());

    // Determine if admin controls are visible or not
    $can_approve = ($project_data['is_accepted'] == -1 || $project_data['is_accepted'] == 0) && $user->is_admin;
    $can_reject  = ($project_data['is_accepted'] == -1 || $project_data['is_accepted'] == 1) && $user->is_admin;

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
        'success_visibility'        => $skin->visibility(empty($success_message), true),
        'edit_visibility'           => $skin->visibility($is_owner || $user->is_admin),
        'delete_visibility'         => $skin->visibility($user->is_admin),
        'mentorship_visibility'     => $skin->visibility($can_mentor),
        'actions_visibility'        => $skin->visibility($is_owner || $can_mentor || $user->is_admin),
        'subscribe_visibility'      => $skin->visibility(isset($show_subscribe)),
        'approve_visibility'        => $skin->visibility($can_approve),
        'reject_visibility'         => $skin->visibility($can_reject),
        'modadm_visibility'         => $skin->visibility($can_approve || $can_reject),
    ));

    // Output the module
    $module_title = $lang->get('view_project');
    $module_data = $skin->output('tpl_view_project');
}
else if ($action == 'user' || $action == 'proposed' || $action == 'accepted' || $action == 'rejected')
{
    // Only admins can see rejected projects
    $user->restrict($action != 'rejected' || ($action == 'rejected' && $user->is_admin));

    // Program ID is mandatory here
    $user->restrict($program_id > 0);

    // Get program  data
    $program_data = $cache->get("program_{$program_id}", 'programs');

    if (!$program_data)
    {
        $sql = "SELECT * FROM {$db->prefix}programs " .
               "WHERE id = {$program_id}";
        $program_data = $db->query($sql, true);

        $cache->put("program_{$program_id}", $program_data, 'programs');
    }

    // Build the queries
    $data_sql = "SELECT * FROM {$db->prefix}projects ";
    $count_sql = "SELECT COUNT(*) AS count FROM {$db->prefix}projects ";
    $limit = "LIMIT {$limit_start}, {$config->per_page}";

    // Set action specific page title and query
    // Proposals will continue to appear as 'proposed' even if it is approved
    // for non-admin and non-mentor roles until the mentor deadline
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
        $is_accepted = 'is_accepted = -1';

        if ($role != 'm' && !$user->is_admin && $core->timestamp < $program_data['dl_mentor'])
        {
            $is_accepted .= ' OR is_accepted = 1';
        }

        $title = $lang->get('proposed_projects');
        $filter = "WHERE {$is_accepted} " .
                  "AND program_id = {$program_id} ";
    }
    else if ($action == 'accepted')
    {
        $is_accepted = 'is_accepted = 1';

        if ($role != 'm' && !$user->is_admin && $core->timestamp < $program_data['dl_mentor'])
        {
            // Just use a random value for is_accepted (2)
            // we don't want to fetch anything here
            $is_accepted = 'is_accepted = 2';
        }

        $title = $lang->get('accepted_projects');
        $filter = "WHERE {$is_accepted} " .
                  "AND program_id = {$program_id} ";
    }
    else if ($action == 'rejected')
    {
        $title = $lang->get('rejected_projects');
        $filter = "WHERE is_accepted = 0 " .
                  "AND program_id = {$program_id} ";
    }

    // Apply filters
    $data_sql  .= $filter;
    $data_sql  .= $limit;
    $count_sql .= $filter;

    // Generate the cache keys
    $crc_data  = crc32($data_sql);
    $crc_count = crc32($count_sql);

    // Get list data and count
    $list_data  = $cache->get($crc_data, 'projects');
    $list_count = $cache->get($crc_count, 'projects');

    if (!$list_data)
    {
        $list_data = $db->query($data_sql);
        $cache->put($crc_data, $list_data, 'projects');
    }

    if (!$list_count)
    {
        $list_count = $db->query($count_sql, true);
        $cache->put($crc_count, $list_count, 'projects');
    }

    // Assign approve/reject flag, we need it everywhere!
    $skin->assign('apprej_visibility', $skin->visibility($action == 'proposed' && $user->is_admin));

    // Set the return URL (needed when approving projects)
    $return_url = urlencode($core->request_uri());

    // Generate the project list
    $key = "skin{$crc_data}" . ($user->is_admin ? '1' : '0');
    $projects_list = $cache->get($key, 'projects');

    if (!$projects_list)
    {
        foreach($list_data as $row)
        {
            $project_title = htmlspecialchars($row['title']);
            $project_desc  = htmlspecialchars($row['description']);

            // Trim the title to 60 characters
            if (strlen($project_title) > 60)
            {
                $project_title = trim(substr($project_title, 0, 60)) . '&hellip;';
            }

            // Trim the description to 150 characters
            if (strlen($project_desc) > 150)
            {
                $project_desc = trim(substr($project_desc, 0, 150)) . '&hellip;';
            }

            // Assign data for each project
            $skin->assign(array(
                'project_title'         => $project_title,
                'project_description'   => $project_desc,
                'project_url'           => "?q=view_projects&amp;prg={$program_id}&amp;p={$row['id']}",
                'approve_url'           => "?q=view_projects&amp;a=approve&amp;prg={$program_id}" .
                                           "&amp;p={$row['id']}&amp;r={$return_url}",
                'reject_url'            => "?q=view_projects&amp;a=reject&amp;prg={$program_id}" .
                                           "&amp;p={$row['id']}&amp;r={$return_url}",
            ));

            $projects_list .= $skin->output('tpl_view_projects_item');
        }

        $cache->put($key, $projects_list, 'projects');
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
else if ($action == 'approve' || $action == 'reject')
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

    // Purge the project cache
    $cache->purge('projects');

    // Redirect to return URL
    $core->redirect(urldecode($return_url));
}
else if ($action == 'apply')
{
    // Only guests can apply
    $user->restrict($role == 'g');

    // We need program ID for this action
    $user->restrict($program_id > 0);

    // Validate category
    $user->restrict(in_array($category, array('student', 'mentor')));

    // Get the program data
    $program_data = $cache->get("program_{$program_id}", 'programs');

    if (!$program_data)
    {
        $sql = "SELECT * FROM {$db->prefix}programs " .
               "WHERE id = {$program_id}";
        $program_data = $db->query($sql, true);

        $cache->put("program_{$program_id}", $program_data, 'programs');
    }

    // Set the new role based on action
    $new_role = $category == 'student' ? 's' : 'i';

    // Allow setting new role based on deadlines
    $user->restrict(($new_role == 's' && $core->timestamp < $program_data['dl_student']) ||
                    ($new_role == 'i' && $core->timestamp < $program_data['dl_mentor']));

    // Insert the new role
    $sql = "INSERT INTO {$db->prefix}roles " .
           "(username, program_id, role) " .
           "VALUES ('{$user->username}', {$program_id}, " .
           "'{$new_role}')";
    $db->query($sql);

    // Notify admin with email for new mentor requests
    if ($new_role == 'i')
    {
        $email->assign('mentor_name', $user->username);
        $email->send($config->webmaster, $lang->get('mentor_subject'), 'mentor');
    }

    // Purge the roles cache
    $cache->purge('roles');

    // Redirect to program home
    $core->redirect("?q=program_home&prg={$program_id}");
}
else if ($action == 'resign')
{
    // Only students can resign
    $user->restrict($role == 's');

    // We need program ID for this action
    $user->restrict($program_id > 0);

    if ($confirm)
    {
        // Check if program has already started
        $sql = "SELECT COUNT(*) AS count " .
               "FROM {$db->prefix}programs " .
               "WHERE id = {$program_id} " .
               "AND start_time <= {$core->timestamp}";
        $prog_count = $db->query($sql, true);

        // If program already started, mark student as failed
        if ($prog_count['count'] > 0)
        {
            $sql = "UPDATE {$db->prefix}participants " .
                   "SET passed = 0 " .
                   "WHERE program_id = {$program_id} " .
                   "AND username = '{$user->username}'";
            $db->query($sql);
        }

        // Else, simply delete the proposals
        else
        {
            $sql = "SELECT * FROM {$db->prefix}participants " .
                   "WHERE program_id = {$program_id} " .
                   "AND username = '{$user->username}'";
            $project_data = $db->query($sql);

            // Student has one or more proposals
            if ($project_data != null)
            {
                $projects_ary = array();

                foreach ($project_data as $row)
                {
                    $projects_ary[] = $row['project_id'];
                }

                $projects = implode(',', $projects_ary);

                // Delete all the projects
                $sql = "DELETE FROM {$db->prefix}participants " .
                       "WHERE project_id IN ({$projects})";
                $db->query($sql);

                $sql = "DELETE FROM {$db->prefix}projects " .
                       "WHERE id IN ({$projects})";
                $db->query($sql);
            }
        }

        // Set role as resigned
        $sql = "UPDATE {$db->prefix}roles " .
               "SET role = 'r' " .
               "WHERE program_id = {$program_id} " .
               "AND username = '{$user->username}'";
        $db->query($sql);

        // Purge the projects and roles cache
        $cache->purge(array('projects', 'roles'));

        // Redirect the user to program home
        $core->redirect("?q=program_home&prg={$program_id}");
    }

    // Assign confirm box data
    $skin->assign(array(
        'message_title'     => $lang->get('confirm_resign'),
        'message_body'      => $lang->get('confirm_resign_exp'),
        'cancel_url'        => "?q=program_home&prg={$program_id}",
    ));

    // Output the module
    $module_title = $lang->get('confirm_deletion');
    $module_data = $skin->output('tpl_confirm_box');
}
else
{
    // Unknown action
    $core->redirect($core->path());
}

?>
