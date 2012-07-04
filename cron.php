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
           "       prg.dl_student as program_deadline, " .
           "       prg.end_time as program_complete, " .
           "       que.deadline as deadline_flag, " .
           "       que.complete as complete_flag " .
           "FROM {$db->prefix}queue que " .
           "LEFT JOIN {$db->prefix}programs prg " .
           "ON que.program_id = prg.id " .
           "WHERE (prg.dl_student < TIMESTAMP(CURRENT_DATE()) " .
           "OR prg.end_time < TIMESTAMP(CURRENT_DATE()))";
    $program_data = $db->query($sql);

    // Traverse through each program
    foreach ($program_data as $program)
    {
        // Set original deadline and complete flags
        $deadline = $program['deadline_flag'];
        $complete = $program['complete_flag'];

        // All projects for this program
        $sql = "SELECT prj.id as project_id, " .
               "       prj.title as project_title, " .
               "       prj.is_accepted as is_accepted, " .
               "       prts.username as student, " .
               "       prtm.username as mentor, " .
               "       prts.passed as passed " .
               "FROM {$db->prefix}projects prj " .
               "LEFT JOIN {$db->prefix}participants prts " .
               "ON prj.id = prts.project_id " .
               "RIGHT JOIN {$db->prefix}participants prtm " .
               "ON prj.id = prtm.project_id " .
               "WHERE prj.program_id = {$program['program_id']} " .
               "AND prj.is_accepted <> -1 " .
               "AND prts.passed <> -1 " .
               "AND prts.role = 's' " .
               "AND prtm.role = 'm'";
        $project_data = $db->query($sql);

        // Assign program name
        $email->assign('program_name', $program['program_title']);

        // Traverse through each project
        foreach ($project_data as $project)
        {            
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
            if ($program['program_deadline'] < $core->timestamp && $deadline == 0)
            {
                // Output status to console
                echo '[' . date('r') . '] ' . $lang->get('sending_status') . " #{$project['project_id']} ";
                
                // Set the template based on the status
                $status = $project['is_accepted'] == 1 ? 'accept' : 'reject';

                // Set initial flag values
                $success_student = false;
                $success_mentor  = false;

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
            if ($program['program_complete'] < $core->timestamp && $complete == 0)
            {
                // Output status to console
                echo '[' . date('r') . '] ' . $lang->get('sending_result') . " #{$project['project_id']} ";

                // Set the template based on the status
                $status = $project['passed'] == 1 ? 'pass' : 'fail';

                // Set initial flag status
                $success = false;

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

    // Read the cron table
    $sql = "SELECT timestamp, locked FROM {$db->prefix}cron LIMIT 1";
    $row = $db->query($sql, true);
    $timestamp = $row['timestamp'];
    $locked = $row['locked'];

    // Check the time difference
    if (((time() - $timestamp) > 60) && !$locked)
    {
        // Make sure the cron is run only once
        $db->query("UPDATE {$db->prefix}cron SET locked = 1 WHERE locked = 0");

        if ($db->affected_rows() > 0)
        {
            // Perform cron tasks
            $db->query("DELETE FROM {$db->prefix}session WHERE timestamp < {$user->max_age}");
            $db->query("UPDATE {$db->prefix}cron SET timestamp = " . time() . ", locked = 0");
        }
    }
}

?>