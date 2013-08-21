<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

$program_id = $core->variable('prg', '');

// Get the program's participant list
$db->escape($program_id);

$sql = "SELECT r.username, r.role AS role, " .
       "pr.id AS project_id, pr.title AS project_title " .
       "FROM {$db->prefix}roles r " .
       "LEFT JOIN {$db->prefix}participants p " .
       "ON r.program_id = p.program_id " .
       "AND r.username = p.username " .
       "LEFT JOIN {$db->prefix}projects pr " .
       "ON p.project_id = pr.id ".
       "WHERE r.program_id = {$program_id} " .
       "ORDER BY r.role, r.username";
$list_data = $db->query($sql);

// Parse the participant list
$list = array();
$prev_row = null;
$project = null;

foreach ($list_data as $row)
{
    // Append project to previous row
    if ($prev_row != null)
    {
        if ($prev_row['username'] == $row['username'] && $prev_row['role'] == $row['role'] &&
            $row['project_id'] != null)
        {
            $idx = count($list) - 1;
            $list[$idx]['projects'] .= '<br /><a href="?q=view_projects&prg=' . $program_id .
                                       '&p=' . $row['project_id'] . '">' . htmlspecialchars($row['project_title']) .
                                       '</a>';
            continue;
        }
    }

    // Link to project only if it exists
    if ($row['project_id'] != null)
    {
        $project = '<a href="?q=view_projects&prg=' . $program_id . '&p=' . $row['project_id'] . '">' .
                   htmlspecialchars($row['project_title']) . '</a>';
    }
    else
    {
        $project = '-';
    }

    $list[] = array(
        'username'  => $row['username'],
        'profile'   => $user->profile(htmlspecialchars($row['username']), true),
        'role'      => $lang->get('role_' . $row['role']),
        'projects'  => $project,
    );

    $prev_row = $row;
}

// Populate the parsed list
$participant_list = '';

foreach ($list as $item)
{
    // Assign data for each mentor
    $skin->assign(array(
        'participant'    => $item['profile'],
        'role'           => $item['role'],
        'projects'       => $item['projects'],
    ));

    $participant_list .= $skin->output('tpl_view_participants_item');
}

// Assign final skin data
$skin->assign(array(
    'participant_list'      => $participant_list,
    'notice_visibility'     => $skin->visibility(count($list) == 0),
    'list_visibility'       => $skin->visibility(count($list) > 0),
));

// Output the module
$module_title = $lang->get('prog_participants');
$module_data = $skin->output('tpl_view_participants');

?>
