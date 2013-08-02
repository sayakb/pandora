<?php
/**
* Pandora v1
* @license GPLv3 - http://www.opensource.org/licenses/GPL-3.0
* @copyright (c) 2012 KDE. All rights reserved.
*/

// We are in CLI, assume OS cron is being used
if (php_sapi_name() == 'cli')
{
    define('IN_PANDORA', true);

    // We need longer execution time
    set_time_limit(3600);

    // Invoke required files
    include_once('init.php');

    // Set default for sending mail
    $name = $config->ldap_fullname;
    $mail = $config->ldap_mail;
    $mentor_name = $lang->get('no_mentor');

    // Get all entries from the queue and their corresponding program data
    $sql = "SELECT prg.id as program_id, " .
           "       prg.title as program_title, " .
           "       prg.dl_mentor as program_deadline, " .
           "       prg.end_time as program_complete, " .
           "       que.deadline as deadline_flag, " .
           "       que.complete as complete_flag " .
           "FROM {$db->prefix}queue que " .
           "LEFT JOIN {$db->prefix}programs prg " .
           "ON que.program_id = prg.id " .
           "WHERE (prg.dl_mentor < TIMESTAMP(CURRENT_DATE()) " .
           "OR prg.end_time < TIMESTAMP(CURRENT_DATE()))";
    $program_data = $db->query($sql);

    // Traverse through each program
    foreach ($program_data as $program)
    {
        // Set original deadline and complete flags
        $deadline = $program['deadline_flag'];
        $complete = $program['complete_flag'];

        // All projects for this program
        $sql = "SELECT id as project_id, " .
               "       title as project_title, " .
               "       is_accepted " .
               "FROM {$db->prefix}projects " .
               "WHERE program_id = {$program['program_id']}";
        $project_data = $db->query($sql);

        // Assign program name
        $email->assign('program_name', $program['program_title']);

        // Traverse through each project
        foreach ($project_data as $project)
        {
            $project['student'] = '';
            $project['mentor'] = '';
            $project['passed'] = false;

            // Get student data
            $sql = "SELECT username, passed " .
                   "FROM {$db->prefix}participants " .
                   "WHERE program_id = {$program['program_id']} " .
                   "AND project_id = {$project['project_id']} " .
                   "AND role = 's'";
            $student_data = $db->query($sql, true);

            if ($student_data != null)
            {
                $project['student'] = $student_data['username'];
                $project['passed'] = $student_data['passed'];
            }

            // Get mentor data
            $sql = "SELECT username " .
                   "FROM {$db->prefix}participants " .
                   "WHERE program_id = {$program['program_id']} " .
                   "AND project_id = {$project['project_id']} " .
                   "AND role = 'm'";
            $mentor_data = $db->query($sql, true);

            if ($mentor_data != null)
            {
                $project['mentor'] = $mentor_data['username'];
            }

            // Get student and mentor data from LDAP
            $student_data = $user->get_details($project['student'], array($name, $mail));
            $mentor_data  = $user->get_details($project['mentor'], array($name, $mail));

            // Set student data
            if ($student_data !== false)
            {
                $student      = $project['student'];
                $student_to   = $student_data[$name][0];
                $student_name = "{$student_data[$name][0]} &lt;{$student_data[$mail][0]}&gt;";
                $student_mail = $student_data[$mail][0];
            }

            // Set mentor data
            if ($mentor_data !== false)
            {
                $mentor      = $project['mentor'];
                $mentor_to   = $mentor_data[$name][0];
                $mentor_name = "{$mentor_data[$name][0]} &lt;{$mentor_data[$mail][0]}&gt;";
                $mentor_mail = $mentor_data[$mail][0];
            }

            // Assign data needed for the email
            $email->assign(array(
                'project_name'      => $project['project_title'],
                'student_name'      => $student_name,
                'mentor_name'       => $mentor_name,
                'project_url'       => "{$config->http_host}?q=view_projects&amp;prg=" .
                                       "{$program['program_id']}&amp;p={$project['project_id']}",
            ));

            // Send out status mails on deadline
            if ($program['program_deadline'] < $core->timestamp && $project['is_accepted'] != -1 && $deadline == 0)
            {
                // Output status to console
                echo '[' . date('r') . '] ' . $lang->get('sending_status') . " #{$project['project_id']} ";

                // Set the template based on the status
                $status = $project['is_accepted'] == 1 ? 'accept' : 'reject';

                // Set initial flag values
                $success_student = true;
                $success_mentor  = true;

                if ($student_data !== false && !empty($student_mail))
                {
                    $email->assign('recipient', $student_to);
                    $success_student = $email->send($student_mail, $lang->get('subject_status'), $status);
                    sleep(2);
                }

                if ($mentor_data !== false && !empty($mentor_mail))
                {
                    $email->assign('recipient', $mentor_to);
                    $success_mentor = $email->send($mentor_mail, $lang->get('subject_status'), $status);
                    sleep(2);
                }

                // Determine status for logging
                $log_status = $success_student && $success_mentor ? $lang->get('status_ok') : $lang->get('status_error');
                echo "{$log_status}\n";
            }

            // Send out result mails on program completion
            if ($program['program_complete'] < $core->timestamp && $project['passed'] != -1 && $complete == 0)
            {
                // Output status to console
                echo '[' . date('r') . '] ' . $lang->get('sending_result') . " #{$project['project_id']} ";

                // Set the template based on the status
                $status = $project['passed'] == 1 ? 'pass' : 'fail';

                // Set initial flag status
                $success = true;

                if ($student_data !== false && !empty($student_mail))
                {
                    $email->assign('recipient', $student_to);
                    $success = $email->send($student_mail, $lang->get('subject_result'), $status);
                    sleep(2);
                }

                // Determine status for logging
                $log_status = $success ? $lang->get('status_ok') : $lang->get('status_error');
                echo "{$log_status}\n";
            }
        }

        // Set new flag values
        $deadline = $program['program_deadline'] < $core->timestamp ? 1 : 0;
        $complete = $program['program_complete'] < $core->timestamp ? 1 : 0;

        // Update the queue entry if at least one flag is still unset
        if ($deadline == 0 || $complete == 0)
        {
            $sql = "UPDATE {$db->prefix}queue " .
                   "SET deadline = {$deadline}, " .
                   "    complete = {$complete} " .
                   "WHERE program_id = {$program['program_id']}";
            $db->query($sql);
        }

        // Both flags set, remove the item from queue
        else
        {
            $sql = "DELETE FROM {$db->prefix}queue " .
                   "WHERE program_id = {$program['program_id']}";
            $db->query($sql);
        }
    }
}

// We are in web more, utilize inbuilt cron feature
else
{
    if (!defined('IN_PANDORA')) exit;

    // Use cache for cron
    if ($cache->is_available)
    {
        // Get last run time
        $last_run = $cache->get('last_run', 'cron');

        if (!$last_run)
        {
            $last_run = 0;
        }
    }

    // Use DB for cron
    else
    {
        // Get last run time
        $sql = "SELECT timestamp " .
               "FROM {$db->prefix}cron";
        $row = $db->query($sql, true);

        if ($row != null)
        {
            $last_run = $row['timestamp'];
        }
        else
        {
            $last_run = 0;
        }
    }

    // Check the time difference
    if (($core->timestamp - $last_run) > 60)
    {
        // Update new run time
        if ($cache->is_available)
        {
            $cache->put('last_run', $core->timestamp, 'cron');
        }
        else
        {
            $sql = "UPDATE {$db->prefix}cron " .
                   "SET timestamp = {$core->timestamp}";
            $db->query($sql);
        }

        // Cron tasks
        $cache->purge('users');
        $db->query("DELETE FROM {$db->prefix}session WHERE timestamp < {$user->max_age}");
    }
}

?>
