<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

if (!defined('IN_PANDORA')) exit;

// Collect some data
$return_url = $core->variable('r', '');
$action = $core->variable('a', 'active');
$page = $core->variable('pg', 1);
$limit_start = ($page - 1) * $config->per_page;

// Validate return URL
$user->restrict(in_array($return_url, array('', 'accepted', 'proposed', 'rejected')));

// Inactive programs can be viewed only by admins
$user->restrict($action == 'active' || ($action == 'inactive' && $user->is_admin));

// Rejected return URL allowed only for admins
$user->restrict($return_url != 'rejected' || ($return_url == 'rejected' && $user->is_admin));

// Get a list of active programs
$data_sql = "SELECT * FROM {$db->prefix}programs ";
$count_sql = "SELECT COUNT(*) AS count FROM {$db->prefix}programs ";
$filter = "WHERE is_active = " . ($action == 'active' ? '1 ' : '0 ');
$limit = "LIMIT {$limit_start}, {$config->per_page}";

// Apply filters
$data_sql  .= $filter;
$data_sql  .= $limit;
$count_sql .= $filter;

// Get the cache keys
$crc_data  = crc32($data_sql);
$crc_count = crc32($count_sql);

// Get program data
$program_data = $cache->get($crc_data, 'programs');
$program_count = $cache->get($crc_count, 'programs');

if (!$program_data)
{
    $program_data = $db->query($data_sql);
    $cache->put($crc_data, $program_data, 'programs');
}

if (!$program_count)
{
    $program_count = $db->query($count_sql, true);
    $cache->put($crc_count, $program_count, 'programs');
}

// If only one program is active, directly take the user to the destination
// Don't do this when we are viewing archived projects
if ($program_count['count'] == 1 && count($program_data) == 1 && $action != 'inactive')
{
    $row = $program_data[0];

    // Generate the program URL
    $url  = !empty($return_url) ? "?q=view_projects&prg={$row['id']}&a={$return_url}"
                                : "?q=program_home&prg={$row['id']}";

    // Redirect to the URL
    $core->redirect($url);
}
else
{
    // Generate a list
    $programs_list = '';

    foreach ($program_data as $row)
    {
        // Generate the program URL
        $url  = !empty($return_url) ? "?q=view_projects&amp;prg={$row['id']}&amp;a={$return_url}"
                                    : "?q=program_home&amp;prg={$row['id']}";

        // Assign data for program
        $skin->assign(array(
            'program_url'         => $url,
            'program_title'       => htmlspecialchars($row['title']),
            'program_description' => nl2br(htmlspecialchars($row['description'])),
        ));

        $programs_list .= $skin->output('tpl_view_programs_item');
    }

    // Determine the page title
    $programs_title = !empty($return_url) ? $lang->get('select_program') : $lang->get('view_active_progms');

    // Get the pagination
    $pagination = $skin->pagination($program_count['count'], $page);

    // Assign final skin data
    $skin->assign(array(
        'programs_title'    => $programs_title,
        'programs_list'     => $programs_list,
        'list_pages'        => $pagination,
        'notice_visibility' => $skin->visibility(count($program_data) == 0),
        'list_visibility'   => $skin->visibility(count($program_data) > 0),
        'pages_visibility'  => $skin->visibility($program_count['count'] > $config->per_page),
    ));

    // Output the module
    $module_title = $programs_title;
    $module_data = $skin->output('tpl_view_programs');
}

?>
